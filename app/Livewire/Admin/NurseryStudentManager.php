<?php

namespace App\Livewire\Admin;

use App\Enums\ClassKind;
use App\Enums\StudentKind;
use App\Enums\StudentStatus;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class NurseryStudentManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $classFilter = 'all';

    // Student Create/Edit Modal State
    public bool $showStudentModal = false;

    public ?int $editingStudentId = null;

    public string $studentCode = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $dob = '';

    public string $gender = 'male';

    public string $status = 'active';

    public ?int $selectedGuardianId = null;

    public string $guardianRelationship = 'mother';

    public ?int $selectedClassId = null;

    // Link Additional Guardian Modal State
    public bool $showLinkGuardianModal = false;

    public ?int $targetStudentId = null;

    public ?int $linkGuardianId = null;

    public string $linkRelationship = 'father';

    public bool $isPrimary = false;

    // Enroll in Class Modal State
    public bool $showEnrollModal = false;

    public ?int $enrollStudentId = null;

    public ?int $enrollClassId = null;

    // Health & Safety Modal State (§6.5)
    public bool $showSafetyModal = false;

    public ?int $safetyStudentId = null;

    public string $safetyActiveTab = 'medical';

    public ?string $bloodGroup = null;

    public ?string $allergies = null;

    public ?string $medicalNotes = null;

    public ?string $emergencyNotes = null;

    // Emergency Contact Form State
    public string $contactName = '';

    public string $contactRelationship = 'Parent';

    public string $contactPhonePrimary = '';

    public string $contactPhoneSecondary = '';

    public int $contactPriority = 1;

    public string $contactNotes = '';

    // Authorized Pickup Form State
    public string $pickupName = '';

    public string $pickupRelationship = 'Parent';

    public string $pickupPhone = '';

    public string $pickupNationalId = '';

    public string $pickupNotes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingStudentId = null;
        $this->studentCode = Student::generateStudentCode(current_institution_id() ?? 1, 'NUR');
        $this->firstName = '';
        $this->lastName = '';
        $this->dob = now()->subYears(3)->format('Y-m-d');
        $this->gender = 'male';
        $this->status = StudentStatus::Active->value;
        $this->selectedGuardianId = null;
        $this->guardianRelationship = 'mother';
        $this->selectedClassId = null;
        $this->showStudentModal = true;
    }

    public function openEditModal(int $studentId): void
    {
        $this->resetValidation();
        $institutionId = current_institution_id() ?? 1;

        $student = Student::where('institution_id', $institutionId)
            ->where('id', $studentId)
            ->with(['guardians', 'classes'])
            ->firstOrFail();

        $this->editingStudentId = $student->id;
        $this->studentCode = $student->student_code;
        $this->firstName = $student->first_name;
        $this->lastName = $student->last_name;
        $this->dob = $student->dob->format('Y-m-d');
        $this->gender = $student->gender ?? 'male';
        $this->status = $student->status instanceof StudentStatus ? $student->status->value : (string) $student->status;

        $primaryGuardian = $student->guardians->first();
        $this->selectedGuardianId = $primaryGuardian?->id;
        $pivot = $primaryGuardian ? $primaryGuardian->pivot : null;
        $this->guardianRelationship = $pivot !== null ? (string) $pivot->getAttribute('relationship') : 'guardian';

        $enrolledClass = $student->classes->first();
        $this->selectedClassId = $enrolledClass?->id;

        $this->showStudentModal = true;
    }

    public function closeStudentModal(): void
    {
        $this->showStudentModal = false;
        $this->editingStudentId = null;
        $this->resetValidation();
    }

    public function saveStudent(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['required', 'string', 'max:120'],
            'dob' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'status' => ['required', Rule::enum(StudentStatus::class)],
            'selectedGuardianId' => ['nullable', 'exists:guardians,id'],
            'guardianRelationship' => ['required', 'string', 'max:50'],
            'selectedClassId' => ['nullable', 'exists:school_classes,id'],
        ]);

        if ($this->editingStudentId) {
            $student = Student::where('institution_id', $institutionId)
                ->where('id', $this->editingStudentId)
                ->firstOrFail();

            $student->update([
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'dob' => $this->dob,
                'gender' => $this->gender,
                'status' => $this->status,
            ]);

            audit('nursery_student.updated', "Updated nursery student {$student->full_name} ({$student->student_code})", $student);
        } else {
            $student = Student::create([
                'institution_id' => $institutionId,
                'student_code' => $this->studentCode ?: Student::generateStudentCode($institutionId, 'NUR'),
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'dob' => $this->dob,
                'gender' => $this->gender,
                'status' => $this->status,
                'kind' => StudentKind::Nursery,
                'registered_by' => auth()->id(),
                'registered_at' => now(),
            ]);

            audit('nursery_student.created', "Created new nursery student {$student->full_name} ({$student->student_code})", $student);
        }

        // Link primary guardian if selected (§6.5)
        if ($this->selectedGuardianId) {
            $guardian = Guardian::where('institution_id', $institutionId)
                ->where('id', $this->selectedGuardianId)
                ->first();

            if ($guardian) {
                $student->guardians()->syncWithoutDetaching([
                    $guardian->id => [
                        'institution_id' => $institutionId,
                        'relationship' => $this->guardianRelationship,
                        'is_primary' => true,
                    ],
                ]);
            }
        }

        // Enroll in class if selected (§6.5, §7.1)
        if ($this->selectedClassId) {
            $schoolClass = SchoolClass::where('institution_id', $institutionId)
                ->where('id', $this->selectedClassId)
                ->first();

            if ($schoolClass) {
                $student->classes()->syncWithoutDetaching([
                    $schoolClass->id => [
                        'institution_id' => $institutionId,
                        'status' => 'active',
                        'source' => 'admin',
                        'enrolled_at' => now(),
                    ],
                ]);
            }
        }

        $this->closeStudentModal();
        session()->flash('success', __('Nursery student record saved successfully.'));
    }

    public function openLinkGuardianModal(int $studentId): void
    {
        $this->resetValidation();
        $this->targetStudentId = $studentId;
        $this->linkGuardianId = null;
        $this->linkRelationship = 'father';
        $this->isPrimary = false;
        $this->showLinkGuardianModal = true;
    }

    public function closeLinkGuardianModal(): void
    {
        $this->showLinkGuardianModal = false;
        $this->targetStudentId = null;
        $this->linkGuardianId = null;
        $this->resetValidation();
    }

    public function linkGuardian(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'targetStudentId' => ['required', 'exists:students,id'],
            'linkGuardianId' => ['required', 'exists:guardians,id'],
            'linkRelationship' => ['required', 'string', 'max:50'],
        ]);

        $student = Student::where('institution_id', $institutionId)
            ->where('id', $this->targetStudentId)
            ->firstOrFail();

        $guardian = Guardian::where('institution_id', $institutionId)
            ->where('id', $this->linkGuardianId)
            ->firstOrFail();

        if ($this->isPrimary) {
            // Set all existing guardian links to is_primary = false
            $student->guardians()->updateExistingPivot(
                $student->guardians->pluck('id')->toArray(),
                ['is_primary' => false]
            );
        }

        $student->guardians()->syncWithoutDetaching([
            $guardian->id => [
                'institution_id' => $institutionId,
                'relationship' => $this->linkRelationship,
                'is_primary' => $this->isPrimary,
            ],
        ]);

        audit(
            'nursery_student.guardian_linked',
            "Linked guardian {$guardian->name} to student {$student->full_name}",
            $student,
            [
                'guardian_id' => $guardian->id,
                'relationship' => $this->linkRelationship,
                'is_primary' => $this->isPrimary,
            ]
        );

        $this->closeLinkGuardianModal();
        session()->flash('success', __('Guardian linked to child successfully.'));
    }

    public function unlinkGuardian(int $studentId, int $guardianId): void
    {
        $institutionId = current_institution_id() ?? 1;

        $student = Student::where('institution_id', $institutionId)
            ->where('id', $studentId)
            ->firstOrFail();

        $student->guardians()->detach($guardianId);

        audit('nursery_student.guardian_unlinked', "Unlinked guardian ID {$guardianId} from student {$student->full_name}", $student);

        session()->flash('success', __('Guardian unlinked successfully.'));
    }

    public function openEnrollModal(int $studentId): void
    {
        $this->resetValidation();
        $this->enrollStudentId = $studentId;
        $this->enrollClassId = null;
        $this->showEnrollModal = true;
    }

    public function closeEnrollModal(): void
    {
        $this->showEnrollModal = false;
        $this->enrollStudentId = null;
        $this->enrollClassId = null;
        $this->resetValidation();
    }

    public function enrollClass(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'enrollStudentId' => ['required', 'exists:students,id'],
            'enrollClassId' => ['required', 'exists:school_classes,id'],
        ]);

        $student = Student::where('institution_id', $institutionId)
            ->where('id', $this->enrollStudentId)
            ->firstOrFail();

        $schoolClass = SchoolClass::where('institution_id', $institutionId)
            ->where('id', $this->enrollClassId)
            ->firstOrFail();

        $student->classes()->syncWithoutDetaching([
            $schoolClass->id => [
                'institution_id' => $institutionId,
                'status' => 'active',
                'source' => 'admin',
                'enrolled_at' => now(),
            ],
        ]);

        audit(
            'nursery_student.enrolled',
            "Enrolled student {$student->full_name} into class {$schoolClass->title}",
            $student,
            ['class_id' => $schoolClass->id]
        );

        $this->closeEnrollModal();
        session()->flash('success', __('Student enrolled into :class successfully.', ['class' => $schoolClass->title]));
    }

    public function unenrollClass(int $studentId, int $classId): void
    {
        $institutionId = current_institution_id() ?? 1;

        $student = Student::where('institution_id', $institutionId)
            ->where('id', $studentId)
            ->firstOrFail();

        $student->classes()->detach($classId);

        audit('nursery_student.unenrolled', "Unenrolled student {$student->full_name} from class ID {$classId}", $student);

        session()->flash('success', __('Student removed from class.'));
    }

    public function deleteStudent(int $studentId): void
    {
        $institutionId = current_institution_id() ?? 1;

        $student = Student::where('institution_id', $institutionId)
            ->where('id', $studentId)
            ->firstOrFail();

        $name = $student->full_name;
        $code = $student->student_code;

        $student->delete();

        audit('nursery_student.deleted', "Deleted nursery student record for {$name} ({$code})", $student);

        session()->flash('success', __('Student record :name has been removed.', ['name' => $name]));
    }

    public function openSafetyModal(int $studentId): void
    {
        $institutionId = current_institution_id() ?? 1;
        $student = Student::where('institution_id', $institutionId)
            ->where('id', $studentId)
            ->firstOrFail();

        $this->authorize('viewMedicalNotes', $student);

        $this->safetyStudentId = $student->id;
        $this->bloodGroup = $student->blood_group;
        $this->allergies = $student->allergies;
        $this->medicalNotes = $student->medical_notes;
        $this->emergencyNotes = $student->emergency_notes;
        $this->safetyActiveTab = 'medical';
        $this->resetSafetyForms();
        $this->showSafetyModal = true;
    }

    public function closeSafetyModal(): void
    {
        $this->showSafetyModal = false;
        $this->safetyStudentId = null;
        $this->resetSafetyForms();
    }

    public function resetSafetyForms(): void
    {
        $this->contactName = '';
        $this->contactRelationship = 'Parent';
        $this->contactPhonePrimary = '';
        $this->contactPhoneSecondary = '';
        $this->contactPriority = 1;
        $this->contactNotes = '';

        $this->pickupName = '';
        $this->pickupRelationship = 'Parent';
        $this->pickupPhone = '';
        $this->pickupNationalId = '';
        $this->pickupNotes = '';
        $this->resetValidation();
    }

    public function setSafetyTab(string $tab): void
    {
        $this->safetyActiveTab = in_array($tab, ['medical', 'contacts', 'pickups'], true) ? $tab : 'medical';
    }

    public function saveMedicalNotes(): void
    {
        $institutionId = current_institution_id() ?? 1;
        $student = Student::where('institution_id', $institutionId)
            ->where('id', $this->safetyStudentId)
            ->firstOrFail();

        $this->authorize('updateMedicalNotes', $student);

        $this->validate([
            'bloodGroup' => ['nullable', 'string', 'max:20'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medicalNotes' => ['nullable', 'string', 'max:5000'],
            'emergencyNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $student->update([
            'blood_group' => $this->bloodGroup ?: null,
            'allergies' => $this->allergies ?: null,
            'medical_notes' => $this->medicalNotes ?: null,
            'emergency_notes' => $this->emergencyNotes ?: null,
        ]);

        audit('student.medical_notes_updated', "Updated health and medical notes for student {$student->full_name}", $student);
        session()->flash('safety_success', __('Medical notes & allergies updated securely.'));
    }

    public function addEmergencyContact(): void
    {
        $institutionId = current_institution_id() ?? 1;
        $student = Student::where('institution_id', $institutionId)
            ->where('id', $this->safetyStudentId)
            ->firstOrFail();

        $this->authorize('manageEmergencyContacts', $student);

        $this->validate([
            'contactName' => ['required', 'string', 'max:150'],
            'contactRelationship' => ['required', 'string', 'max:50'],
            'contactPhonePrimary' => ['required', 'string', 'max:50'],
            'contactPhoneSecondary' => ['nullable', 'string', 'max:50'],
            'contactPriority' => ['required', 'integer', 'min:1', 'max:10'],
            'contactNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $student->emergencyContacts()->create([
            'institution_id' => $institutionId,
            'name' => $this->contactName,
            'relationship' => $this->contactRelationship,
            'phone_primary' => $this->contactPhonePrimary,
            'phone_secondary' => $this->contactPhoneSecondary ?: null,
            'priority' => $this->contactPriority,
            'notes' => $this->contactNotes ?: null,
        ]);

        audit('student.emergency_contact_added', "Added emergency contact {$this->contactName} for student {$student->full_name}", $student);
        $this->resetSafetyForms();
        session()->flash('safety_success', __('Emergency contact added successfully.'));
    }

    public function deleteEmergencyContact(int $contactId): void
    {
        $institutionId = current_institution_id() ?? 1;
        $student = Student::where('institution_id', $institutionId)
            ->where('id', $this->safetyStudentId)
            ->firstOrFail();

        $this->authorize('manageEmergencyContacts', $student);

        $contact = $student->emergencyContacts()->where('id', $contactId)->firstOrFail();
        $name = $contact->name;
        $contact->delete();

        audit('student.emergency_contact_deleted', "Deleted emergency contact {$name} for student {$student->full_name}", $student);
        session()->flash('safety_success', __('Emergency contact removed.'));
    }

    public function addAuthorizedPickup(): void
    {
        $institutionId = current_institution_id() ?? 1;
        $student = Student::where('institution_id', $institutionId)
            ->where('id', $this->safetyStudentId)
            ->firstOrFail();

        $this->authorize('manageAuthorizedPickups', $student);

        $this->validate([
            'pickupName' => ['required', 'string', 'max:150'],
            'pickupRelationship' => ['required', 'string', 'max:50'],
            'pickupPhone' => ['required', 'string', 'max:50'],
            'pickupNationalId' => ['nullable', 'string', 'max:50'],
            'pickupNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $student->authorizedPickups()->create([
            'institution_id' => $institutionId,
            'name' => $this->pickupName,
            'relationship' => $this->pickupRelationship,
            'phone' => $this->pickupPhone,
            'national_id' => $this->pickupNationalId ?: null,
            'is_active' => true,
            'notes' => $this->pickupNotes ?: null,
        ]);

        audit('student.authorized_pickup_added', "Added authorized pickup person {$this->pickupName} for student {$student->full_name}", $student);
        $this->resetSafetyForms();
        session()->flash('safety_success', __('Authorized pickup person added successfully.'));
    }

    public function togglePickupActive(int $pickupId): void
    {
        $institutionId = current_institution_id() ?? 1;
        $student = Student::where('institution_id', $institutionId)
            ->where('id', $this->safetyStudentId)
            ->firstOrFail();

        $this->authorize('manageAuthorizedPickups', $student);

        $pickup = $student->authorizedPickups()->where('id', $pickupId)->firstOrFail();
        $pickup->update(['is_active' => ! $pickup->is_active]);

        audit('student.authorized_pickup_toggled', "Toggled active status for pickup person {$pickup->name} ({$student->full_name})", $student);
        session()->flash('safety_success', __('Pickup authorization status updated.'));
    }

    public function deleteAuthorizedPickup(int $pickupId): void
    {
        $institutionId = current_institution_id() ?? 1;
        $student = Student::where('institution_id', $institutionId)
            ->where('id', $this->safetyStudentId)
            ->firstOrFail();

        $this->authorize('manageAuthorizedPickups', $student);

        $pickup = $student->authorizedPickups()->where('id', $pickupId)->firstOrFail();
        $name = $pickup->name;
        $pickup->delete();

        audit('student.authorized_pickup_deleted', "Deleted authorized pickup person {$name} for student {$student->full_name}", $student);
        session()->flash('safety_success', __('Authorized pickup person removed.'));
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;

        $query = Student::where('institution_id', $institutionId)
            ->where('kind', StudentKind::Nursery)
            ->with(['guardians.user', 'classes', 'registeredBy']);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->classFilter !== 'all') {
            $query->whereHas('classes', fn (Builder $q) => $q->where('school_classes.id', $this->classFilter));
        }

        if (! empty(trim($this->search))) {
            $searchTerm = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('first_name', 'like', $searchTerm)
                    ->orWhere('last_name', 'like', $searchTerm)
                    ->orWhere('student_code', 'like', $searchTerm)
                    ->orWhereHas('guardians.user', fn (Builder $g) => $g->where('name', 'like', $searchTerm)->orWhere('phone', 'like', $searchTerm));
            });
        }

        $students = $query->latest()->paginate(10);

        $classes = SchoolClass::where('institution_id', $institutionId)
            ->where('kind', ClassKind::NurseryGroup)
            ->get();

        $guardians = Guardian::where('institution_id', $institutionId)
            ->with('user')
            ->get();

        $stats = [
            'total_students' => Student::where('institution_id', $institutionId)->where('kind', StudentKind::Nursery)->count(),
            'active_students' => Student::where('institution_id', $institutionId)->where('kind', StudentKind::Nursery)->where('status', StudentStatus::Active)->count(),
            'total_classes' => $classes->count(),
            'total_guardians' => $guardians->count(),
        ];

        $safetyStudent = null;
        if ($this->safetyStudentId !== null) {
            $safetyStudent = Student::where('institution_id', $institutionId)
                ->where('id', $this->safetyStudentId)
                ->with(['emergencyContacts', 'authorizedPickups.photo'])
                ->first();
        }

        return view('livewire.admin.nursery-student-manager', [
            'students' => $students,
            'classes' => $classes,
            'guardians' => $guardians,
            'stats' => $stats,
            'safetyStudent' => $safetyStudent,
        ]);
    }
}
