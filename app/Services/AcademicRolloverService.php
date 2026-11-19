<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AcademicRolloverService
{
    /**
     * Perform academic year rollover from source year to target year.
     *
     * @param  array{
     *     copy_class_ids?: list<int>,
     *     copy_schedules?: bool,
     *     rollover_students?: bool,
     *     archive_source_year?: bool,
     *     archive_source_classes?: bool,
     *     complete_source_enrollments?: bool,
     *     set_target_as_current?: bool
     * }  $options
     * @return array{
     *     success: bool,
     *     copied_classes: int,
     *     copied_schedules: int,
     *     rolled_over_students: int,
     *     message: string
     * }
     */
    public function performRollover(AcademicYear $sourceYear, AcademicYear $targetYear, array $options = []): array
    {
        if ($sourceYear->institution_id !== $targetYear->institution_id) {
            throw new InvalidArgumentException('Source and target academic years must belong to the same institution.');
        }

        if ($sourceYear->id === $targetYear->id) {
            throw new InvalidArgumentException('Source and target academic years cannot be the same.');
        }

        $copyClassIds = $options['copy_class_ids'] ?? [];
        $copySchedules = $options['copy_schedules'] ?? true;
        $rolloverStudents = $options['rollover_students'] ?? false;
        $archiveSourceYear = $options['archive_source_year'] ?? true;
        $archiveSourceClasses = $options['archive_source_classes'] ?? true;
        $completeSourceEnrollments = $options['complete_source_enrollments'] ?? true;
        $setTargetAsCurrent = $options['set_target_as_current'] ?? true;

        return DB::transaction(function () use (
            $sourceYear,
            $targetYear,
            $copyClassIds,
            $copySchedules,
            $rolloverStudents,
            $archiveSourceYear,
            $archiveSourceClasses,
            $completeSourceEnrollments,
            $setTargetAsCurrent
        ) {
            $institutionId = $sourceYear->institution_id;

            // 1. Target Year Activation & Current Flag
            if ($setTargetAsCurrent) {
                AcademicYear::where('institution_id', $institutionId)
                    ->where('id', '!=', $targetYear->id)
                    ->update(['is_current' => false]);

                $targetYear->update([
                    'status' => 'active',
                    'is_current' => true,
                ]);
            } else {
                $targetYear->update(['status' => 'active']);
            }

            // 2. Source Year Archival
            if ($archiveSourceYear) {
                $sourceYear->update([
                    'status' => 'archived',
                    'is_current' => false,
                ]);
            }

            // 3. Classes to Rollover
            $classesQuery = SchoolClass::where('institution_id', $institutionId)
                ->where('academic_year_id', $sourceYear->id);

            if (! empty($copyClassIds)) {
                $classesQuery->whereIn('id', $copyClassIds);
            }

            $classesToCopy = $classesQuery->with(['schedules', 'enrollments'])->get();

            $copiedClassesCount = 0;
            $copiedSchedulesCount = 0;
            $rolledOverStudentsCount = 0;

            foreach ($classesToCopy as $oldClass) {
                // Replicate class into target year
                $newClass = SchoolClass::create([
                    'institution_id' => $institutionId,
                    'academic_year_id' => $targetYear->id,
                    'teacher_id' => $oldClass->teacher_id,
                    'batch_id' => null,
                    'title' => $oldClass->title,
                    'subject' => $oldClass->subject,
                    'kind' => $oldClass->kind,
                    'fee_amount' => $oldClass->fee_amount,
                    'capacity' => $oldClass->capacity,
                    'room' => $oldClass->room,
                    'online_url' => $oldClass->online_url,
                    'status' => 'active',
                ]);

                $copiedClassesCount++;

                // Copy schedule slots
                if ($copySchedules && $oldClass->schedules->isNotEmpty()) {
                    foreach ($oldClass->schedules as $slot) {
                        ClassSchedule::create([
                            'institution_id' => $institutionId,
                            'class_id' => $newClass->id,
                            'weekday' => $slot->weekday,
                            'starts_at' => $slot->starts_at,
                            'ends_at' => $slot->ends_at,
                            'room' => $slot->room,
                            'online_url' => $slot->online_url,
                            'valid_from' => $targetYear->starts_on->toDateString(),
                            'valid_to' => $targetYear->ends_on->toDateString(),
                        ]);
                        $copiedSchedulesCount++;
                    }
                }

                // Rollover students
                if ($rolloverStudents && $oldClass->enrollments->isNotEmpty()) {
                    $activeEnrollments = $oldClass->enrollments->where('status', EnrollmentStatus::Active);
                    foreach ($activeEnrollments as $enr) {
                        Enrollment::create([
                            'institution_id' => $institutionId,
                            'student_id' => $enr->student_id,
                            'class_id' => $newClass->id,
                            'status' => EnrollmentStatus::Active->value,
                            'source' => 'rollover',
                            'notes' => "Rolled over from {$sourceYear->name}",
                            'enrolled_at' => now(),
                        ]);
                        $rolledOverStudentsCount++;
                    }
                }

                // Archive source classes and complete enrollments
                if ($completeSourceEnrollments) {
                    $oldClass->enrollments()
                        ->where('status', EnrollmentStatus::Active->value)
                        ->update(['status' => EnrollmentStatus::Completed->value]);
                }

                if ($archiveSourceClasses) {
                    $oldClass->update(['status' => 'archived']);
                }
            }

            audit(
                'academic.rollover',
                "Completed academic rollover from '{$sourceYear->name}' to '{$targetYear->name}'. Copied {$copiedClassesCount} classes, {$copiedSchedulesCount} schedules, {$rolledOverStudentsCount} students.",
                $targetYear,
                [
                    'source_year_id' => $sourceYear->id,
                    'target_year_id' => $targetYear->id,
                    'copied_classes' => $copiedClassesCount,
                    'copied_schedules' => $copiedSchedulesCount,
                    'rolled_over_students' => $rolledOverStudentsCount,
                ]
            );

            return [
                'success' => true,
                'copied_classes' => $copiedClassesCount,
                'copied_schedules' => $copiedSchedulesCount,
                'rolled_over_students' => $rolledOverStudentsCount,
                'message' => __("Rollover completed successfully: :classes classes copied, :schedules schedule slots replicated, :students students enrolled in ':target'.", [
                    'classes' => $copiedClassesCount,
                    'schedules' => $copiedSchedulesCount,
                    'students' => $rolledOverStudentsCount,
                    'target' => $targetYear->name,
                ]),
            ];
        });
    }
}
