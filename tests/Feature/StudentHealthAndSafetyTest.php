<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\SmsChannelInterface;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Enums\StudentKind;
use App\Enums\StudentStatus;
use App\Livewire\Admin\NurseryStudentManager;
use App\Livewire\Admin\ParentApprovalsQueue;
use App\Livewire\Parent\ParentChildSafetyManager;
use App\Models\AuthorizedPickup;
use App\Models\ChildRegistration;
use App\Models\EmergencyContact;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class StudentHealthAndSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected User $teacher1;

    protected User $teacher2;

    protected User $parentUser;

    protected User $otherParentUser;

    protected Guardian $guardian;

    protected Guardian $otherGuardian;

    protected Student $student;

    protected SchoolClass $class1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'Royal Palms Nursery',
            'slug' => 'royal-palms-nursery',
            'code' => 'RPN',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        $this->seed(RolesAndPermissionsSeeder::class);

        // Admin
        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'SECRET',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);

        // Active Teacher (teaches class1)
        $this->teacher1 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->teacher1->assignRole(RoleName::Teacher->value);

        // Unrelated Teacher (does not teach class1)
        $this->teacher2 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->teacher2->assignRole(RoleName::Teacher->value);

        // Parent & Guardian
        $this->parentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'phone' => '+94771112233',
        ]);
        $this->parentUser->assignRole(RoleName::Parent->value);

        $this->guardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser->id,
            'relationship' => 'mother',
            'address' => '123 Palm Grove',
        ]);

        // Other Parent
        $this->otherParentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'phone' => '+94779998877',
        ]);
        $this->otherParentUser->assignRole(RoleName::Parent->value);

        $this->otherGuardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->otherParentUser->id,
            'relationship' => 'father',
            'address' => '456 Oak Street',
        ]);

        // Student
        $this->student = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0001-ABCD',
            'first_name' => 'Oliver',
            'last_name' => 'Vance',
            'dob' => '2023-05-10',
            'gender' => 'male',
            'status' => StudentStatus::Active,
            'kind' => StudentKind::Nursery,
            'blood_group' => 'O+',
            'allergies' => 'Severe Peanut Allergy; Milk Intolerance',
            'medical_notes' => 'Requires Epipen in classroom cabinet at all times.',
            'emergency_notes' => 'Dr. Perera (+94 11 234 5678)',
        ]);

        // Link student to guardian
        $this->student->guardians()->attach($this->guardian->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);

        // Nursery Class taught by teacher1
        $this->class1 = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Little Explorers Playgroup',
            'code' => 'NUR-EXPLORERS',
            'kind' => ClassKind::NurseryGroup,
            'status' => 'active',
            'capacity' => 15,
        ]);

        $this->student->classes()->attach($this->class1->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);
    }

    public function test_medical_notes_and_allergies_are_encrypted_at_rest_in_database(): void
    {
        // Query raw database row directly without Eloquent model decryption
        $raw = DB::table('students')->where('id', $this->student->id)->first();

        $this->assertNotNull($raw);
        $this->assertNotEquals('Severe Peanut Allergy; Milk Intolerance', $raw->allergies);
        $this->assertNotEquals('Requires Epipen in classroom cabinet at all times.', $raw->medical_notes);

        // Through Eloquent model, decrypted automatically
        $fresh = Student::find($this->student->id);
        $this->assertNotNull($fresh);
        $this->assertSame('Severe Peanut Allergy; Milk Intolerance', $fresh->allergies);
        $this->assertSame('Requires Epipen in classroom cabinet at all times.', $fresh->medical_notes);
    }

    public function test_enrolled_teacher_admin_and_guardian_can_view_medical_notes(): void
    {
        // Enrolled Teacher
        $this->assertTrue(Gate::forUser($this->teacher1)->allows('viewMedicalNotes', $this->student));
        $this->assertTrue(Gate::forUser($this->teacher1)->allows('viewEmergencyContacts', $this->student));
        $this->assertTrue(Gate::forUser($this->teacher1)->allows('viewAuthorizedPickups', $this->student));

        // Admin
        $this->assertTrue(Gate::forUser($this->admin)->allows('viewMedicalNotes', $this->student));
        $this->assertTrue(Gate::forUser($this->admin)->allows('manageEmergencyContacts', $this->student));

        // Guardian Parent
        $this->assertTrue(Gate::forUser($this->parentUser)->allows('viewMedicalNotes', $this->student));
        $this->assertTrue(Gate::forUser($this->parentUser)->allows('updateMedicalNotes', $this->student));
        $this->assertTrue(Gate::forUser($this->parentUser)->allows('manageEmergencyContacts', $this->student));
        $this->assertTrue(Gate::forUser($this->parentUser)->allows('manageAuthorizedPickups', $this->student));
    }

    public function test_unrelated_teacher_and_other_parents_cannot_view_or_manage_medical_notes(): void
    {
        // Unrelated Teacher (not teaching child's class)
        $this->assertFalse(Gate::forUser($this->teacher2)->allows('viewMedicalNotes', $this->student));
        $this->assertFalse(Gate::forUser($this->teacher2)->allows('manageEmergencyContacts', $this->student));
        $this->assertFalse(Gate::forUser($this->teacher2)->allows('manageAuthorizedPickups', $this->student));

        // Other parent
        $this->assertFalse(Gate::forUser($this->otherParentUser)->allows('viewMedicalNotes', $this->student));
        $this->assertFalse(Gate::forUser($this->otherParentUser)->allows('manageEmergencyContacts', $this->student));
        $this->assertFalse(Gate::forUser($this->otherParentUser)->allows('manageAuthorizedPickups', $this->student));

        // Student role user
        $studentUser = User::factory()->create(['institution_id' => $this->institution->id]);
        $studentUser->assignRole(RoleName::Student->value);
        $this->assertFalse(Gate::forUser($studentUser)->allows('viewMedicalNotes', $this->student));
    }

    public function test_parent_can_manage_child_health_notes_contacts_and_pickups(): void
    {
        $this->actingAs($this->parentUser);

        // Access ParentChildSafetyManager
        Livewire::test(ParentChildSafetyManager::class, ['student' => $this->student])
            ->assertOk()
            ->assertSee('Oliver Vance')
            ->assertSet('allergies', 'Severe Peanut Allergy; Milk Intolerance')
            // Update medical details
            ->set('allergies', 'Peanuts, Cashews, Soy')
            ->set('medicalNotes', 'Epipen updated with expiration 2027.')
            ->call('saveMedicalNotes')
            ->assertHasNoErrors()
            ->assertSee(__('Medical details & allergies updated securely.'));

        $this->student->refresh();
        $this->assertSame('Peanuts, Cashews, Soy', $this->student->allergies);
        $this->assertSame('Epipen updated with expiration 2027.', $this->student->medical_notes);

        // Add Emergency Contact
        Livewire::test(ParentChildSafetyManager::class, ['student' => $this->student])
            ->set('contactName', 'Grandmother Vance')
            ->set('contactRelationship', 'Grandmother')
            ->set('contactPhonePrimary', '+94 77 333 4455')
            ->set('contactPriority', 2)
            ->set('contactNotes', 'Lives 5 minutes away')
            ->call('addEmergencyContact')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('emergency_contacts', [
            'student_id' => $this->student->id,
            'name' => 'Grandmother Vance',
            'relationship' => 'Grandmother',
            'priority' => 2,
        ]);

        $contact = EmergencyContact::where('student_id', $this->student->id)->where('name', 'Grandmother Vance')->firstOrFail();

        // Add Authorized Pickup Person
        Livewire::test(ParentChildSafetyManager::class, ['student' => $this->student])
            ->set('pickupName', 'Uncle Arthur')
            ->set('pickupRelationship', 'Uncle')
            ->set('pickupPhone', '+94 71 888 9900')
            ->set('pickupNationalId', '199012345678')
            ->set('pickupNotes', 'Authorized for Friday afternoon pickup')
            ->call('addAuthorizedPickup')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('authorized_pickups', [
            'student_id' => $this->student->id,
            'name' => 'Uncle Arthur',
            'relationship' => 'Uncle',
            'national_id' => '199012345678',
            'is_active' => true,
        ]);

        $pickup = AuthorizedPickup::where('student_id', $this->student->id)->where('name', 'Uncle Arthur')->firstOrFail();

        // Toggle Pickup Active Status
        Livewire::test(ParentChildSafetyManager::class, ['student' => $this->student])
            ->call('togglePickupActive', $pickup->id);

        $pickup->refresh();
        $this->assertFalse($pickup->is_active);

        // Delete Contact & Pickup
        Livewire::test(ParentChildSafetyManager::class, ['student' => $this->student])
            ->call('deleteEmergencyContact', $contact->id)
            ->call('deleteAuthorizedPickup', $pickup->id);

        $this->assertDatabaseMissing('emergency_contacts', ['id' => $contact->id]);
        $this->assertDatabaseMissing('authorized_pickups', ['id' => $pickup->id]);
    }

    public function test_unauthorized_parent_cannot_manage_another_child_safety(): void
    {
        $this->actingAs($this->otherParentUser);

        // Attempting to mount safety manager for student they are not guardian of
        Livewire::test(ParentChildSafetyManager::class, ['student' => $this->student])
            ->assertForbidden();
    }

    public function test_admin_can_manage_health_and_safety_in_nursery_student_manager(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(NurseryStudentManager::class)
            ->call('openSafetyModal', $this->student->id)
            ->assertSet('showSafetyModal', true)
            ->assertSet('bloodGroup', 'O+')
            // Update medical info
            ->set('bloodGroup', 'A+')
            ->set('allergies', 'None recorded')
            ->call('saveMedicalNotes')
            ->assertHasNoErrors()
            // Add contact
            ->set('contactName', 'School Nurse Liaison')
            ->set('contactRelationship', 'Medical Liaison')
            ->set('contactPhonePrimary', '+94 11 999 0000')
            ->set('contactPriority', 3)
            ->call('addEmergencyContact')
            ->assertHasNoErrors()
            // Add pickup
            ->set('pickupName', 'Designated School Bus Driver')
            ->set('pickupRelationship', 'Transport Driver')
            ->set('pickupPhone', '+94 77 000 1122')
            ->set('pickupNationalId', '198000112233')
            ->call('addAuthorizedPickup')
            ->assertHasNoErrors();

        $this->student->refresh();
        $this->assertSame('A+', $this->student->blood_group);
        $this->assertSame('None recorded', $this->student->allergies);

        $this->assertDatabaseHas('emergency_contacts', [
            'student_id' => $this->student->id,
            'name' => 'School Nurse Liaison',
        ]);

        $this->assertDatabaseHas('authorized_pickups', [
            'student_id' => $this->student->id,
            'name' => 'Designated School Bus Driver',
            'is_active' => true,
        ]);
    }

    public function test_multi_institution_isolation_prevents_safety_access(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Secondary Branch',
            'slug' => 'secondary-branch',
            'code' => 'SEC',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        $otherAdmin = User::factory()->create([
            'institution_id' => $otherInstitution->id,
            'status' => 'active',
            'two_factor_secret' => 'SECRET',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        ]);
        $otherAdmin->assignRole(RoleName::Admin->value);

        $this->assertFalse(Gate::forUser($otherAdmin)->allows('viewMedicalNotes', $this->student));
        $this->assertFalse(Gate::forUser($otherAdmin)->allows('manageEmergencyContacts', $this->student));
        $this->assertFalse(Gate::forUser($otherAdmin)->allows('manageAuthorizedPickups', $this->student));
    }

    public function test_parent_approvals_queue_transfers_medical_notes_and_creates_initial_safety_records(): void
    {
        Notification::fake();
        $smsMock = $this->mock(SmsChannelInterface::class);
        $smsMock->shouldReceive('send')->andReturn(true);

        $this->actingAs($this->admin);

        $newParent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'pending',
            'phone' => '+94775556677',
            'name' => 'Samantha Reed',
        ]);
        $newParent->assignRole(RoleName::Parent->value);

        $profile = ParentProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $newParent->id,
            'emergency_contact_name' => 'Jonathan Reed',
            'emergency_contact_phone' => '+94775556688',
            'address' => '78 Lake View, Colombo',
        ]);

        $childReg = ChildRegistration::create([
            'institution_id' => $this->institution->id,
            'parent_profile_id' => $profile->id,
            'first_name' => 'Leo',
            'last_name' => 'Reed',
            'dob' => '2023-08-15',
            'gender' => 'male',
            'medical_notes' => 'Asthma; use salbutamol inhaler as needed.',
        ]);

        Livewire::test(ParentApprovalsQueue::class)
            ->call('approve', $profile->id)
            ->assertHasNoErrors();

        // Check created student
        $createdStudent = Student::where('institution_id', $this->institution->id)
            ->where('first_name', 'Leo')
            ->where('last_name', 'Reed')
            ->first();

        $this->assertNotNull($createdStudent);
        $this->assertSame('Asthma; use salbutamol inhaler as needed.', $createdStudent->medical_notes);

        // Emergency contact created from emergency_contact_name on profile
        $this->assertDatabaseHas('emergency_contacts', [
            'student_id' => $createdStudent->id,
            'name' => 'Jonathan Reed',
            'phone_primary' => '+94775556688',
            'priority' => 1,
        ]);

        // Authorized pickup created for parent
        $this->assertDatabaseHas('authorized_pickups', [
            'student_id' => $createdStudent->id,
            'name' => 'Samantha Reed',
            'is_active' => true,
        ]);
    }
}
