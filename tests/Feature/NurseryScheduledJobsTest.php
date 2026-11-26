<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\ActivitySubmissionStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Enums\StudentKind;
use App\Enums\StudentStatus;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\ActivityVideo;
use App\Models\DeadlineExtension;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ActivityDeadlineReminderNotification;
use App\Notifications\BrokenVideoLinkNotification;
use App\Services\YoutubeService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NurseryScheduledJobsTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $teacher;

    protected User $parentUser1;

    protected User $parentUser2;

    protected Guardian $guardian1;

    protected Guardian $guardian2;

    protected Student $student1;

    protected Student $student2;

    protected SchoolClass $class;

    protected Activity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'Royal Palms Nursery',
            'slug' => 'royal-palms',
            'code' => 'RPN',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        $this->seed(RolesAndPermissionsSeeder::class);

        // Teacher
        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Sarah Jenkins',
            'email' => 'sarah@example.com',
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        // Class
        $this->class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Little Explorers Playgroup',
            'code' => 'NUR-EXPLORERS',
            'kind' => ClassKind::NurseryGroup,
            'status' => 'active',
            'capacity' => 15,
        ]);

        // Parents & Guardians
        $this->parentUser1 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Eleanor Vance',
            'status' => 'active',
        ]);
        $this->parentUser1->assignRole(RoleName::Parent->value);

        $this->guardian1 = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser1->id,
            'relationship' => 'mother',
        ]);

        $this->parentUser2 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Arthur Pendelton',
            'status' => 'active',
        ]);
        $this->parentUser2->assignRole(RoleName::Parent->value);

        $this->guardian2 = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser2->id,
            'relationship' => 'father',
        ]);

        // Students
        $this->student1 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0001-STU1',
            'first_name' => 'Oliver',
            'last_name' => 'Vance',
            'dob' => '2023-05-10',
            'gender' => 'male',
            'status' => StudentStatus::Active,
            'kind' => StudentKind::Nursery,
        ]);
        $this->student1->guardians()->attach($this->guardian1->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);
        $this->student1->classes()->attach($this->class->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);

        $this->student2 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0002-STU2',
            'first_name' => 'Maya',
            'last_name' => 'Pendelton',
            'dob' => '2023-08-15',
            'gender' => 'female',
            'status' => StudentStatus::Active,
            'kind' => StudentKind::Nursery,
        ]);
        $this->student2->guardians()->attach($this->guardian2->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'father',
            'is_primary' => true,
        ]);
        $this->student2->classes()->attach($this->class->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);

        // Activity with deadline in 12 hours
        $this->activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Finger Painting Autumn Leaves',
            'description' => 'Explore outdoor leaves and paint textures.',
            'instructions' => 'Step by step painting instructions.',
            'age_group' => 'Toddlers (2-3 yrs)',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->subDay(),
            'deadline' => now()->addHours(12),
            'allow_late' => true,
        ]);
    }

    public function test_video_health_check_flags_healthy_video(): void
    {
        $video = ActivityVideo::create([
            'activity_id' => $this->activity->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_id' => 'dQw4w9WgXcQ',
            'title' => 'Sample Learning Video',
            'is_healthy' => true,
            'sort_order' => 1,
        ]);

        $youtubeMock = $this->mock(YoutubeService::class);
        $youtubeMock->shouldReceive('checkHealth')
            ->with('dQw4w9WgXcQ')
            ->once()
            ->andReturn(true);

        Notification::fake();

        $exitCode = Artisan::call('nursery:check-video-health', [
            '--activity' => $this->activity->id,
        ]);

        $this->assertSame(0, $exitCode);

        $video->refresh();
        $this->assertTrue($video->is_healthy);
        $this->assertNotNull($video->last_checked_at);

        Notification::assertNothingSent();
    }

    public function test_video_health_check_detects_broken_video_and_notifies_teacher(): void
    {
        $video = ActivityVideo::create([
            'activity_id' => $this->activity->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=INVALID_VID',
            'video_id' => 'INVALID_VID',
            'title' => 'Deleted Nursery Song',
            'is_healthy' => true,
            'sort_order' => 1,
        ]);

        $youtubeMock = $this->mock(YoutubeService::class);
        $youtubeMock->shouldReceive('checkHealth')
            ->with('INVALID_VID')
            ->once()
            ->andReturn(false);

        Notification::fake();

        $exitCode = Artisan::call('nursery:check-video-health', [
            '--activity' => $this->activity->id,
        ]);

        $this->assertSame(0, $exitCode);

        $video->refresh();
        $this->assertFalse($video->is_healthy);
        $this->assertNotNull($video->last_checked_at);

        // Teacher was notified of broken video
        Notification::assertSentTo(
            $this->teacher,
            BrokenVideoLinkNotification::class,
            function (BrokenVideoLinkNotification $notification) use ($video) {
                return $notification->activity->id === $this->activity->id
                    && $notification->video->id === $video->id;
            }
        );

        $this->assertDatabaseHas('activity_log', [
            'description' => "Video 'Deleted Nursery Song' (https://www.youtube.com/watch?v=INVALID_VID) in activity 'Finger Painting Autumn Leaves' was detected as broken or unreachable.",
        ]);
    }

    public function test_deadline_reminders_sends_notification_only_to_unsubmitted_students(): void
    {
        Notification::fake();

        // Student 2 has already submitted
        ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student2->id,
            'guardian_id' => $this->guardian2->id,
            'status' => ActivitySubmissionStatus::Submitted,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now()->subHours(2),
        ]);

        // Student 1 has NOT submitted

        $exitCode = Artisan::call('nursery:send-deadline-reminders', [
            '--hours' => 24,
        ]);

        $this->assertSame(0, $exitCode);

        // Guardian 1 (for student 1) receives reminder
        Notification::assertSentTo(
            $this->parentUser1,
            ActivityDeadlineReminderNotification::class,
            function (ActivityDeadlineReminderNotification $notification) {
                return $notification->activity->id === $this->activity->id
                    && $notification->student->id === $this->student1->id;
            }
        );

        // Guardian 2 (for student 2) does NOT receive reminder
        Notification::assertNotSentTo(
            $this->parentUser2,
            ActivityDeadlineReminderNotification::class
        );

        // Reminder record created
        $this->assertDatabaseHas('activity_deadline_reminders', [
            'activity_id' => $this->activity->id,
            'student_id' => $this->student1->id,
            'guardian_id' => $this->guardian1->id,
        ]);
    }

    public function test_deadline_reminders_does_not_send_duplicate_reminders_on_rerun(): void
    {
        Notification::fake();

        // First run
        Artisan::call('nursery:send-deadline-reminders', ['--hours' => 24]);
        Notification::assertSentTimes(ActivityDeadlineReminderNotification::class, 2);

        // Second run immediately after
        Notification::fake();
        Artisan::call('nursery:send-deadline-reminders', ['--hours' => 24]);
        Notification::assertNothingSent();
    }

    public function test_deadline_reminders_respects_individual_student_extensions(): void
    {
        Notification::fake();

        // Extend deadline for Student 1 by 5 days (beyond 24 hour reminder window)
        DeadlineExtension::create([
            'institution_id' => $this->institution->id,
            'extendable_type' => Activity::class,
            'extendable_id' => $this->activity->id,
            'student_id' => $this->student1->id,
            'extended_by' => $this->teacher->id,
            'original_deadline' => $this->activity->deadline,
            'extended_deadline' => now()->addDays(5),
            'reason' => 'Sick leave extension',
        ]);

        Artisan::call('nursery:send-deadline-reminders', ['--hours' => 24]);

        // Student 1's guardian does NOT receive reminder because effective deadline is in 5 days
        Notification::assertNotSentTo(
            $this->parentUser1,
            ActivityDeadlineReminderNotification::class
        );

        // Student 2's guardian DOES receive reminder
        Notification::assertSentTo(
            $this->parentUser2,
            ActivityDeadlineReminderNotification::class
        );
    }
}
