<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;

class EnrollmentService
{
    public function __construct(
        protected ScheduleClashDetector $clashDetector
    ) {}

    /**
     * Enroll a student into a class with capacity enforcement and clash detection.
     *
     * @param  array{override_clash?: bool, force_status?: string, source?: string, notes?: string}  $options
     * @return array{
     *     success: bool,
     *     status: string,
     *     enrollment: Enrollment|null,
     *     clashes: array<int, mixed>,
     *     message: string
     * }
     */
    public function enroll(SchoolClass $class, Student $student, array $options = []): array
    {
        $overrideClash = $options['override_clash'] ?? false;
        $source = $options['source'] ?? 'admin';
        $notes = $options['notes'] ?? null;
        $institutionId = $class->institution_id;

        // Check if student belongs to the same institution
        if ($student->institution_id !== $institutionId) {
            return [
                'success' => false,
                'status' => 'institution_mismatch',
                'enrollment' => null,
                'clashes' => [],
                'message' => __('Student does not belong to the same institution.'),
            ];
        }

        // Check if student already has an active or waitlisted enrollment in this class
        $existing = Enrollment::where('institution_id', $institutionId)
            ->where('class_id', $class->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            if ($existing->isActive()) {
                return [
                    'success' => false,
                    'status' => 'already_active',
                    'enrollment' => $existing,
                    'clashes' => [],
                    'message' => __("Student ':name' is already actively enrolled in this class.", ['name' => $student->full_name]),
                ];
            }

            if ($existing->isWaitlisted()) {
                return [
                    'success' => false,
                    'status' => 'already_waitlisted',
                    'enrollment' => $existing,
                    'clashes' => [],
                    'message' => __("Student ':name' is already on the waiting list for this class.", ['name' => $student->full_name]),
                ];
            }
        }

        // Run schedule clash detection
        $clashes = $this->clashDetector->detectClashes($student, $class);

        if (! empty($clashes) && ! $overrideClash) {
            return [
                'success' => false,
                'status' => 'clash_detected',
                'enrollment' => null,
                'clashes' => $clashes,
                'message' => __('Schedule clash detected with other enrolled classes. Please acknowledge to proceed.'),
            ];
        }

        // Check class capacity
        $activeCount = $class->enrollments()->where('status', EnrollmentStatus::Active->value)->count();
        $isFull = ($class->capacity > 0) && ($activeCount >= $class->capacity);

        $targetStatus = $isFull ? EnrollmentStatus::Waitlisted->value : EnrollmentStatus::Active->value;

        // Create or reactivate enrollment record
        if ($existing) {
            $existing->update([
                'status' => $targetStatus,
                'source' => $source,
                'notes' => $notes ?: $existing->notes,
                'enrolled_at' => now(),
                'dropped_at' => null,
            ]);
            $enrollment = $existing->fresh();
        } else {
            $enrollment = Enrollment::create([
                'institution_id' => $institutionId,
                'student_id' => $student->id,
                'class_id' => $class->id,
                'status' => $targetStatus,
                'source' => $source,
                'notes' => $notes,
                'enrolled_at' => now(),
            ]);
        }

        if ($isFull) {
            $waitlistPosition = Enrollment::where('class_id', $class->id)
                ->where('status', EnrollmentStatus::Waitlisted->value)
                ->where('enrolled_at', '<=', $enrollment->enrolled_at)
                ->count();

            audit('enrollment.waitlisted', "Student {$student->full_name} placed on waitlist for {$class->title} at position #{$waitlistPosition}", $enrollment);

            return [
                'success' => true,
                'status' => 'waitlisted',
                'enrollment' => $enrollment,
                'clashes' => $clashes,
                'message' => __("Class ':class' is at full capacity (:active/:cap). Student ':name' has been added to the waiting list at position #:pos.", [
                    'class' => $class->title,
                    'active' => $activeCount,
                    'cap' => $class->capacity,
                    'name' => $student->full_name,
                    'pos' => $waitlistPosition,
                ]),
            ];
        }

        audit('enrollment.created', "Enrolled student {$student->full_name} in {$class->title}", $enrollment);

        return [
            'success' => true,
            'status' => 'active',
            'enrollment' => $enrollment,
            'clashes' => $clashes,
            'message' => __("Student ':name' enrolled successfully in ':class'.", [
                'name' => $student->full_name,
                'class' => $class->title,
            ]),
        ];
    }

    /**
     * Drop a student's enrollment.
     */
    public function drop(Enrollment $enrollment, ?string $reason = null): bool
    {
        $oldStatus = $enrollment->status instanceof EnrollmentStatus ? $enrollment->status->value : (string) $enrollment->status;
        $studentName = $enrollment->student->full_name ?? 'Student';
        $classTitle = $enrollment->schoolClass->title ?? 'Class';

        $enrollment->update([
            'status' => EnrollmentStatus::Dropped->value,
            'dropped_at' => now(),
            'notes' => $reason ? trim($reason) : $enrollment->notes,
        ]);

        audit('enrollment.dropped', "Dropped enrollment for {$studentName} from {$classTitle} (was {$oldStatus})", $enrollment, [
            'reason' => $reason,
        ]);

        return true;
    }

    /**
     * Promote a waitlisted student to active status if capacity permits.
     *
     * @return array{success: bool, enrollment: Enrollment, message: string}
     */
    public function promoteFromWaitlist(Enrollment $enrollment): array
    {
        $class = $enrollment->schoolClass;
        if (! $class) {
            return [
                'success' => false,
                'enrollment' => $enrollment,
                'message' => __('Associated class not found.'),
            ];
        }

        $activeCount = $class->enrollments()->where('status', EnrollmentStatus::Active->value)->count();
        if ($class->capacity > 0 && $activeCount >= $class->capacity) {
            return [
                'success' => false,
                'enrollment' => $enrollment,
                'message' => __("Cannot promote: class ':class' is still at full capacity (:count/:cap).", [
                    'class' => $class->title,
                    'count' => $activeCount,
                    'cap' => $class->capacity,
                ]),
            ];
        }

        $enrollment->update([
            'status' => EnrollmentStatus::Active->value,
            'enrolled_at' => now(),
        ]);

        $studentName = $enrollment->student->full_name ?? 'Student';
        audit('enrollment.promoted', "Promoted waitlisted student {$studentName} to active in {$class->title}", $enrollment);

        return [
            'success' => true,
            'enrollment' => $enrollment->fresh(),
            'message' => __("Student ':name' successfully promoted to active enrollment.", ['name' => $studentName]),
        ];
    }
}
