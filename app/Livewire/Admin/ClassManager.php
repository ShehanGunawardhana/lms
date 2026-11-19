<?php

namespace App\Livewire\Admin;

use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ClassManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $kindFilter = 'all'; // 'all', 'institute_class', 'nursery_group'

    public string $statusFilter = 'all'; // 'all', 'active', 'archived'

    public ?int $yearFilter = null;

    public ?int $teacherFilter = null;

    // Modal state
    public bool $showModal = false;

    public ?int $editingClassId = null;

    public string $title = '';

    public string $subject = '';

    public string $kind = 'institute_class';

    public ?int $academic_year_id = null;

    public ?int $batch_id = null;

    public ?int $teacher_id = null;

    public float|string $fee_amount = '0.00';

    public int|string $capacity = 30;

    public string $room = '';

    public string $online_url = '';

    public string $description = '';

    public string $status = 'active';

    public function mount(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $currentYear = AcademicYear::where('institution_id', $institutionId)
            ->where('is_current', true)
            ->first();

        if ($currentYear) {
            $this->yearFilter = $currentYear->id;
            $this->academic_year_id = $currentYear->id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingKindFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingYearFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTeacherFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $class = SchoolClass::where('institution_id', $institutionId)->findOrFail($id);

        $this->editingClassId = $class->id;
        $this->title = $class->title;
        $this->subject = $class->subject ?? '';
        $this->kind = $class->kind instanceof ClassKind ? $class->kind->value : (string) $class->kind;
        $this->academic_year_id = $class->academic_year_id;
        $this->batch_id = $class->batch_id;
        $this->teacher_id = $class->teacher_id;
        $this->fee_amount = number_format((float) $class->fee_amount, 2, '.', '');
        $this->capacity = $class->capacity;
        $this->room = $class->room ?? '';
        $this->online_url = $class->online_url ?? '';
        $this->description = $class->description ?? '';
        $this->status = $class->status;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $institutionId = current_institution_id() ?? 1;
        $currentYear = AcademicYear::where('institution_id', $institutionId)->where('is_current', true)->first();

        $this->editingClassId = null;
        $this->title = '';
        $this->subject = '';
        $this->kind = ClassKind::InstituteClass->value;
        $this->academic_year_id = $currentYear ? $currentYear->id : $this->yearFilter;
        $this->batch_id = null;
        $this->teacher_id = null;
        $this->fee_amount = '0.00';
        $this->capacity = 30;
        $this->room = '';
        $this->online_url = '';
        $this->description = '';
        $this->status = 'active';
        $this->resetErrorBag();
    }

    public function saveClass(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'subject' => ['nullable', 'string', 'max:100'],
            'kind' => ['required', Rule::in([ClassKind::InstituteClass->value, ClassKind::NurseryGroup->value])],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'fee_amount' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'room' => ['nullable', 'string', 'max:100'],
            'online_url' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'archived'])],
        ]);

        if ($this->editingClassId) {
            $class = SchoolClass::where('institution_id', $institutionId)->findOrFail($this->editingClassId);

            $class->update([
                'title' => trim($this->title),
                'subject' => trim($this->subject) ?: null,
                'kind' => $this->kind,
                'academic_year_id' => $this->academic_year_id ?: null,
                'batch_id' => $this->batch_id ?: null,
                'teacher_id' => $this->teacher_id ?: null,
                'fee_amount' => (float) $this->fee_amount,
                'capacity' => (int) $this->capacity,
                'room' => trim($this->room) ?: null,
                'online_url' => trim($this->online_url) ?: null,
                'description' => trim($this->description) ?: null,
                'status' => $this->status,
            ]);

            audit('class.updated', "Updated class {$class->title} (#{$class->id})", $class);
            session()->flash('success', __("Class ':title' updated successfully.", ['title' => $class->title]));
        } else {
            $class = SchoolClass::create([
                'institution_id' => $institutionId,
                'title' => trim($this->title),
                'subject' => trim($this->subject) ?: null,
                'kind' => $this->kind,
                'academic_year_id' => $this->academic_year_id ?: null,
                'batch_id' => $this->batch_id ?: null,
                'teacher_id' => $this->teacher_id ?: null,
                'fee_amount' => (float) $this->fee_amount,
                'capacity' => (int) $this->capacity,
                'room' => trim($this->room) ?: null,
                'online_url' => trim($this->online_url) ?: null,
                'description' => trim($this->description) ?: null,
                'status' => $this->status,
            ]);

            audit('class.created', "Created class {$class->title} (#{$class->id})", $class);
            session()->flash('success', __("Class ':title' created successfully.", ['title' => $class->title]));
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $class = SchoolClass::where('institution_id', $institutionId)->findOrFail($id);

        $newStatus = $class->status === 'active' ? 'archived' : 'active';
        $class->update(['status' => $newStatus]);

        audit('class.status_changed', "Changed class {$class->title} status to {$newStatus}", $class);
        session()->flash('success', __("Class ':title' marked as :status.", ['title' => $class->title, 'status' => $newStatus]));
    }

    public function deleteClass(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $class = SchoolClass::where('institution_id', $institutionId)
            ->withCount('enrollments')
            ->findOrFail($id);

        if ($class->enrollments_count > 0) {
            session()->flash('error', __('Cannot delete class with :count enrolled students. Please drop enrollments or archive the class instead.', ['count' => $class->enrollments_count]));

            return;
        }

        $title = $class->title;
        $class->delete();

        audit('class.deleted', "Deleted class {$title}");
        session()->flash('success', __("Class ':title' deleted successfully.", ['title' => $title]));
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;

        $query = SchoolClass::where('institution_id', $institutionId)
            ->with(['academicYear', 'batch', 'teacher'])
            ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')]);

        if ($this->kindFilter !== 'all') {
            $query->where('kind', $this->kindFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->yearFilter) {
            $query->where('academic_year_id', $this->yearFilter);
        }

        if ($this->teacherFilter) {
            $query->where('teacher_id', $this->teacherFilter);
        }

        if (! empty(trim($this->search))) {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('subject', 'like', $term)
                    ->orWhere('room', 'like', $term)
                    ->orWhereHas('teacher', fn ($tq) => $tq->where('name', 'like', $term));
            });
        }

        $classes = $query->orderBy('title')->paginate(10);

        // Filter collections
        $academicYears = AcademicYear::where('institution_id', $institutionId)->orderByDesc('starts_on')->get();
        $batches = Batch::where('institution_id', $institutionId)->where('status', 'active')->orderBy('name')->get();
        $teachers = User::where('institution_id', $institutionId)->role(RoleName::Teacher->value)->orderBy('name')->get();

        // Metrics
        $totalClasses = SchoolClass::where('institution_id', $institutionId)->count();
        $activeClasses = SchoolClass::where('institution_id', $institutionId)->where('status', 'active')->count();
        $nurseryCount = SchoolClass::where('institution_id', $institutionId)->where('kind', ClassKind::NurseryGroup->value)->count();
        $instituteCount = SchoolClass::where('institution_id', $institutionId)->where('kind', ClassKind::InstituteClass->value)->count();

        return view('livewire.admin.class-manager', [
            'classes' => $classes,
            'academicYears' => $academicYears,
            'batches' => $batches,
            'teachers' => $teachers,
            'metrics' => [
                'total' => $totalClasses,
                'active' => $activeClasses,
                'nursery' => $nurseryCount,
                'institute' => $instituteCount,
            ],
        ]);
    }
}
