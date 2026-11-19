<?php

namespace App\Livewire\Admin;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Term;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AcademicStructureManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $activeTab = 'years'; // 'years', 'terms', 'batches'

    // --- Academic Years State ---
    public string $yearSearch = '';

    public string $yearStatusFilter = 'all';

    public bool $showYearModal = false;

    public ?int $editingYearId = null;

    public string $yearName = '';

    public string $yearCode = '';

    public string $yearStartsOn = '';

    public string $yearEndsOn = '';

    public bool $yearIsCurrent = false;

    public string $yearStatus = 'active';

    public string $yearDescription = '';

    // --- Terms State ---
    public ?int $termYearFilter = null;

    public bool $showTermModal = false;

    public ?int $editingTermId = null;

    public ?int $termAcademicYearId = null;

    public string $termName = '';

    public string $termStartsOn = '';

    public string $termEndsOn = '';

    public bool $termIsCurrent = false;

    // --- Batches State ---
    public string $batchSearch = '';

    public string $batchKindFilter = 'all';

    public ?int $batchYearFilter = null;

    public bool $showBatchModal = false;

    public ?int $editingBatchId = null;

    public ?int $batchAcademicYearId = null;

    public string $batchName = '';

    public string $batchCode = '';

    public string $batchKind = 'institute_batch';

    public string $batchStatus = 'active';

    public string $batchDescription = '';

    public function mount(): void
    {
        $institutionId = current_institution_id() ?? 1;

        // Auto-select current academic year for term filtering if available
        $currentYear = AcademicYear::where('institution_id', $institutionId)
            ->where('is_current', true)
            ->first();

        if ($currentYear) {
            $this->termYearFilter = $currentYear->id;
        } else {
            $firstYear = AcademicYear::where('institution_id', $institutionId)->first();
            $this->termYearFilter = $firstYear?->id;
        }
    }

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['years', 'terms', 'batches'], true)) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function updatingYearSearch(): void
    {
        $this->resetPage();
    }

    public function updatingYearStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingBatchSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBatchKindFilter(): void
    {
        $this->resetPage();
    }

    // ==========================================
    // Academic Years Actions
    // ==========================================

    public function openCreateYearModal(): void
    {
        $this->resetYearFields();
        $this->showYearModal = true;
    }

    public function openEditYearModal(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $year = AcademicYear::where('institution_id', $institutionId)->findOrFail($id);

        $this->editingYearId = $year->id;
        $this->yearName = $year->name;
        $this->yearCode = $year->code ?? '';
        $this->yearStartsOn = $year->starts_on->format('Y-m-d');
        $this->yearEndsOn = $year->ends_on->format('Y-m-d');
        $this->yearIsCurrent = $year->is_current;
        $this->yearStatus = $year->status;
        $this->yearDescription = $year->description ?? '';

        $this->showYearModal = true;
    }

    public function closeYearModal(): void
    {
        $this->showYearModal = false;
        $this->resetYearFields();
    }

    private function resetYearFields(): void
    {
        $this->editingYearId = null;
        $this->yearName = '';
        $this->yearCode = '';
        $this->yearStartsOn = now()->format('Y-01-01');
        $this->yearEndsOn = now()->format('Y-12-31');
        $this->yearIsCurrent = false;
        $this->yearStatus = 'active';
        $this->yearDescription = '';
        $this->resetErrorBag();
    }

    public function saveYear(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'yearName' => ['required', 'string', 'min:3', 'max:100'],
            'yearCode' => ['nullable', 'string', 'max:50'],
            'yearStartsOn' => ['required', 'date'],
            'yearEndsOn' => ['required', 'date', 'after_or_equal:yearStartsOn'],
            'yearStatus' => ['required', Rule::in(['active', 'archived', 'upcoming'])],
            'yearDescription' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->editingYearId) {
            $year = AcademicYear::where('institution_id', $institutionId)->findOrFail($this->editingYearId);
            $year->update([
                'name' => trim($this->yearName),
                'code' => trim($this->yearCode) ?: null,
                'starts_on' => $this->yearStartsOn,
                'ends_on' => $this->yearEndsOn,
                'status' => $this->yearStatus,
                'description' => trim($this->yearDescription) ?: null,
            ]);

            if ($this->yearIsCurrent) {
                $year->markAsCurrent();
            }

            audit('academic_year.updated', "Updated academic year {$year->name}", $year);
            session()->flash('success', __("Academic year ':name' updated successfully.", ['name' => $year->name]));
        } else {
            $year = AcademicYear::create([
                'institution_id' => $institutionId,
                'name' => trim($this->yearName),
                'code' => trim($this->yearCode) ?: null,
                'starts_on' => $this->yearStartsOn,
                'ends_on' => $this->yearEndsOn,
                'is_current' => false,
                'status' => $this->yearStatus,
                'description' => trim($this->yearDescription) ?: null,
            ]);

            if ($this->yearIsCurrent) {
                $year->markAsCurrent();
            }

            audit('academic_year.created', "Created academic year {$year->name}", $year);
            session()->flash('success', __("Academic year ':name' created successfully.", ['name' => $year->name]));
        }

        $this->closeYearModal();
    }

    public function setYearAsCurrent(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $year = AcademicYear::where('institution_id', $institutionId)->findOrFail($id);

        $year->markAsCurrent();

        audit('academic_year.set_current', "Marked academic year {$year->name} as current", $year);
        session()->flash('success', __("':name' is now marked as the current academic year.", ['name' => $year->name]));
    }

    public function deleteYear(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $year = AcademicYear::where('institution_id', $institutionId)->withCount(['classes', 'terms'])->findOrFail($id);

        if ($year->classes_count > 0) {
            session()->flash('error', __('Cannot delete academic year with :count linked classes. Please reassign or archive instead.', ['count' => $year->classes_count]));

            return;
        }

        $name = $year->name;
        $year->delete();

        audit('academic_year.deleted', "Deleted academic year {$name}");
        session()->flash('success', __("Academic year ':name' deleted successfully.", ['name' => $name]));
    }

    // ==========================================
    // Terms Actions
    // ==========================================

    public function openCreateTermModal(?int $academicYearId = null): void
    {
        $this->resetTermFields();
        $this->termAcademicYearId = $academicYearId ?? $this->termYearFilter;
        $this->showTermModal = true;
    }

    public function openEditTermModal(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $term = Term::where('institution_id', $institutionId)->findOrFail($id);

        $this->editingTermId = $term->id;
        $this->termAcademicYearId = $term->academic_year_id;
        $this->termName = $term->name;
        $this->termStartsOn = $term->starts_on->format('Y-m-d');
        $this->termEndsOn = $term->ends_on->format('Y-m-d');
        $this->termIsCurrent = $term->is_current;

        $this->showTermModal = true;
    }

    public function closeTermModal(): void
    {
        $this->showTermModal = false;
        $this->resetTermFields();
    }

    private function resetTermFields(): void
    {
        $this->editingTermId = null;
        $this->termAcademicYearId = $this->termYearFilter;
        $this->termName = '';
        $this->termStartsOn = now()->format('Y-m-d');
        $this->termEndsOn = now()->addMonths(3)->format('Y-m-d');
        $this->termIsCurrent = false;
        $this->resetErrorBag();
    }

    public function saveTerm(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'termAcademicYearId' => ['required', 'integer', 'exists:academic_years,id'],
            'termName' => ['required', 'string', 'min:2', 'max:100'],
            'termStartsOn' => ['required', 'date'],
            'termEndsOn' => ['required', 'date', 'after_or_equal:termStartsOn'],
        ]);

        if ($this->editingTermId) {
            $term = Term::where('institution_id', $institutionId)->findOrFail($this->editingTermId);
            $term->update([
                'academic_year_id' => $this->termAcademicYearId,
                'name' => trim($this->termName),
                'starts_on' => $this->termStartsOn,
                'ends_on' => $this->termEndsOn,
            ]);

            if ($this->termIsCurrent) {
                $term->markAsCurrent();
            }

            audit('term.updated', "Updated term {$term->name} for academic year #{$term->academic_year_id}", $term);
            session()->flash('success', __("Term ':name' updated successfully.", ['name' => $term->name]));
        } else {
            $term = Term::create([
                'institution_id' => $institutionId,
                'academic_year_id' => $this->termAcademicYearId,
                'name' => trim($this->termName),
                'starts_on' => $this->termStartsOn,
                'ends_on' => $this->termEndsOn,
                'is_current' => false,
            ]);

            if ($this->termIsCurrent) {
                $term->markAsCurrent();
            }

            audit('term.created', "Created term {$term->name} for academic year #{$term->academic_year_id}", $term);
            session()->flash('success', __("Term ':name' created successfully.", ['name' => $term->name]));
        }

        $this->closeTermModal();
    }

    public function setTermAsCurrent(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $term = Term::where('institution_id', $institutionId)->findOrFail($id);

        $term->markAsCurrent();

        audit('term.set_current', "Marked term {$term->name} as current for year #{$term->academic_year_id}", $term);
        session()->flash('success', __("':name' is now marked as the current term.", ['name' => $term->name]));
    }

    public function deleteTerm(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $term = Term::where('institution_id', $institutionId)->findOrFail($id);

        $name = $term->name;
        $term->delete();

        audit('term.deleted', "Deleted term {$name}");
        session()->flash('success', __("Term ':name' deleted successfully.", ['name' => $name]));
    }

    // ==========================================
    // Batches & Groups Actions
    // ==========================================

    public function openCreateBatchModal(): void
    {
        $this->resetBatchFields();
        $this->showBatchModal = true;
    }

    public function openEditBatchModal(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $batch = Batch::where('institution_id', $institutionId)->findOrFail($id);

        $this->editingBatchId = $batch->id;
        $this->batchAcademicYearId = $batch->academic_year_id;
        $this->batchName = $batch->name;
        $this->batchCode = $batch->code ?? '';
        $this->batchKind = $batch->kind;
        $this->batchStatus = $batch->status;
        $this->batchDescription = $batch->description ?? '';

        $this->showBatchModal = true;
    }

    public function closeBatchModal(): void
    {
        $this->showBatchModal = false;
        $this->resetBatchFields();
    }

    private function resetBatchFields(): void
    {
        $this->editingBatchId = null;
        $this->batchAcademicYearId = $this->termYearFilter;
        $this->batchName = '';
        $this->batchCode = '';
        $this->batchKind = 'institute_batch';
        $this->batchStatus = 'active';
        $this->batchDescription = '';
        $this->resetErrorBag();
    }

    public function saveBatch(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'batchName' => ['required', 'string', 'min:2', 'max:100'],
            'batchCode' => ['nullable', 'string', 'max:50'],
            'batchKind' => ['required', Rule::in(['institute_batch', 'nursery_group'])],
            'batchAcademicYearId' => ['nullable', 'integer', 'exists:academic_years,id'],
            'batchStatus' => ['required', Rule::in(['active', 'archived'])],
            'batchDescription' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->editingBatchId) {
            $batch = Batch::where('institution_id', $institutionId)->findOrFail($this->editingBatchId);
            $batch->update([
                'academic_year_id' => $this->batchAcademicYearId,
                'name' => trim($this->batchName),
                'code' => trim($this->batchCode) ?: null,
                'kind' => $this->batchKind,
                'status' => $this->batchStatus,
                'description' => trim($this->batchDescription) ?: null,
            ]);

            audit('batch.updated', "Updated batch {$batch->name}", $batch);
            session()->flash('success', __("Batch/Group ':name' updated successfully.", ['name' => $batch->name]));
        } else {
            $batch = Batch::create([
                'institution_id' => $institutionId,
                'academic_year_id' => $this->batchAcademicYearId,
                'name' => trim($this->batchName),
                'code' => trim($this->batchCode) ?: null,
                'kind' => $this->batchKind,
                'status' => $this->batchStatus,
                'description' => trim($this->batchDescription) ?: null,
            ]);

            audit('batch.created', "Created batch {$batch->name}", $batch);
            session()->flash('success', __("Batch/Group ':name' created successfully.", ['name' => $batch->name]));
        }

        $this->closeBatchModal();
    }

    public function toggleBatchStatus(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $batch = Batch::where('institution_id', $institutionId)->findOrFail($id);

        $newStatus = $batch->status === 'active' ? 'archived' : 'active';
        $batch->update(['status' => $newStatus]);

        audit('batch.status_toggled', "Changed batch {$batch->name} status to {$newStatus}", $batch);
        session()->flash('success', __("Batch ':name' marked as :status.", ['name' => $batch->name, 'status' => $newStatus]));
    }

    public function deleteBatch(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $batch = Batch::where('institution_id', $institutionId)->withCount('classes')->findOrFail($id);

        if ($batch->classes_count > 0) {
            session()->flash('error', __('Cannot delete batch with :count linked classes. Please reassign classes first.', ['count' => $batch->classes_count]));

            return;
        }

        $name = $batch->name;
        $batch->delete();

        audit('batch.deleted', "Deleted batch {$name}");
        session()->flash('success', __("Batch/Group ':name' deleted successfully.", ['name' => $name]));
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;

        // All academic years for selector dropdowns
        $allYears = AcademicYear::where('institution_id', $institutionId)
            ->orderByDesc('starts_on')
            ->get();

        // 1. Years Tab Data
        $yearQuery = AcademicYear::where('institution_id', $institutionId)
            ->withCount(['classes', 'terms', 'batches']);

        if ($this->yearStatusFilter !== 'all') {
            $yearQuery->where('status', $this->yearStatusFilter);
        }

        if (! empty(trim($this->yearSearch))) {
            $term = '%'.trim($this->yearSearch).'%';
            $yearQuery->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term);
            });
        }

        $years = $yearQuery->orderByDesc('starts_on')->paginate(10, ['*'], 'years_page');

        // 2. Terms Tab Data
        $termQuery = Term::where('institution_id', $institutionId)->with('academicYear');

        if ($this->termYearFilter) {
            $termQuery->where('academic_year_id', $this->termYearFilter);
        }

        $terms = $termQuery->orderBy('starts_on')->paginate(10, ['*'], 'terms_page');

        // 3. Batches Tab Data
        $batchQuery = Batch::where('institution_id', $institutionId)
            ->with('academicYear')
            ->withCount('classes');

        if ($this->batchKindFilter !== 'all') {
            $batchQuery->where('kind', $this->batchKindFilter);
        }

        if ($this->batchYearFilter) {
            $batchQuery->where('academic_year_id', $this->batchYearFilter);
        }

        if (! empty(trim($this->batchSearch))) {
            $term = '%'.trim($this->batchSearch).'%';
            $batchQuery->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term);
            });
        }

        $batches = $batchQuery->orderBy('name')->paginate(10, ['*'], 'batches_page');

        return view('livewire.admin.academic-structure-manager', [
            'allYears' => $allYears,
            'years' => $years,
            'terms' => $terms,
            'batches' => $batches,
        ]);
    }
}
