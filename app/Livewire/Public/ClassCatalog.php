<?php

namespace App\Livewire\Public;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class ClassCatalog extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedSubject = 'all';

    public int $perPage = 12;

    // Apply modal
    public bool $showApplyModal = false;

    public ?int $selectedClassId = null;

    public string $applicantName = '';

    public string $applicantPhone = '';

    public string $applicantEmail = '';

    public string $applicationNotes = '';

    public function mount(): void
    {
        if (auth()->check()) {
            /** @var User $user */
            $user = auth()->user();
            $this->applicantName = $user->name;
            $this->applicantEmail = $user->email;
            $this->applicantPhone = (string) ($user->phone ?? '');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSubject(): void
    {
        $this->resetPage();
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
            "Applicant {$this->applicantName} submitted admission application for class {$class->title}",
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

        session()->flash('success', __("Your application for ':class' has been submitted! Our academic office will contact you with admission instructions.", ['class' => $classTitle]));
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;

        $query = SchoolClass::query()
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->with(['teacher.teacherProfile.photo']);

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('subject', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhereHas('teacher', function (Builder $tQ) use ($term) {
                        $tQ->where('name', 'like', $term);
                    });
            });
        }

        if ($this->selectedSubject !== 'all') {
            $query->where('subject', $this->selectedSubject);
        }

        $classes = $query->orderBy('title')->paginate($this->perPage);

        $availableSubjects = SchoolClass::where('institution_id', $institutionId)
            ->where('status', 'active')
            ->whereNotNull('subject')
            ->distinct()
            ->pluck('subject')
            ->toArray();

        $selectedClass = null;
        if ($this->selectedClassId !== null) {
            $selectedClass = SchoolClass::with('teacher')->find($this->selectedClassId);
        }

        return view('livewire.public.class-catalog', [
            'classes' => $classes,
            'availableSubjects' => $availableSubjects,
            'selectedClass' => $selectedClass,
        ]);
    }
}
