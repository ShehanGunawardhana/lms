<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\DeadlineExtension;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class DeadlineExtensionService
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    /**
     * Extend deadline for an activity, either for everyone in the class or a specific child.
     */
    public function extendActivityDeadline(
        Activity $activity,
        User $extendedBy,
        Carbon $newDeadline,
        string $reason,
        ?int $studentId = null
    ): DeadlineExtension {
        $cleanReason = trim($reason);
        if ($cleanReason === '') {
            throw new InvalidArgumentException(__('A valid reason for the extension is required.'));
        }

        $targetStudent = null;
        if ($studentId !== null) {
            /** @var Student|null $targetStudent */
            $targetStudent = Student::query()
                ->where('id', $studentId)
                ->where('institution_id', $activity->institution_id)
                ->whereHas('enrollments', function ($q) use ($activity) {
                    $q->where('class_id', $activity->class_id)
                        ->where('status', 'active');
                })
                ->first();

            if ($targetStudent === null) {
                throw new InvalidArgumentException(__('The specified child is not enrolled in this activity class.'));
            }
        }

        $currentDeadline = $activity->effectiveDeadlineForStudent($studentId);

        /** @var DeadlineExtension $extension */
        $extension = DeadlineExtension::create([
            'institution_id' => $activity->institution_id,
            'extendable_type' => Activity::class,
            'extendable_id' => $activity->id,
            'student_id' => $studentId,
            'extended_by' => $extendedBy->id,
            'original_deadline' => $currentDeadline,
            'extended_deadline' => $newDeadline,
            'reason' => $cleanReason,
        ]);

        // Audit log the extension (§14)
        audit(
            event: 'activity.deadline_extended',
            description: "Deadline extended for activity '{$activity->title}' to {$newDeadline->toIso8601String()}",
            subject: $activity,
            properties: [
                'extension_id' => $extension->id,
                'student_id' => $studentId,
                'target' => $studentId !== null ? 'student' : 'everyone',
                'old_deadline' => $currentDeadline?->toIso8601String(),
                'new_deadline' => $newDeadline->toIso8601String(),
                'reason' => $cleanReason,
            ],
            causer: $extendedBy,
        );

        // Notify affected parents (§6.2, §12)
        $this->notifyParentsOfExtension($activity, $extension, $targetStudent);

        return $extension;
    }

    /**
     * Notify affected parent users when an extension is granted.
     */
    protected function notifyParentsOfExtension(
        Activity $activity,
        DeadlineExtension $extension,
        ?Student $targetStudent = null
    ): void {
        $parentUsers = collect();

        if ($targetStudent !== null) {
            $targetStudent->loadMissing('guardians.user');
            foreach ($targetStudent->guardians as $guardian) {
                if ($guardian->user !== null) {
                    $parentUsers->push($guardian->user);
                }
            }
        } else {
            // Everyone in class
            $activity->loadMissing('schoolClass.students.guardians.user');
            if ($activity->schoolClass !== null) {
                /** @var Collection<int, Student> $students */
                $students = $activity->schoolClass->students()
                    ->wherePivot('status', 'active')
                    ->with('guardians.user')
                    ->get();

                foreach ($students as $student) {
                    foreach ($student->guardians as $guardian) {
                        if ($guardian->user !== null) {
                            $parentUsers->push($guardian->user);
                        }
                    }
                }
            }
        }

        $uniqueParents = $parentUsers->unique('id');

        $formattedDate = $extension->extended_deadline->format('l, M j, Y • g:i A');

        foreach ($uniqueParents as $parentUser) {
            $childContext = $targetStudent !== null
                ? " for {$targetStudent->full_name}"
                : '';

            $message = __("The deadline for ':title'{$childContext} has been extended to :date. Reason: :reason", [
                'title' => $activity->title,
                'date' => $formattedDate,
                'reason' => $extension->reason,
            ]);

            $url = route('parent.activities.show', [
                'activity' => $activity->id,
                'child' => $targetStudent?->id,
            ]);

            $this->notificationService->send(
                user: $parentUser,
                eventType: 'deadline_extended',
                title: __('⏰ Deadline Extended: :title', ['title' => $activity->title]),
                message: $message,
                data: [
                    'activity_id' => $activity->id,
                    'extension_id' => $extension->id,
                    'extended_deadline' => $extension->extended_deadline->toIso8601String(),
                    'reason' => $extension->reason,
                    'student_id' => $targetStudent?->id,
                ],
                url: $url,
            );
        }
    }
}
