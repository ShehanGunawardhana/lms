<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Assignment;
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

    /**
     * Extend deadline for an assignment, either for everyone in the class or a specific student.
     */
    public function extendAssignmentDeadline(
        Assignment $assignment,
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
                ->where('institution_id', $assignment->institution_id)
                ->whereHas('enrollments', function ($q) use ($assignment) {
                    $q->where('class_id', $assignment->class_id)
                        ->where('status', 'active');
                })
                ->first();

            if ($targetStudent === null) {
                throw new InvalidArgumentException(__('The specified student is not enrolled in this assignment class.'));
            }
        }

        $currentDeadline = $assignment->effectiveDeadlineForStudent($studentId);

        /** @var DeadlineExtension $extension */
        $extension = DeadlineExtension::create([
            'institution_id' => $assignment->institution_id,
            'extendable_type' => Assignment::class,
            'extendable_id' => $assignment->id,
            'student_id' => $studentId,
            'extended_by' => $extendedBy->id,
            'original_deadline' => $currentDeadline,
            'extended_deadline' => $newDeadline,
            'reason' => $cleanReason,
        ]);

        // Audit log the extension (§14)
        audit(
            event: 'assignment.deadline_extended',
            description: "Deadline extended for assignment '{$assignment->title}' to {$newDeadline->toIso8601String()}",
            subject: $assignment,
            properties: [
                'extension_id' => $extension->id,
                'student_id' => $studentId,
                'target' => $studentId !== null ? 'student' : 'everyone',
                'old_deadline' => $currentDeadline->toIso8601String(),
                'new_deadline' => $newDeadline->toIso8601String(),
                'reason' => $cleanReason,
            ],
            causer: $extendedBy,
        );

        // Notify affected students and parents (§8, §12)
        $this->notifyRecipientsOfAssignmentExtension($assignment, $extension, $targetStudent);

        return $extension;
    }

    /**
     * Notify affected student and parent users when an assignment extension is granted.
     */
    protected function notifyRecipientsOfAssignmentExtension(
        Assignment $assignment,
        DeadlineExtension $extension,
        ?Student $targetStudent = null
    ): void {
        $usersToNotify = collect();

        if ($targetStudent !== null) {
            // Student user account
            $targetStudent->loadMissing(['user', 'guardians.user']);
            if ($targetStudent->user !== null) {
                $usersToNotify->push($targetStudent->user);
            }

            // Guardians
            foreach ($targetStudent->guardians as $guardian) {
                if ($guardian->user !== null) {
                    $usersToNotify->push($guardian->user);
                }
            }
        } else {
            // Everyone in class
            $assignment->loadMissing('schoolClass.students.guardians.user', 'schoolClass.students.user');
            if ($assignment->schoolClass !== null) {
                /** @var Collection<int, Student> $students */
                $students = $assignment->schoolClass->students()
                    ->wherePivot('status', 'active')
                    ->with(['guardians.user', 'user'])
                    ->get();

                foreach ($students as $student) {
                    if ($student->user !== null) {
                        $usersToNotify->push($student->user);
                    }
                    foreach ($student->guardians as $guardian) {
                        if ($guardian->user !== null) {
                            $usersToNotify->push($guardian->user);
                        }
                    }
                }
            }
        }

        $uniqueUsers = $usersToNotify->unique('id');
        $formattedDate = $extension->extended_deadline->format('l, M j, Y • g:i A');

        foreach ($uniqueUsers as $user) {
            $studentContext = $targetStudent !== null
                ? " for {$targetStudent->full_name}"
                : '';

            $message = __("The deadline for ':title'{$studentContext} has been extended to :date. Reason: :reason", [
                'title' => $assignment->title,
                'date' => $formattedDate,
                'reason' => $extension->reason,
            ]);

            $url = route('assignments.show', [
                'assignment' => $assignment->id,
            ]);

            $this->notificationService->send(
                user: $user,
                eventType: 'deadline_extended',
                title: __('⏰ Deadline Extended: :title', ['title' => $assignment->title]),
                message: $message,
                data: [
                    'assignment_id' => $assignment->id,
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
