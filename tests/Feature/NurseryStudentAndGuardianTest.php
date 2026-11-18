<?php

namespace Tests\Feature;

use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Enums\StudentKind;
use App\Enums\StudentStatus;
use App\Livewire\Admin\NurseryStudentManager;
use App\Livewire\Admin\ParentApprovalsQueue;
use App\Models\ChildRegistration;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class NurseryStudentAndGuardianTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected User $parentUser1;

    protected User $parentUser2;

    protected Guardian $guardianMother;

    protected Guardian $guardianFather;

    protected SchoolClass $nurseryClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'Royal Palms Nursery & Institute',
            'slug' => 'royal-palms',
            'code' => 'RPNI',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'SECRET',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);

        $this->parentUser1 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Eleanor Vance',
            'email' => 'eleanor.vance@example.com',
            'status' => 'active',
        ]);
        $this->parentUser1->assignRole(RoleName::Parent->value);

        $this->parentUser2 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Theodora Vance',
            'email' => 'theodora.vance@example.com',
            'status' => 'active',
        ]);
        $this->parentUser2->assignRole(RoleName::Parent->value);

        $this->guardianMother = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser1->id,
            'relationship' => 'mother',
        ]);

        $this->guardianFather = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser2->id,
            'relationship' => 'father',
        ]);

        $this->nurseryClass = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'title' => 'Little Blossoms Playgroup',
            'kind' => ClassKind::NurseryGroup,
            'capacity' => 15,
            'fee_amount' => 12500.00,
            'status' => 'active',
        ]);
    }

    public function test_guest_or_non_admin_cannot_access_nursery_student_manager(): void
    {
        $this->get('/admin/nursery/students')->assertRedirect('/login');

        $this->actingAs($this->parentUser1)
            ->get('/admin/nursery/students')
            ->assertStatus(403);
    }

    public function test_admin_with_two_factor_can_render_nursery_student_manager(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/nursery/students')
            ->assertOk()
            ->assertSee(__('Nursery Students & Guardians Roster'))
            ->assertSee(__('Add New Child'));
    }

    public function test_admin_can_create_new_nursery_student_and_link_primary_guardian(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(NurseryStudentManager::class)
            ->call('openCreateModal')
            ->set('firstName', 'Lucas')
            ->set('lastName', 'Vance')
            ->set('dob', '2023-04-12')
            ->set('gender', 'male')
            ->set('status', StudentStatus::Active->value)
            ->set('selectedGuardianId', $this->guardianMother->id)
            ->set('guardianRelationship', 'mother')
            ->set('selectedClassId', $this->nurseryClass->id)
            ->call('saveStudent')
            ->assertHasNoErrors()
            ->assertSee(__('Nursery student record saved successfully.'));

        $student = Student::where('first_name', 'Lucas')->where('last_name', 'Vance')->first();
        expect($student)->not->toBeNull();
        expect($student->student_code)->toStartWith('NUR-');
        expect($student->kind)->toBe(StudentKind::Nursery);
        expect($student->status)->toBe(StudentStatus::Active);

        // Verify primary guardian link
        $primaryGuardian = $student->guardians->first();
        expect($primaryGuardian)->not->toBeNull();
        expect($primaryGuardian->id)->toBe($this->guardianMother->id);
        expect($primaryGuardian->pivot->relationship)->toBe('mother');
        expect((bool) $primaryGuardian->pivot->is_primary)->toBeTrue();

        // Verify class enrollment
        $enrolledClass = $student->classes->first();
        expect($enrolledClass)->not->toBeNull();
        expect($enrolledClass->id)->toBe($this->nurseryClass->id);

        $activity = Activity::where('event', 'nursery_student.created')->first();
        expect($activity)->not->toBeNull();
        expect($activity->causer_id)->toBe($this->admin->id);
    }

    public function test_admin_can_link_multiple_guardians_and_multiple_children_per_parent(): void
    {
        $this->actingAs($this->admin);

        // Child 1: Lucas
        $child1 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => Student::generateStudentCode($this->institution->id, 'NUR'),
            'first_name' => 'Lucas',
            'last_name' => 'Vance',
            'dob' => '2022-03-10',
            'status' => StudentStatus::Active,
            'kind' => StudentKind::Nursery,
        ]);
        $child1->guardians()->attach($this->guardianMother->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);

        // Child 2 (Sibling): Chloe
        $child2 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => Student::generateStudentCode($this->institution->id, 'NUR'),
            'first_name' => 'Chloe',
            'last_name' => 'Vance',
            'dob' => '2024-01-20',
            'status' => StudentStatus::Active,
            'kind' => StudentKind::Nursery,
        ]);
        // Mother also linked to Child 2 (demonstrating several children per parent)
        $child2->guardians()->attach($this->guardianMother->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);

        // Verify mother has 2 children
        $this->guardianMother->refresh();
        expect($this->guardianMother->students->count())->toBe(2);

        // Link Father to Child 1 as 2nd guardian via Livewire component
        Livewire::test(NurseryStudentManager::class)
            ->call('openLinkGuardianModal', $child1->id)
            ->set('linkGuardianId', $this->guardianFather->id)
            ->set('linkRelationship', 'father')
            ->set('isPrimary', false)
            ->call('linkGuardian')
            ->assertHasNoErrors()
            ->assertSee(__('Guardian linked to child successfully.'));

        $child1->refresh();
        expect($child1->guardians->count())->toBe(2);

        // Unlink guardian
        Livewire::test(NurseryStudentManager::class)
            ->call('unlinkGuardian', $child1->id, $this->guardianFather->id)
            ->assertSee(__('Guardian unlinked successfully.'));

        $child1->refresh();
        expect($child1->guardians->count())->toBe(1);
    }

    public function test_admin_can_enroll_and_unenroll_student_in_nursery_class(): void
    {
        $this->actingAs($this->admin);

        $student = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => Student::generateStudentCode($this->institution->id, 'NUR'),
            'first_name' => 'Nell',
            'last_name' => 'Crain',
            'dob' => '2023-06-18',
            'status' => StudentStatus::Active,
            'kind' => StudentKind::Nursery,
        ]);

        // 1. Enroll
        Livewire::test(NurseryStudentManager::class)
            ->call('openEnrollModal', $student->id)
            ->set('enrollClassId', $this->nurseryClass->id)
            ->call('enrollClass')
            ->assertHasNoErrors()
            ->assertSee(__('enrolled into'));

        $student->refresh();
        expect($student->classes->count())->toBe(1);
        expect($student->classes->first()->id)->toBe($this->nurseryClass->id);

        // 2. Unenroll
        Livewire::test(NurseryStudentManager::class)
            ->call('unenrollClass', $student->id, $this->nurseryClass->id)
            ->assertSee(__('Student removed from class.'));

        $student->refresh();
        expect($student->classes->count())->toBe(0);
    }

    public function test_admin_can_edit_and_delete_student_record(): void
    {
        $this->actingAs($this->admin);

        $student = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => Student::generateStudentCode($this->institution->id, 'NUR'),
            'first_name' => 'Luke',
            'last_name' => 'Crain',
            'dob' => '2022-11-05',
            'status' => StudentStatus::Registered,
            'kind' => StudentKind::Nursery,
        ]);

        // 1. Edit
        Livewire::test(NurseryStudentManager::class)
            ->call('openEditModal', $student->id)
            ->set('firstName', 'Lucas Updated')
            ->set('status', StudentStatus::Active->value)
            ->call('saveStudent')
            ->assertHasNoErrors();

        $student->refresh();
        expect($student->first_name)->toBe('Lucas Updated');
        expect($student->status)->toBe(StudentStatus::Active);

        // 2. Soft delete
        Livewire::test(NurseryStudentManager::class)
            ->call('deleteStudent', $student->id)
            ->assertSee(__('has been removed'));

        expect(Student::find($student->id))->toBeNull();
        expect(Student::withTrashed()->find($student->id))->not->toBeNull();
    }

    public function test_parent_approvals_queue_automatically_creates_guardian_and_student(): void
    {
        $this->actingAs($this->admin);

        $pendingParent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'pending',
        ]);
        $pendingParent->assignRole(RoleName::Parent->value);

        $profile = ParentProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $pendingParent->id,
            'relationship' => 'mother',
            'consent_terms' => true,
            'consent_child_safety' => true,
        ]);

        ChildRegistration::create([
            'institution_id' => $this->institution->id,
            'parent_profile_id' => $profile->id,
            'first_name' => 'Daisy',
            'last_name' => 'Miller',
            'dob' => '2023-08-25',
            'gender' => 'female',
            'nursery_group' => 'playgroup',
        ]);

        Livewire::test(ParentApprovalsQueue::class)
            ->call('approve', $profile->id)
            ->assertSee(__('has been approved and notified successfully'));

        // Verify Guardian created
        $guardian = Guardian::where('user_id', $pendingParent->id)->first();
        expect($guardian)->not->toBeNull();

        // Verify Student created
        $student = Student::where('first_name', 'Daisy')->where('last_name', 'Miller')->first();
        expect($student)->not->toBeNull();
        expect($student->kind)->toBe(StudentKind::Nursery);
        expect($student->status)->toBe(StudentStatus::Active);

        // Verify Guardian-Student link
        expect($guardian->students()->where('student_id', $student->id)->exists())->toBeTrue();
    }
}
