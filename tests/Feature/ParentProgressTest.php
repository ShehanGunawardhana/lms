<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\ActivitySubmissionStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Enums\StudentKind;
use App\Enums\StudentStatus;
use App\Livewire\Parent\ParentChildProgressView;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ParentProgressTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $teacher;

    protected User $parentUser;

    protected User $otherParentUser;

    protected Guardian $guardian;

    protected Guardian $otherGuardian;

    protected Student $child1;

    protected Student $child2;

    protected Student $otherChild;

    protected SchoolClass $class1;

    protected SchoolClass $class2;

    protected Activity $activity1;

    protected Activity $activity2;

    protected Activity $activity3;

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
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        // Parent
        $this->parentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Eleanor Vance',
            'status' => 'active',
            'phone' => '+94771234567',
        ]);
        $this->parentUser->assignRole(RoleName::Parent->value);

        $this->guardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser->id,
            'relationship' => 'mother',
        ]);

        // Other Parent
        $this->otherParentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Arthur Pendelton',
            'status' => 'active',
            'phone' => '+94779998877',
        ]);
        $this->otherParentUser->assignRole(RoleName::Parent->value);

        $this->otherGuardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->otherParentUser->id,
            'relationship' => 'father',
        ]);

        // Classes
        $this->class1 = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Butterflies Toddlers',
            'code' => 'NUR-BUTTERFLIES',
            'kind' => ClassKind::NurseryGroup,
            'status' => 'active',
            'capacity' => 15,
        ]);

        $this->class2 = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Little Explorers Playgroup',
            'code' => 'NUR-EXPLORERS',
            'kind' => ClassKind::NurseryGroup,
            'status' => 'active',
            'capacity' => 15,
        ]);

        // Child 1 (Oliver)
        $this->child1 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0001-AAA1',
            'first_name' => 'Oliver',
            'last_name' => 'Vance',
            'dob' => '2023-04-10',
            'gender' => 'male',
            'status' => StudentStatus::Active,
            'kind' => StudentKind::Nursery,
        ]);
        $this->child1->guardians()->attach($this->guardian->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);
        $this->child1->classes()->attach($this->class1->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);

        // Child 2 (Maya) - Multi-child support
        $this->child2 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0002-BBB2',
            'first_name' => 'Maya',
            'last_name' => 'Vance',
            'dob' => '2024-02-14',
            'gender' => 'female',
            'status' => StudentStatus::Active,
            'kind' => StudentKind::Nursery,
        ]);
        $this->child2->guardians()->attach($this->guardian->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => false,
        ]);
        $this->child2->classes()->attach($this->class2->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);

        // Other parent's child
        $this->otherChild = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0099-ZZZ9',
            'first_name' => 'Leo',
            'last_name' => 'Pendelton',
            'dob' => '2023-09-01',
            'gender' => 'male',
            'status' => StudentStatus::Active,
            'kind' => StudentKind::Nursery,
        ]);
        $this->otherChild->guardians()->attach($this->otherGuardian->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'father',
            'is_primary' => true,
        ]);
        $this->otherChild->classes()->attach($this->class1->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);

        // Activities for Class 1
        $this->activity1 = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class1->id,
            'created_by' => $this->teacher->id,
            'title' => 'Finger Painting Autumn Leaves',
            'description' => 'Explore finger painting textures and colors.',
            'instructions' => 'Dip fingers in washable paint and press onto leaf template.',
            'age_group' => 'Toddlers (2-3 yrs)',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->subDays(3),
            'deadline' => now()->addDays(2),
            'allow_late' => true,
        ]);

        $this->activity2 = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class1->id,
            'created_by' => $this->teacher->id,
            'title' => 'Counting Apples & Singing',
            'description' => 'Count from 1 to 5 with apple cutouts.',
            'instructions' => 'Follow along with the counting audio.',
            'age_group' => 'Toddlers (2-3 yrs)',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->subDays(2),
            'deadline' => now()->addDays(3),
            'allow_late' => false,
        ]);

        $this->activity3 = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class1->id,
            'created_by' => $this->teacher->id,
            'title' => 'Sensory Playdough Sculpting',
            'description' => 'Make simple playdough animal shapes.',
            'instructions' => 'Roll balls and snakes.',
            'age_group' => 'Toddlers (2-3 yrs)',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->subDay(),
            'deadline' => now()->addDays(4),
            'allow_late' => false,
        ]);

        // Child 1 Submissions:
        // Activity 1: Reviewed with Star Sticker & Feedback
        ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity1->id,
            'student_id' => $this->child1->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'Oliver enjoyed using red and yellow paint!',
            'status' => ActivitySubmissionStatus::Reviewed,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now()->subDays(2),
            'reviewed_at' => now()->subDay(),
            'reviewed_by' => $this->teacher->id,
            'feedback' => 'Wonderful color blending, Oliver! Loved your creative leaf art!',
            'rating' => 'creative',
        ]);

        // Activity 2: Submitted (Pending Review)
        ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $this->activity2->id,
            'student_id' => $this->child1->id,
            'guardian_id' => $this->guardian->id,
            'note' => 'Completed the counting song video.',
            'status' => ActivitySubmissionStatus::Submitted,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now()->subHours(6),
        ]);

        // Activity 3: Not submitted (Not started)
    }

    public function test_guest_or_non_parent_cannot_access_parent_progress(): void
    {
        $this->get(route('parent.progress'))->assertRedirect(route('login'));

        $this->actingAs($this->teacher);
        $this->get(route('parent.progress'))->assertForbidden();
    }

    public function test_parent_can_render_progress_view_with_milestones_and_completion_rate(): void
    {
        $this->actingAs($this->parentUser);

        Livewire::test(ParentChildProgressView::class, ['student' => $this->child1])
            ->assertOk()
            ->assertSee('Oliver Vance')
            ->assertSee('NUR-2026-0001-AAA1')
            ->assertSee('Butterflies Toddlers')
            // Completion stats
            ->assertSee('33%') // 1 out of 3 reviewed = 33%
            ->assertSee('Total Assigned')
            ->assertSee('Completed & Reviewed')
            ->assertSee('Pending Review')
            ->assertSee('Not Started')
            // Milestone counter
            ->assertSee('1') // 1 sticker awarded
            // Activities
            ->assertSee('Finger Painting Autumn Leaves')
            ->assertSee('Counting Apples & Singing')
            ->assertSee('Sensory Playdough Sculpting');
    }

    public function test_parent_can_switch_between_multiple_children(): void
    {
        $this->actingAs($this->parentUser);

        // Initially on child 1 (Oliver)
        $component = Livewire::test(ParentChildProgressView::class)
            ->assertSet('selectedChildId', $this->child1->id)
            ->assertSee('Oliver Vance');

        // Switch to child 2 (Maya)
        $component->call('selectChild', $this->child2->id)
            ->assertSet('selectedChildId', $this->child2->id)
            ->assertSee('Maya Vance')
            ->assertSee('Little Explorers Playgroup');
    }

    public function test_parent_can_filter_activities_by_status_and_search(): void
    {
        $this->actingAs($this->parentUser);

        // Filter: Reviewed
        Livewire::test(ParentChildProgressView::class, ['student' => $this->child1])
            ->call('setStatusFilter', 'reviewed')
            ->assertSee('Finger Painting Autumn Leaves')
            ->assertDontSee('Counting Apples & Singing')
            ->assertDontSee('Sensory Playdough Sculpting')
            // Filter: Submitted
            ->call('setStatusFilter', 'submitted')
            ->assertDontSee('Finger Painting Autumn Leaves')
            ->assertSee('Counting Apples & Singing')
            ->assertDontSee('Sensory Playdough Sculpting')
            // Filter: Not Started
            ->call('setStatusFilter', 'not_started')
            ->assertDontSee('Finger Painting Autumn Leaves')
            ->assertDontSee('Counting Apples & Singing')
            ->assertSee('Sensory Playdough Sculpting')
            // Search filter
            ->call('setStatusFilter', 'all')
            ->set('search', 'Playdough')
            ->assertSee('Sensory Playdough Sculpting')
            ->assertDontSee('Finger Painting Autumn Leaves');
    }

    public function test_sticker_trophy_cabinet_displays_awarded_stickers_and_feedback(): void
    {
        $this->actingAs($this->parentUser);

        Livewire::test(ParentChildProgressView::class, ['student' => $this->child1])
            ->call('setActiveTab', 'stickers')
            ->assertSee('Sticker Showcase')
            ->assertSee('So Creative!')
            ->assertSee('🎨')
            ->assertSee('Wonderful color blending, Oliver! Loved your creative leaf art!')
            ->assertSee('Sarah Jenkins');
    }

    public function test_quizzes_tab_renders_interactive_assessments_section(): void
    {
        $this->actingAs($this->parentUser);

        Livewire::test(ParentChildProgressView::class, ['student' => $this->child1])
            ->call('setActiveTab', 'quizzes')
            ->assertSee('Interactive Nursery Quizzes & Games')
            ->assertSee('Nursery Mode Ready')
            ->assertSee('The Hungry Caterpillar Count Quiz');
    }

    public function test_unauthorized_parent_cannot_view_another_child_progress(): void
    {
        $this->actingAs($this->otherParentUser);

        // Other parent attempts to mount progress view for Oliver
        Livewire::test(ParentChildProgressView::class, ['student' => $this->child1])
            ->assertForbidden();
    }

    public function test_multi_institution_isolation_prevents_progress_view(): void
    {
        $otherInst = Institution::create([
            'name' => 'Hillside Nursery',
            'slug' => 'hillside',
            'code' => 'HSN',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        $crossParent = User::factory()->create([
            'institution_id' => $otherInst->id,
            'status' => 'active',
        ]);
        $crossParent->assignRole(RoleName::Parent->value);

        $this->actingAs($crossParent);

        Livewire::test(ParentChildProgressView::class, ['student' => $this->child1])
            ->assertForbidden();
    }
}
