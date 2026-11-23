<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\ActivitySubmissionStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Livewire\Teacher\ActivityReviewManager;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Enrollment;
use App\Models\FileAsset;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherActivityReviewTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $otherTeacher;

    protected User $admin;

    protected User $parentUser;

    protected User $studentUser;

    protected Guardian $guardian;

    protected Student $student1;

    protected Student $student2;

    protected Student $student3;

    protected SchoolClass $schoolClass;

    protected Activity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('public');

        $this->institution = Institution::create([
            'name' => 'Royal Blossoms Nursery',
            'slug' => 'royal-blossoms',
            'code' => 'RBN',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        $this->otherInstitution = Institution::create([
            'name' => 'Starlight Academy',
            'slug' => 'starlight-academy',
            'code' => 'SLA',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        session(['current_institution_id' => $this->institution->id]);

        $this->seed(RolesAndPermissionsSeeder::class);

        // Teacher
        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Teacher Sarah',
            'email' => 'sarah@royalblossoms.test',
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        // Other institution teacher
        $this->otherTeacher = User::factory()->create([
            'institution_id' => $this->otherInstitution->id,
            'name' => 'Teacher Olivia',
            'email' => 'olivia@starlight.test',
            'status' => 'active',
        ]);
        $this->otherTeacher->assignRole(RoleName::Teacher->value);

        // Admin
        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Admin Elena',
            'email' => 'admin@royalblossoms.test',
            'status' => 'active',
            'two_factor_secret' => 'SECRET',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);

        // Parent & Guardian
        $this->parentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Parent Mark',
            'email' => 'mark@example.test',
            'status' => 'active',
        ]);
        $this->parentUser->assignRole(RoleName::Parent->value);

        $this->guardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser->id,
            'first_name' => 'Mark',
            'last_name' => 'Taylor',
            'relationship' => 'Father',
            'phone' => '+94771234567',
            'email' => 'mark@example.test',
        ]);

        // Student User
        $this->studentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Child Account',
            'status' => 'active',
        ]);
        $this->studentUser->assignRole(RoleName::Student->value);

        // Nursery Students
        $this->student1 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-001',
            'first_name' => 'Leo',
            'last_name' => 'Taylor',
            'dob' => '2023-03-15',
            'gender' => 'male',
            'kind' => 'nursery',
            'status' => 'active',
        ]);
        $this->student1->guardians()->attach($this->guardian->id, [
            'institution_id' => $this->institution->id,
            'is_primary' => true,
            'relationship' => 'Father',
        ]);

        $this->student2 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-002',
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'dob' => '2023-05-20',
            'gender' => 'female',
            'kind' => 'nursery',
            'status' => 'active',
        ]);

        $this->student3 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-003',
            'first_name' => 'Noah',
            'last_name' => 'Brown',
            'dob' => '2023-01-10',
            'gender' => 'male',
            'kind' => 'nursery',
            'status' => 'active',
        ]);

        // Nursery Classroom Group
        $this->schoolClass = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Little Pandas (2-3 yrs)',
            'kind' => ClassKind::NurseryGroup,
            'fee_amount' => 12000.00,
            'capacity' => 20,
            'status' => 'active',
        ]);

        // Enroll all 3 students in this classroom group
        Enrollment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->schoolClass->id,
            'student_id' => $this->student1->id,
            'status' => 'active',
            'source' => 'direct',
            'enrolled_at' => now(),
        ]);
        Enrollment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->schoolClass->id,
            'student_id' => $this->student2->id,
            'status' => 'active',
            'source' => 'direct',
            'enrolled_at' => now(),
        ]);
        Enrollment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->schoolClass->id,
            'student_id' => $this->student3->id,
            'status' => 'active',
            'source' => 'direct',
            'enrolled_at' => now(),
        ]);

        // Create published activity
        $this->activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->schoolClass->id,
            'created_by' => $this->teacher->id,
            'title' => 'Finger Painting Butterflies',
            'status' => ActivityStatus::Published,
            'age_group' => '2-3 yrs',
            'published_at' => now()->subDay(),
            'deadline' => now()->addDays(3),
            'allow_late' => true,
            'instructions' => '<p>Mix colorful washable paints and press hands together!</p>',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('teacher.activities.reviews', $this->activity))
            ->assertRedirect(route('login'));
    }

    public function test_parent_and_student_cannot_access_review_screen(): void
    {
        $this->actingAs($this->parentUser)
            ->get(route('teacher.activities.reviews', $this->activity))
            ->assertForbidden();

        $this->actingAs($this->studentUser)
            ->get(route('teacher.activities.reviews', $this->activity))
            ->assertForbidden();
    }

    public function test_teacher_from_another_institution_cannot_access_review_screen(): void
    {
        $this->actingAs($this->otherTeacher)
            ->get(route('teacher.activities.reviews', $this->activity))
            ->assertForbidden();
    }

    public function test_teacher_and_admin_can_access_review_screen(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.activities.reviews', $this->activity))
            ->assertOk()
            ->assertSee('Review Submissions')
            ->assertSee('Finger Painting Butterflies')
            ->assertSee('Little Pandas (2-3 yrs)')
            ->assertSee('Leo Taylor')
            ->assertSee('Emma Watson')
            ->assertSee('Noah Brown');

        $this->actingAs($this->admin)
            ->get(route('teacher.activities.reviews', $this->activity))
            ->assertOk();
    }

    public function test_roster_shows_missing_when_students_have_not_submitted(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(ActivityReviewManager::class, ['activity' => $this->activity])
            ->assertSee('Class Roster')
            ->assertSeeHtml('3') // 3 total students
            ->assertSee('Not Submitted')
            ->assertSeeHtml('Not Submitted');
    }

    public function test_roster_displays_submitted_and_late_submissions(): void
    {
        // Student 1 submits on time
        $fileAsset = FileAsset::create([
            'uuid' => (string) Str::uuid(),
            'institution_id' => $this->institution->id,
            'uploaded_by' => $this->parentUser->id,
            'disk' => 'private',
            'path' => 'submissions/painting.png',
            'original_name' => 'painting.png',
            'mime_verified' => 'image/png',
            'extension' => 'png',
            'size' => 1024 * 500,
            'hash_sha256' => hash('sha256', 'sample'),
            'kind' => 'image',
            'scan_status' => 'clean',
        ]);

        ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student1->id,
            'guardian_id' => $this->guardian->id,
            'file_asset_id' => $fileAsset->id,
            'note' => 'Leo loved using blue and yellow paint!',
            'status' => ActivitySubmissionStatus::Submitted,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now(),
        ]);

        // Student 2 submits late
        ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student2->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'Sorry for the slight delay!',
            'status' => ActivitySubmissionStatus::Late,
            'version' => 1,
            'is_late' => true,
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityReviewManager::class, ['activity' => $this->activity])
            ->assertSee('Submitted')
            ->assertSee('Submitted Late')
            ->assertSee('Leo loved using blue and yellow paint!')
            ->assertSee('painting.png')
            ->assertSee('Late');
    }

    public function test_teacher_can_filter_by_status_tabs(): void
    {
        // Student 1: Submitted
        ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student1->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'Submitted work',
            'status' => ActivitySubmissionStatus::Submitted,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now(),
        ]);

        // Student 2: Reviewed
        ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student2->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'Already graded work',
            'status' => ActivitySubmissionStatus::Reviewed,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'reviewed_by' => $this->teacher->id,
            'rating' => 'superstar',
            'feedback' => 'Spectacular butterfly artwork!',
        ]);

        // Student 3 has not submitted (missing)

        Livewire::actingAs($this->teacher)
            ->test(ActivityReviewManager::class, ['activity' => $this->activity])
            // Filter pending
            ->set('statusFilter', 'pending')
            ->assertSee('Leo Taylor')
            ->assertDontSee('Emma Watson')
            ->assertDontSee('Noah Brown')
            // Filter reviewed
            ->set('statusFilter', 'reviewed')
            ->assertSee('Emma Watson')
            ->assertDontSee('Leo Taylor')
            ->assertDontSee('Noah Brown')
            // Filter missing
            ->set('statusFilter', 'missing')
            ->assertSee('Noah Brown')
            ->assertDontSee('Leo Taylor')
            ->assertDontSee('Emma Watson');
    }

    public function test_teacher_can_search_students_by_name_or_code(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(ActivityReviewManager::class, ['activity' => $this->activity])
            ->set('search', 'Leo')
            ->assertSee('Leo Taylor')
            ->assertDontSee('Emma Watson')
            ->assertDontSee('Noah Brown')
            ->set('search', 'STU-003')
            ->assertSee('Noah Brown')
            ->assertDontSee('Leo Taylor')
            ->assertDontSee('Emma Watson');
    }

    public function test_teacher_can_mark_submission_reviewed_with_sticker_and_feedback(): void
    {
        $submission = ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student1->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'Look at my butterfly!',
            'status' => ActivitySubmissionStatus::Submitted,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityReviewManager::class, ['activity' => $this->activity])
            ->call('openReviewModal', $this->student1->id)
            ->assertSet('showReviewModal', true)
            ->assertSet('selectedStudentId', $this->student1->id)
            ->set('reviewRating', 'superstar')
            ->set('reviewFeedback', 'Exceptional creativity and precision with the handprints!')
            ->call('submitReview')
            ->assertSet('showReviewModal', false)
            ->assertHasNoErrors();

        $submission->refresh();
        $this->assertEquals(ActivitySubmissionStatus::Reviewed, $submission->status);
        $this->assertEquals('superstar', $submission->rating);
        $this->assertEquals('Exceptional creativity and precision with the handprints!', $submission->feedback);
        $this->assertEquals($this->teacher->id, $submission->reviewed_by);
        $this->assertNotNull($submission->reviewed_at);

        // Audit log verified
        $this->assertDatabaseHas('activity_log', [
            'event' => 'activity.submission_reviewed',
            'causer_id' => $this->teacher->id,
        ]);
    }

    public function test_teacher_can_return_submission_for_correction_with_mandatory_feedback(): void
    {
        $submission = ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student1->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'Please check',
            'status' => ActivitySubmissionStatus::Submitted,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now(),
        ]);

        // Return requires reason >= 5 characters
        Livewire::actingAs($this->teacher)
            ->test(ActivityReviewManager::class, ['activity' => $this->activity])
            ->call('openReturnModal', $this->student1->id)
            ->assertSet('showReturnModal', true)
            ->set('returnReason', 'bad')
            ->call('confirmReturn')
            ->assertHasErrors(['returnReason']);

        Livewire::actingAs($this->teacher)
            ->test(ActivityReviewManager::class, ['activity' => $this->activity])
            ->call('openReturnModal', $this->student1->id)
            ->set('returnReason', 'The photo was too blurry to read the labels. Please retake in good lighting.')
            ->call('confirmReturn')
            ->assertSet('showReturnModal', false)
            ->assertHasNoErrors();

        $submission->refresh();
        $this->assertEquals(ActivitySubmissionStatus::Returned, $submission->status);
        $this->assertEquals('The photo was too blurry to read the labels. Please retake in good lighting.', $submission->feedback);

        // Audit log verified
        $this->assertDatabaseHas('activity_log', [
            'event' => 'activity.submission_returned',
            'causer_id' => $this->teacher->id,
        ]);
    }

    public function test_teacher_can_reopen_reviewed_submission(): void
    {
        $submission = ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student1->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'Complete work',
            'status' => ActivitySubmissionStatus::Reviewed,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'reviewed_by' => $this->teacher->id,
            'rating' => 'star',
            'feedback' => 'Good effort.',
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityReviewManager::class, ['activity' => $this->activity])
            ->call('reopenSubmission', $submission->id)
            ->assertHasNoErrors();

        $submission->refresh();
        $this->assertEquals(ActivitySubmissionStatus::Submitted, $submission->status);
        $this->assertNull($submission->reviewed_by);
        $this->assertNull($submission->reviewed_at);

        // Audit log verified
        $this->assertDatabaseHas('activity_log', [
            'event' => 'activity.submission_reopened',
            'causer_id' => $this->teacher->id,
        ]);
    }

    public function test_teacher_can_inspect_version_history(): void
    {
        // Version 1 (soft deleted)
        $v1 = ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student1->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'First attempt',
            'status' => ActivitySubmissionStatus::Returned,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now()->subHours(5),
            'deleted_at' => now()->subHours(2),
        ]);

        // Version 2 (active)
        $v2 = ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->student1->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'Corrected second attempt with brighter colors',
            'status' => ActivitySubmissionStatus::Submitted,
            'version' => 2,
            'is_late' => false,
            'submitted_at' => now()->subHour(),
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityReviewManager::class, ['activity' => $this->activity])
            ->assertSee('History (2)')
            ->call('openHistoryModal', $this->student1->id)
            ->assertSet('showHistoryModal', true)
            ->assertSee('v1')
            ->assertSee('First attempt')
            ->assertSee('v2')
            ->assertSee('Corrected second attempt with brighter colors')
            ->call('closeHistoryModal')
            ->assertSet('showHistoryModal', false);
    }
}
