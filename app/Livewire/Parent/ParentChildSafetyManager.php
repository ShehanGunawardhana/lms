<?php

declare(strict_types=1);

namespace App\Livewire\Parent;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ParentChildSafetyManager extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?int $selectedStudentId = null;

    // Medical & Allergy state
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

    public string $pickupRelationship = 'Relative';

    public string $pickupPhone = '';

    public string $pickupNationalId = '';

    public string $pickupNotes = '';

    public mixed $pickupPhoto = null;

    public function mount(?Student $student = null): void
    {
        if ($student !== null && $student->exists) {
            $this->authorize('viewMedicalNotes', $student);
            $this->selectedStudentId = $student->id;
        }

        $children = $this->getParentChildren();

        if ($this->selectedStudentId === null && $children->isNotEmpty()) {
            /** @var Student $firstChild */
            $firstChild = $children->first();
            $this->selectedStudentId = $firstChild->id;
        }

        if ($this->selectedStudentId !== null) {
            $this->loadStudentData();
        }
    }

    public function selectStudent(int $studentId): void
    {
        $student = $this->getStudent($studentId);
        $this->authorize('viewMedicalNotes', $student);

        $this->selectedStudentId = $student->id;
        $this->loadStudentData();
    }

    public function loadStudentData(): void
    {
        if ($this->selectedStudentId === null) {
            return;
        }

        $student = $this->getStudent($this->selectedStudentId);
        $this->authorize('viewMedicalNotes', $student);

        $this->bloodGroup = $student->blood_group;
        $this->allergies = $student->allergies;
        $this->medicalNotes = $student->medical_notes;
        $this->emergencyNotes = $student->emergency_notes;

        $this->resetForms();
    }

    public function resetForms(): void
    {
        $this->contactName = '';
        $this->contactRelationship = 'Parent';
        $this->contactPhonePrimary = '';
        $this->contactPhoneSecondary = '';
        $this->contactPriority = 1;
        $this->contactNotes = '';

        $this->pickupName = '';
        $this->pickupRelationship = 'Relative';
        $this->pickupPhone = '';
        $this->pickupNationalId = '';
        $this->pickupNotes = '';
        $this->pickupPhoto = null;

        $this->resetValidation();
    }

    public function saveMedicalNotes(): void
    {
        if ($this->selectedStudentId === null) {
            return;
        }

        $student = $this->getStudent($this->selectedStudentId);
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

        audit('parent.medical_notes_updated', "Parent updated medical notes for student {$student->full_name}", $student);
        session()->flash('success', __('Medical details & allergies updated securely.'));
    }

    public function addEmergencyContact(): void
    {
        if ($this->selectedStudentId === null) {
            return;
        }

        $student = $this->getStudent($this->selectedStudentId);
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
            'institution_id' => $student->institution_id,
            'name' => $this->contactName,
            'relationship' => $this->contactRelationship,
            'phone_primary' => $this->contactPhonePrimary,
            'phone_secondary' => $this->contactPhoneSecondary ?: null,
            'priority' => $this->contactPriority,
            'notes' => $this->contactNotes ?: null,
        ]);

        audit('parent.emergency_contact_added', "Parent added emergency contact {$this->contactName} for student {$student->full_name}", $student);

        $this->resetForms();
        session()->flash('success', __('Emergency contact added successfully.'));
    }

    public function deleteEmergencyContact(int $contactId): void
    {
        if ($this->selectedStudentId === null) {
            return;
        }

        $student = $this->getStudent($this->selectedStudentId);
        $this->authorize('manageEmergencyContacts', $student);

        $contact = $student->emergencyContacts()->where('id', $contactId)->firstOrFail();
        $name = $contact->name;
        $contact->delete();

        audit('parent.emergency_contact_deleted', "Parent deleted emergency contact {$name} for student {$student->full_name}", $student);
        session()->flash('success', __('Emergency contact removed.'));
    }

    public function addAuthorizedPickup(FileService $fileService): void
    {
        if ($this->selectedStudentId === null) {
            return;
        }

        $student = $this->getStudent($this->selectedStudentId);
        $this->authorize('manageAuthorizedPickups', $student);

        $this->validate([
            'pickupName' => ['required', 'string', 'max:150'],
            'pickupRelationship' => ['required', 'string', 'max:50'],
            'pickupPhone' => ['required', 'string', 'max:50'],
            'pickupNationalId' => ['nullable', 'string', 'max:50'],
            'pickupNotes' => ['nullable', 'string', 'max:500'],
            'pickupPhoto' => ['nullable', 'image', 'max:10240'], // 10MB
        ]);

        $photoFileId = null;
        if ($this->pickupPhoto !== null) {
            /** @var User $user */
            $user = auth()->user();
            $asset = $fileService->store(
                $this->pickupPhoto,
                purpose: 'pickup_photo',
                allowedTypes: ['jpg', 'jpeg', 'png', 'webp'],
                uploader: $user
            );
            $photoFileId = $asset->id;
        }

        $student->authorizedPickups()->create([
            'institution_id' => $student->institution_id,
            'name' => $this->pickupName,
            'relationship' => $this->pickupRelationship,
            'phone' => $this->pickupPhone,
            'national_id' => $this->pickupNationalId ?: null,
            'photo_file_id' => $photoFileId,
            'is_active' => true,
            'notes' => $this->pickupNotes ?: null,
        ]);

        audit('parent.authorized_pickup_added', "Parent authorized pickup person {$this->pickupName} for student {$student->full_name}", $student);

        $this->resetForms();
        session()->flash('success', __('Authorized pickup person added successfully.'));
    }

    public function togglePickupActive(int $pickupId): void
    {
        if ($this->selectedStudentId === null) {
            return;
        }

        $student = $this->getStudent($this->selectedStudentId);
        $this->authorize('manageAuthorizedPickups', $student);

        $pickup = $student->authorizedPickups()->where('id', $pickupId)->firstOrFail();
        $pickup->update(['is_active' => ! $pickup->is_active]);

        audit('parent.authorized_pickup_toggled', "Parent toggled active status for {$pickup->name} ({$student->full_name})", $student);
        session()->flash('success', __('Pickup authorization status updated.'));
    }

    public function deleteAuthorizedPickup(int $pickupId): void
    {
        if ($this->selectedStudentId === null) {
            return;
        }

        $student = $this->getStudent($this->selectedStudentId);
        $this->authorize('manageAuthorizedPickups', $student);

        $pickup = $student->authorizedPickups()->where('id', $pickupId)->firstOrFail();
        $name = $pickup->name;
        $pickup->delete();

        audit('parent.authorized_pickup_deleted', "Parent removed authorized pickup person {$name} for student {$student->full_name}", $student);
        session()->flash('success', __('Authorized pickup person removed.'));
    }

    /**
     * @return Collection<int, Student>
     */
    protected function getParentChildren(): Collection
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Guardian|null $guardian */
        $guardian = $user->guardian ?? Guardian::where('user_id', $user->id)->first();

        if ($guardian === null) {
            return new Collection;
        }

        return $guardian->students()
            ->with(['classes', 'emergencyContacts', 'authorizedPickups.photo'])
            ->get();
    }

    protected function getStudent(int $studentId): Student
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Guardian|null $guardian */
        $guardian = $user->guardian ?? Guardian::where('user_id', $user->id)->first();

        if ($guardian === null) {
            abort(403, __('No guardian record found.'));
        }

        /** @var Student $student */
        $student = $guardian->students()
            ->where('students.id', $studentId)
            ->with(['classes', 'emergencyContacts', 'authorizedPickups.photo'])
            ->firstOrFail();

        return $student;
    }

    public function render(): View
    {
        $children = $this->getParentChildren();

        $currentStudent = null;
        if ($this->selectedStudentId !== null) {
            $currentStudent = $children->firstWhere('id', $this->selectedStudentId);
        }

        return view('livewire.parent.parent-child-safety-manager', [
            'children' => $children,
            'currentStudent' => $currentStudent,
        ]);
    }
}
