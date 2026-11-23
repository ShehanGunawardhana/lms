<?php

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\ActivitySubmissionStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Livewire\Parent\ParentActivityShow;
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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity as AuditActivity;
use Tests\TestCase;

class ParentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $parent;

    protected User $otherParent;

    protected Guardian $guardian;

    protected Guardian $otherGuardian;

    protected Student $child;

    protected Student $otherChild;

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
            'name' => 'Teacher Lily',
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        // Parent 1
        $this->parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Sarah Connor',
            'status' => 'active',
        ]);
        $this->parent->assignRole(RoleName::Parent->value);

        $this->guardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parent->id,
            'relationship' => 'mother',
        ]);

        $this->child = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0001-A1',
            'first_name' => 'John',
            'last_name' => 'Connor',
            'dob' => now()->subYears(3),
            'status' => 'active',
            'kind' => 'nursery',
        ]);

        $this->guardian->students()->attach($this->child->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);

        // Parent 2 (same institution, different family)
        $this->otherParent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Bruce Wayne',
            'status' => 'active',
        ]);
        $this->otherParent->assignRole(RoleName::Parent->value);

        $this->otherGuardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->otherParent->id,
            'relationship' => 'father',
        ]);

        $this->otherChild = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0002-B2',
            'first_name' => 'Damian',
            'last_name' => 'Wayne',
            'dob' => now()->subYears(3),
            'status' => 'active',
            'kind' => 'nursery',
        ]);

        $this->otherGuardian->students()->attach($this->otherChild->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'father',
            'is_primary' => true,
        ]);

        // Class
        $this->schoolClass = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Little Sprouts',
            'kind' => ClassKind::NurseryGroup,
            'capacity' => 12,
            'status' => 'active',
        ]);

        // Enroll students
        Enrollment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->schoolClass->id,
            'student_id' => $this->child->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonths(2),
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->schoolClass->id,
            'student_id' => $this->otherChild->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonths(2),
        ]);

        // Default published activity with 2 days future deadline
        $this->activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->schoolClass->id,
            'created_by' => $this->teacher->id,
            'title' => 'Finger Painting Autumn Trees',
            'description' => 'Dip fingers in red and yellow paint to create autumn leaves.',
            'instructions' => 'Step 1: Prepare paper. Step 2: Finger paint tree branches.',
            'age_group' => 'toddler',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->subDay(),
            'deadline' => now()->addDays(2),
            'allow_late' => true,
        ]);
    }

    public function test_initial_submission_status_is_not_started(): void
    {
        $status = $this->activity->statusForStudent($this->child->id);
        $this->assertEquals(ActivitySubmissionStatus::NotStarted, $status);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->assertSee(__('Not submitted yet'))
            ->assertSee(__('Submit Child\'s Work'));
    }

    public function test_parent_can_upload_submission_on_time_transitioning_to_submitted(): void
    {
        $file = UploadedFile::fake()->image('autumn_tree.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $file)
            ->set('submissionNote', 'John had so much fun with red and orange colors!')
            ->call('submitWork')
            ->assertHasNoErrors()
            ->assertSet('submissionError', '');

        $submission = $this->activity->submissionForStudent($this->child->id);
        $this->assertNotNull($submission);
        $this->assertEquals(ActivitySubmissionStatus::Submitted, $submission->status);
        $this->assertEquals(1, $submission->version);
        $this->assertFalse($submission->is_late);
        $this->assertEquals('John had so much fun with red and orange colors!', $submission->note);
        $this->assertEquals($this->guardian->id, $submission->guardian_id);

        // Verify status from activity
        $this->assertEquals(ActivitySubmissionStatus::Submitted, $this->activity->statusForStudent($this->child->id));

        // Verify audit log
        $this->assertTrue(
            AuditActivity::where('event', 'activity.submitted')
                ->where('subject_id', $submission->id)
                ->exists()
        );
    }

    public function test_parent_can_upload_past_deadline_when_allow_late_is_true_transitioning_to_late(): void
    {
        $this->activity->update([
            'deadline' => now()->subHours(3),
            'allow_late' => true,
        ]);

        $file = UploadedFile::fake()->image('late_painting.jpg', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $file)
            ->set('submissionNote', 'Sorry for the delay!')
            ->call('submitWork')
            ->assertHasNoErrors()
            ->assertSet('submissionError', '');

        $submission = $this->activity->submissionForStudent($this->child->id);
        $this->assertNotNull($submission);
        $this->assertEquals(ActivitySubmissionStatus::Late, $submission->status);
        $this->assertTrue($submission->is_late);
        $this->assertEquals(1, $submission->version);

        $this->assertEquals(ActivitySubmissionStatus::Late, $this->activity->statusForStudent($this->child->id));
    }

    public function test_parent_cannot_upload_past_deadline_when_allow_late_is_false(): void
    {
        $this->activity->update([
            'deadline' => now()->subHours(2),
            'allow_late' => false,
        ]);

        $file = UploadedFile::fake()->image('late_painting.jpg', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $file)
            ->call('submitWork')
            ->assertSet('submissionError', __('The deadline has passed and late submissions are not allowed for this activity.'));

        $this->assertNull($this->activity->submissionForStudent($this->child->id));
        $this->assertEquals(ActivitySubmissionStatus::NotStarted, $this->activity->statusForStudent($this->child->id));
    }

    public function test_parent_can_edit_note_on_unlocked_submission(): void
    {
        $fileAsset = FileAsset::create([
            'institution_id' => $this->institution->id,
            'uuid' => (string) Str::uuid(),
            'uploaded_by' => $this->parent->id,
            'disk' => 'private',
            'path' => 'submissions/sample.png',
            'original_name' => 'sample.png',
            'mime_verified' => 'image/png',
            'extension' => 'png',
            'size' => 1024,
            'hash_sha256' => hash('sha256', 'sample'),
            'kind' => 'image',
        ]);

        $submission = ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity->id,
            'student_id' => $this->child->id,
            'guardian_id' => $this->guardian->id,
            'file_asset_id' => $fileAsset->id,
            'note' => 'Initial note',
            'status' => ActivitySubmissionStatus::Submitted,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->call('openEditNoteModal')
            ->assertSet('showEditNoteModal', true)
            ->assertSet('editingNote', 'Initial note')
            ->set('editingNote', 'Updated note with new reflection')
            ->call('saveNote')
            ->assertHasNoErrors()
            ->assertSet('showEditNoteModal', false);

        $this->assertEquals('Updated note with new reflection', $submission->fresh()->note);

        $this->assertTrue(
            AuditActivity::where('event', 'activity.submission_note_updated')
                ->where('subject_id', $submission->id)
                ->exists()
        );
    }

    public function test_parent_can_replace_submission_creating_new_version(): void
    {
        $v1File = UploadedFile::fake()->image('tree_v1.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $v1File)
            ->set('submissionNote', 'Version 1 artwork')
            ->call('submitWork');

        $v1Submission = $this->activity->submissionForStudent($this->child->id);
        $this->assertNotNull($v1Submission);
        $this->assertEquals(1, $v1Submission->version);

        // Now replace with v2
        $v2File = UploadedFile::fake()->image('tree_v2.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->call('openReplaceModal')
            ->assertSet('showReplaceModal', true)
            ->set('replacementFile', $v2File)
            ->set('replacementNote', 'Version 2 with glitter added')
            ->call('replaceWork')
            ->assertHasNoErrors()
            ->assertSet('showReplaceModal', false)
            ->assertSet('submissionError', '');

        $currentSubmission = $this->activity->submissionForStudent($this->child->id);
        $this->assertNotNull($currentSubmission);
        $this->assertEquals(2, $currentSubmission->version);
        $this->assertEquals('Version 2 with glitter added', $currentSubmission->note);
        $this->assertEquals(ActivitySubmissionStatus::Submitted, $currentSubmission->status);

        // Historical count should be 2
        $allVersions = ActivitySubmission::where('activity_id', $this->activity->id)
            ->where('student_id', $this->child->id)
            ->get();
        $this->assertCount(2, $allVersions);

        $this->assertTrue(
            AuditActivity::where('event', 'activity.submission_replaced')
                ->where('subject_id', $currentSubmission->id)
                ->exists()
        );
    }

    public function test_replacing_past_deadline_transitions_status_to_late_when_allow_late_is_true(): void
    {
        $v1File = UploadedFile::fake()->image('v1.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $v1File)
            ->call('submitWork');

        // Move deadline to past
        $this->activity->update([
            'deadline' => now()->subHour(),
            'allow_late' => true,
        ]);

        $v2File = UploadedFile::fake()->image('v2_late.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('replacementFile', $v2File)
            ->call('replaceWork')
            ->assertHasNoErrors();

        $active = $this->activity->submissionForStudent($this->child->id);
        $this->assertNotNull($active);
        $this->assertEquals(2, $active->version);
        $this->assertTrue($active->is_late);
        $this->assertEquals(ActivitySubmissionStatus::Late, $active->status);
    }

    public function test_teacher_can_mark_submission_reviewed_and_locks_modifications(): void
    {
        $file = UploadedFile::fake()->image('work.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $file)
            ->call('submitWork');

        /** @var ActivitySubmission $submission */
        $submission = $this->activity->submissionForStudent($this->child->id);
        $this->assertEquals(ActivitySubmissionStatus::Submitted, $submission->status);

        // Teacher reviews
        $submission->markReviewed($this->teacher, feedback: 'Wonderful creativity and neat color blending!', rating: '⭐⭐⭐⭐⭐');

        $submission->refresh();
        $this->assertEquals(ActivitySubmissionStatus::Reviewed, $submission->status);
        $this->assertTrue($submission->isReviewed());
        $this->assertTrue($submission->isLocked());
        $this->assertFalse($submission->canBeModifiedByParent());
        $this->assertEquals('Wonderful creativity and neat color blending!', $submission->feedback);
        $this->assertEquals('⭐⭐⭐⭐⭐', $submission->rating);
        $this->assertEquals($this->teacher->id, $submission->reviewed_by);

        // Status from activity perspective
        $this->assertEquals(ActivitySubmissionStatus::Reviewed, $this->activity->statusForStudent($this->child->id));

        // Parent attempts to edit note or replace -> forbidden
        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->call('saveNote')
            ->assertForbidden();

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('replacementFile', $file)
            ->call('replaceWork')
            ->assertForbidden();

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->call('deleteSubmission')
            ->assertForbidden();
    }

    public function test_teacher_can_return_submission_and_parent_re_uploads_transitioning_back_to_submitted(): void
    {
        $file = UploadedFile::fake()->image('v1.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $file)
            ->call('submitWork');

        /** @var ActivitySubmission $submission */
        $submission = $this->activity->submissionForStudent($this->child->id);

        // 1. Teacher marks reviewed
        $submission->markReviewed($this->teacher, 'Initial review');

        // 2. Teacher returns for correction (Reviewed -> Returned)
        $submission->markReturned($this->teacher, feedback: 'Please ask John to write his initials on the paper and re-submit.');

        $submission->refresh();
        $this->assertEquals(ActivitySubmissionStatus::Returned, $submission->status);
        $this->assertTrue($submission->isReturned());
        $this->assertTrue($submission->canBeModifiedByParent());
        $this->assertEquals(ActivitySubmissionStatus::Returned, $this->activity->statusForStudent($this->child->id));

        // 3. Parent replaces work (Returned -> Submitted)
        $revisedFile = UploadedFile::fake()->image('v2_initials.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('replacementFile', $revisedFile)
            ->set('replacementNote', 'Initials added in bottom right corner!')
            ->call('replaceWork')
            ->assertHasNoErrors();

        $active = $this->activity->submissionForStudent($this->child->id);
        $this->assertNotNull($active);
        $this->assertEquals(2, $active->version);
        $this->assertEquals(ActivitySubmissionStatus::Submitted, $active->status);
        $this->assertEquals(ActivitySubmissionStatus::Submitted, $this->activity->statusForStudent($this->child->id));
    }

    public function test_teacher_can_reopen_reviewed_submission_transitioning_to_submitted(): void
    {
        $file = UploadedFile::fake()->image('v1.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $file)
            ->call('submitWork');

        /** @var ActivitySubmission $submission */
        $submission = $this->activity->submissionForStudent($this->child->id);
        $submission->markReviewed($this->teacher, 'Done');

        $this->assertEquals(ActivitySubmissionStatus::Reviewed, $submission->status);

        // Reopen
        $submission->reopen($this->teacher);

        $submission->refresh();
        $this->assertEquals(ActivitySubmissionStatus::Submitted, $submission->status);
        $this->assertNull($submission->reviewed_by);
        $this->assertNull($submission->reviewed_at);

        $this->assertTrue(
            AuditActivity::where('event', 'activity.submission_reopened')
                ->where('subject_id', $submission->id)
                ->exists()
        );
    }

    public function test_parent_can_soft_delete_submission_transitioning_back_to_not_started(): void
    {
        $file = UploadedFile::fake()->image('to_delete.png', 100, 100);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $file)
            ->call('submitWork');

        /** @var ActivitySubmission $submission */
        $submission = $this->activity->submissionForStudent($this->child->id);
        $this->assertNotNull($submission);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->call('confirmDelete')
            ->assertSet('showDeleteModal', true)
            ->call('deleteSubmission')
            ->assertHasNoErrors()
            ->assertSet('showDeleteModal', false);

        $this->assertTrue($submission->fresh()->trashed());
        $this->assertNull($this->activity->submissionForStudent($this->child->id));
        $this->assertEquals(ActivitySubmissionStatus::NotStarted, $this->activity->statusForStudent($this->child->id));

        $this->assertTrue(
            AuditActivity::where('event', 'activity.submission_deleted')
                ->where('subject_id', $submission->id)
                ->exists()
        );
    }

    public function test_parent_re_uploading_after_deletion_increments_version(): void
    {
        // 1. Upload v1
        $file1 = UploadedFile::fake()->image('first.png', 100, 100);
        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $file1)
            ->call('submitWork');

        $v1 = $this->activity->submissionForStudent($this->child->id);
        $this->assertEquals(1, $v1->version);

        // 2. Delete v1
        $v1->delete();
        $this->assertEquals(ActivitySubmissionStatus::NotStarted, $this->activity->statusForStudent($this->child->id));

        // 3. Re-upload fresh work
        $file2 = UploadedFile::fake()->image('fresh_v2.png', 100, 100);
        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->set('submissionFile', $file2)
            ->call('submitWork')
            ->assertHasNoErrors();

        $v2 = $this->activity->submissionForStudent($this->child->id);
        $this->assertNotNull($v2);
        $this->assertEquals(2, $v2->version);
        $this->assertEquals(ActivitySubmissionStatus::Submitted, $v2->status);
    }

    public function test_parent_cannot_view_or_submit_for_another_parents_child(): void
    {
        // otherChild belongs to Bruce Wayne
        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->otherChild->id,
            ])
            // Because parent has no relation to otherChild, mount selects parent's eligible child (John)
            ->assertSet('selectedChildId', $this->child->id);

        // Attempting to select otherChild fails because otherChild is not in eligibleChildren
        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $this->activity,
                'child' => $this->child->id,
            ])
            ->call('selectChild', $this->otherChild->id)
            ->assertSet('selectedChildId', $this->child->id);
    }

    public function test_parent_cannot_access_draft_activity(): void
    {
        $draftActivity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->schoolClass->id,
            'created_by' => $this->teacher->id,
            'title' => 'Draft Unfinished Activity',
            'instructions' => 'Draft instructions',
            'status' => ActivityStatus::Draft,
            'age_group' => 'toddler',
        ]);

        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $draftActivity,
                'child' => $this->child->id,
            ])
            ->assertForbidden();
    }

    public function test_multi_institution_tenant_isolation_on_submissions(): void
    {
        $otherTeacher = User::factory()->create([
            'institution_id' => $this->otherInstitution->id,
            'status' => 'active',
        ]);
        $otherTeacher->assignRole(RoleName::Teacher->value);

        // Create an activity in other institution
        $otherClass = SchoolClass::create([
            'institution_id' => $this->otherInstitution->id,
            'teacher_id' => $otherTeacher->id,
            'title' => 'Star Group',
            'kind' => ClassKind::NurseryGroup,
            'capacity' => 10,
            'status' => 'active',
        ]);

        $otherActivity = Activity::create([
            'institution_id' => $this->otherInstitution->id,
            'class_id' => $otherClass->id,
            'created_by' => $otherTeacher->id,
            'title' => 'Other Institution Craft',
            'instructions' => 'Other institution instructions',
            'status' => ActivityStatus::Published,
            'age_group' => 'toddler',
        ]);

        // Parent in Institution A tries to view Institution B activity
        Livewire::actingAs($this->parent)
            ->test(ParentActivityShow::class, [
                'activity' => $otherActivity,
                'child' => $this->child->id,
            ])
            ->assertForbidden();
    }
}
