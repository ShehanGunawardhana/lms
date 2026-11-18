<?php

namespace App\Livewire\Public;

use App\Models\SchoolClass;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class LecturerPortfolioPage extends Component
{
    public TeacherProfile $profile;

    // Apply Modal state
    public bool $showApplyModal = false;

    public ?int $selectedClassId = null;

    public string $applicantName = '';

    public string $applicantPhone = '';

    public string $applicantEmail = '';

    public string $applicationNotes = '';

    public function mount(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;

        $profile = TeacherProfile::where('institution_id', $institutionId)
            ->where('id', $id)
            ->with(['user', 'photo', 'classes'])
            ->firstOrFail();

        // Enforce visibility: only published portfolios are visible to the public.
        // If draft/pending, only the owner teacher or an admin may view the preview.
        if (! $profile->isPublished()) {
            /** @var User|null $user */
            $user = auth()->user();
            if ($user === null || ($user->id !== $profile->user_id && ! $user->hasRole(['admin', 'super_admin']))) {
                abort(404);
            }
        }

        $this->profile = $profile;

        if (auth()->check()) {
            /** @var User $currentUser */
            $currentUser = auth()->user();
            $this->applicantName = $currentUser->name;
            $this->applicantEmail = $currentUser->email;
            $this->applicantPhone = (string) ($currentUser->phone ?? '');
        }
    }

    public function openApplyModal(int $classId): void
    {
        $this->selectedClassId = $classId;
        $this->showApplyModal = true;
    }

    public function closeApplyModal(): void
    {
        $this->showApplyModal = false;
        $this->selectedClassId = null;
        $this->applicationNotes = '';
    }

    public function submitApplication(): void
    {
        $this->validate([
            'applicantName' => ['required', 'string', 'min:3', 'max:255'],
            'applicantEmail' => ['required', 'email', 'max:255'],
            'applicantPhone' => ['required', 'string', 'min:7', 'max:30'],
            'applicationNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($this->selectedClassId === null) {
            return;
        }

        $class = SchoolClass::findOrFail($this->selectedClassId);

        audit(
            'class.application_submitted',
            "Applicant {$this->applicantName} submitted admission request for class {$class->title}",
            $class,
            [
                'applicant_name' => $this->applicantName,
                'applicant_email' => $this->applicantEmail,
                'applicant_phone' => $this->applicantPhone,
                'notes' => $this->applicationNotes,
            ]
        );

        $classTitle = $class->title;
        $this->closeApplyModal();

        session()->flash('success', __("Your application for ':class' has been received! Our academic admissions office will review your request shortly.", ['class' => $classTitle]));
    }

    public function render(): View
    {
        $selectedClass = null;
        if ($this->selectedClassId !== null) {
            $selectedClass = SchoolClass::find($this->selectedClassId);
        }

        return view('livewire.public.lecturer-portfolio-page', [
            'selectedClass' => $selectedClass,
        ]);
    }
}
