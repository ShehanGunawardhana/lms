<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ActivityStatus;
use App\Enums\ActivitySubmissionStatus;
use App\Models\Activity;
use App\Models\ActivityDeadlineReminder;
use App\Models\Student;
use App\Notifications\ActivityDeadlineReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;

class SendActivityDeadlineRemindersJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $hoursAhead = 24
    ) {}

    /**
     * @return array{activities_scanned: int, reminders_sent: int}
     */
    public function handle(): array
    {
        $now = now();
        $targetDeadline = now()->addHours($this->hoursAhead);

        // Fetch published activities with a deadline set
        $activities = Activity::query()
            ->where('status', ActivityStatus::Published->value)
            ->whereNotNull('deadline')
            ->where('deadline', '>', $now)
            ->where('deadline', '<=', $targetDeadline)
            ->with(['schoolClass.students.guardians.user', 'extensions'])
            ->get();

        $activitiesScanned = 0;
        $remindersSent = 0;

        foreach ($activities as $activity) {
            $activitiesScanned++;
            $schoolClass = $activity->schoolClass;

            if ($schoolClass === null) {
                continue;
            }

            /** @var Collection<int, Student> $students */
            $students = $schoolClass->students()
                ->wherePivot('status', 'active')
                ->with(['guardians.user'])
                ->get();

            foreach ($students as $student) {
                // 1. Calculate effective deadline for this student (accounting for extensions)
                $effectiveDeadline = $activity->effectiveDeadlineForStudent($student->id);

                if ($effectiveDeadline === null || $effectiveDeadline->isPast() || $effectiveDeadline->isAfter($targetDeadline)) {
                    continue;
                }

                // 2. Check if student has already submitted
                $submissionStatus = $activity->statusForStudent($student->id);
                if (in_array($submissionStatus, [ActivitySubmissionStatus::Submitted, ActivitySubmissionStatus::Reviewed, ActivitySubmissionStatus::Late], true)) {
                    continue;
                }

                // 3. For each guardian, check if reminder has already been sent
                foreach ($student->guardians as $guardian) {
                    $user = $guardian->user;
                    if ($user === null) {
                        continue;
                    }

                    $alreadyReminded = ActivityDeadlineReminder::where('activity_id', $activity->id)
                        ->where('student_id', $student->id)
                        ->where('guardian_id', $guardian->id)
                        ->exists();

                    if ($alreadyReminded) {
                        continue;
                    }

                    // Dispatch notification
                    $user->notify(new ActivityDeadlineReminderNotification(
                        activity: $activity,
                        student: $student,
                        deadline: $effectiveDeadline
                    ));

                    // Record reminder sent
                    ActivityDeadlineReminder::create([
                        'institution_id' => $activity->institution_id,
                        'activity_id' => $activity->id,
                        'student_id' => $student->id,
                        'guardian_id' => $guardian->id,
                        'reminded_at' => now(),
                    ]);

                    audit(
                        'activity.deadline_reminder_sent',
                        "Sent deadline reminder for activity '{$activity->title}' to guardian {$guardian->name} for child {$student->full_name}.",
                        $activity,
                        [
                            'student_id' => $student->id,
                            'guardian_id' => $guardian->id,
                            'deadline' => $effectiveDeadline->toIso8601String(),
                        ]
                    );

                    $remindersSent++;
                }
            }
        }

        return [
            'activities_scanned' => $activitiesScanned,
            'reminders_sent' => $remindersSent,
        ];
    }
}
