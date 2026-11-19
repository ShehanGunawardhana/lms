<?php

namespace App\Livewire\Admin;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Services\AcademicRolloverService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AcademicRolloverManager extends Component
{
    use AuthorizesRequests;

    public ?int $sourceYearId = null;

    public ?int $targetYearId = null;

    /**
     * @var array<int, int>
     */
    public array $selectedClassIds = [];

    public bool $copySchedules = true;

    public bool $rolloverStudents = false;

    public bool $archiveSourceYear = true;

    public bool $archiveSourceClasses = true;

    public bool $completeSourceEnrollments = true;

    public bool $setTargetAsCurrent = true;

    // Quick-create Target Year Modal
    public bool $showCreateYearModal = false;

    public string $newYearName = '';

    public string $newYearStartsOn = '';

    public string $newYearEndsOn = '';

    /**
     * @var array{
     *     copied_classes?: int,
     *     copied_schedules?: int,
     *     rolled_over_students?: int,
     *     message?: string
     * }|null
     */
    public ?array $rolloverResult = null;

    public function mount(): void
    {
        $institutionId = current_institution_id() ?? 1;

        // Auto-select current active year as source year
        $current = AcademicYear::where('institution_id', $institutionId)
            ->where('is_current', true)
            ->first();

        if ($current) {
            $this->sourceYearId = $current->id;
            $this->loadSourceClasses();
        }

        // Initialize default dates for new year modal
        $this->newYearStartsOn = now()->addYear()->startOfYear()->format('Y-01-01');
        $this->newYearEndsOn = now()->addYear()->endOfYear()->format('Y-12-31');
        $this->newYearName = now()->addYear()->format('Y').' Academic Year';
    }

    public function updatedSourceYearId(): void
    {
        $this->loadSourceClasses();
    }

    private function loadSourceClasses(): void
    {
        if (! $this->sourceYearId) {
            $this->selectedClassIds = [];

            return;
        }

        $institutionId = current_institution_id() ?? 1;
        $classIds = SchoolClass::where('institution_id', $institutionId)
            ->where('academic_year_id', $this->sourceYearId)
            ->pluck('id')
            ->all();

        $this->selectedClassIds = $classIds;
    }

    public function selectAllClasses(): void
    {
        $this->loadSourceClasses();
    }

    public function deselectAllClasses(): void
    {
        $this->selectedClassIds = [];
    }

    public function openCreateYearModal(): void
    {
        $this->showCreateYearModal = true;
    }

    public function closeCreateYearModal(): void
    {
        $this->showCreateYearModal = false;
    }

    public function createTargetYear(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'newYearName' => ['required', 'string', 'min:3', 'max:100'],
            'newYearStartsOn' => ['required', 'date'],
            'newYearEndsOn' => ['required', 'date', 'after:newYearStartsOn'],
        ]);

        $year = AcademicYear::create([
            'institution_id' => $institutionId,
            'name' => trim($this->newYearName),
            'starts_on' => $this->newYearStartsOn,
            'ends_on' => $this->newYearEndsOn,
            'status' => 'upcoming',
            'is_current' => false,
        ]);

        $this->targetYearId = $year->id;
        $this->closeCreateYearModal();
        session()->flash('success', __("Academic year ':name' created and selected as target.", ['name' => $year->name]));
    }

    public function executeRollover(AcademicRolloverService $rolloverService): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'sourceYearId' => ['required', 'integer', 'exists:academic_years,id'],
            'targetYearId' => ['required', 'integer', 'exists:academic_years,id', 'different:sourceYearId'],
        ], [
            'targetYearId.different' => __('Target academic year must be different from source academic year.'),
        ]);

        $sourceYear = AcademicYear::where('institution_id', $institutionId)->findOrFail($this->sourceYearId);
        $targetYear = AcademicYear::where('institution_id', $institutionId)->findOrFail($this->targetYearId);

        $result = $rolloverService->performRollover($sourceYear, $targetYear, [
            'copy_class_ids' => $this->selectedClassIds,
            'copy_schedules' => $this->copySchedules,
            'rollover_students' => $this->rolloverStudents,
            'archive_source_year' => $this->archiveSourceYear,
            'archive_source_classes' => $this->archiveSourceClasses,
            'complete_source_enrollments' => $this->completeSourceEnrollments,
            'set_target_as_current' => $this->setTargetAsCurrent,
        ]);

        $this->rolloverResult = $result;
        session()->flash('success', $result['message']);
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;

        $allYears = AcademicYear::where('institution_id', $institutionId)
            ->withCount(['classes'])
            ->orderByDesc('starts_on')
            ->get();

        $sourceClasses = [];
        if ($this->sourceYearId) {
            $sourceClasses = SchoolClass::where('institution_id', $institutionId)
                ->where('academic_year_id', $this->sourceYearId)
                ->with(['teacher', 'schedules'])
                ->withCount('enrollments')
                ->get();
        }

        return view('livewire.admin.academic-rollover-manager', [
            'allYears' => $allYears,
            'sourceClasses' => $sourceClasses,
        ]);
    }
}
