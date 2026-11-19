<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\Student;

class ScheduleClashDetector
{
    /**
     * Detect schedule overlaps between a target class and a student's existing active enrolled classes.
     *
     * @return array<int, array{
     *     conflicting_class_id: int,
     *     conflicting_class_title: string,
     *     weekday: int,
     *     weekday_name: string,
     *     target_time: string,
     *     conflicting_time: string,
     *     message: string
     * }>
     */
    public function detectClashes(Student $student, SchoolClass $targetClass): array
    {
        $targetSchedules = $targetClass->schedules()->get();

        if ($targetSchedules->isEmpty()) {
            return [];
        }

        $activeEnrollments = $student->enrollments()
            ->where('status', EnrollmentStatus::Active->value)
            ->where('class_id', '!=', $targetClass->id)
            ->with(['schoolClass.schedules'])
            ->get();

        $clashes = [];

        foreach ($activeEnrollments as $enrollment) {
            $otherClass = $enrollment->schoolClass;
            if (! $otherClass) {
                continue;
            }

            $otherSchedules = $otherClass->schedules;

            foreach ($targetSchedules as $targetSlot) {
                foreach ($otherSchedules as $otherSlot) {
                    if ($targetSlot->weekday !== $otherSlot->weekday) {
                        continue;
                    }

                    if ($this->timesOverlap($targetSlot, $otherSlot)) {
                        $targetTimeStr = substr((string) $targetSlot->starts_at, 0, 5).' - '.substr((string) $targetSlot->ends_at, 0, 5);
                        $conflictTimeStr = substr((string) $otherSlot->starts_at, 0, 5).' - '.substr((string) $otherSlot->ends_at, 0, 5);

                        $clashes[] = [
                            'conflicting_class_id' => $otherClass->id,
                            'conflicting_class_title' => $otherClass->title,
                            'weekday' => $targetSlot->weekday,
                            'weekday_name' => $targetSlot->weekday_name,
                            'target_time' => $targetTimeStr,
                            'conflicting_time' => $conflictTimeStr,
                            'message' => __("Schedule clash with ':title' on :day (:target vs :conflict)", [
                                'title' => $otherClass->title,
                                'day' => $targetSlot->weekday_name,
                                'target' => $targetTimeStr,
                                'conflict' => $conflictTimeStr,
                            ]),
                        ];
                    }
                }
            }
        }

        return $clashes;
    }

    /**
     * Check whether two schedule slots on the same day overlap in time.
     */
    private function timesOverlap(ClassSchedule $slotA, ClassSchedule $slotB): bool
    {
        $startA = $this->normalizeTime((string) $slotA->starts_at);
        $endA = $this->normalizeTime((string) $slotA->ends_at);
        $startB = $this->normalizeTime((string) $slotB->starts_at);
        $endB = $this->normalizeTime((string) $slotB->ends_at);

        return ($startA < $endB) && ($endA > $startB);
    }

    private function normalizeTime(string $time): string
    {
        if (strlen($time) === 5) {
            return $time.':00';
        }

        return substr($time, 0, 8);
    }

    public function hasClashes(Student $student, SchoolClass $targetClass): bool
    {
        return count($this->detectClashes($student, $targetClass)) > 0;
    }
}
