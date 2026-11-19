<?php

namespace App\Livewire\Admin;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\EnrollmentService;
use App\Services\ScheduleClashDetector;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ClassEnrollmentManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public int $classId;

    public string $activeTab = 'active'; // 'active', 'waitlisted', 'dropped'

    public string $search = '';

    // Enroll Modal State
    public bool $showEnrollModal = false;

    public ?int $selectedStudentId = null;

    public string $studentSearch = '';

    public string $enrollSource = 'admin';

    public string $enrollNotes = '';

    public bool $acknowledgeClash = false;

    /**
     * @var array<int, array{
     *     conflicting_class_id: int,
     *     conflicting_class_title: string,
     *     weekday: int,
     *     weekday_name: string,
     *     target_time: string,
     *     conflicting_time: string,
     *     message: string
     * }>
     */
    public array $detectedClashes = [];

    // Drop Modal State
    public bool $showDropModal = false;

    public ?int $droppingEnrollmentId = null;

    public string $dropReason = '';

    public function mount(int $classId): void
    {
        $this->classId = $classId;
        $this->loadClass();
    }

    private function loadClass(): SchoolClass
    {
        $institutionId = current_institution_id() ?? 1;

        return SchoolClass::where('institution_id', $institutionId)
            ->with(['academicYear', 'teacher', 'schedules'])
            ->findOrFail($this->classId);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['active', 'waitlisted', 'dropped'], true)) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    // ==========================================
    // Enroll Modal Actions
    // ==========================================

    public function openEnrollModal(): void
    {
        $this->resetEnrollFields();
        $this->showEnrollModal = true;
    }

    public function closeEnrollModal(): void
    {
        $this->showEnrollModal = false;
        $this->resetEnrollFields();
    }

    private function resetEnrollFields(): void
    {
        $this->selectedStudentId = null;
        $this->studentSearch = '';
        $this->enrollSource = 'admin';
        $this->enrollNotes = '';
        $this->acknowledgeClash = false;
        $this->detectedClashes = [];
        $this->resetErrorBag();
    }

    public function selectStudent(int $studentId, ScheduleClashDetector $clashDetector): void
    {
        $institutionId = current_institution_id() ?? 1;
        $student = Student::where('institution_id', $institutionId)->findOrFail($studentId);

        $this->selectedStudentId = $student->id;
        $this->studentSearch = $student->full_name.' ('.$student->student_code.')';

        // Check for clashes live
        $class = $this->loadClass();
        $this->detectedClashes = $clashDetector->detectClashes($student, $class);
        $this->acknowledgeClash = false;
    }

    public function clearSelectedStudent(): void
    {
        $this->selectedStudentId = null;
        $this->studentSearch = '';
        $this->detectedClashes = [];
        $this->acknowledgeClash = false;
    }

    public function enrollStudent(EnrollmentService $enrollmentService): void
    {
        $this->validate([
            'selectedStudentId' => ['required', 'integer', 'exists:students,id'],
            'enrollSource' => ['required', 'string', 'max:50'],
            'enrollNotes' => ['nullable', 'string', 'max:500'],
        ], [
            'selectedStudentId.required' => __('Please select a student to enroll.'),
        ]);

        if (! empty($this->detectedClashes) && ! $this->acknowledgeClash) {
            $this->addError('acknowledgeClash', __('Please acknowledge the schedule clash warning to proceed with enrollment.'));

            return;
        }

        $institutionId = current_institution_id() ?? 1;
        $class = $this->loadClass();
        $student = Student::where('institution_id', $institutionId)->findOrFail($this->selectedStudentId);

        $result = $enrollmentService->enroll($class, $student, [
            'override_clash' => $this->acknowledgeClash,
            'source' => $this->enrollSource,
            'notes' => trim($this->enrollNotes) ?: null,
        ]);

        if (! $result['success']) {
            session()->flash('error', $result['message']);

            return;
        }

        $this->closeEnrollModal();

        if ($result['status'] === 'waitlisted') {
            $this->activeTab = 'waitlisted';
            session()->flash('info', $result['message']);
        } else {
            $this->activeTab = 'active';
            session()->flash('success', $result['message']);
        }
    }

    // ==========================================
    // Drop Actions
    // ==========================================

    public function openDropModal(int $enrollmentId): void
    {
        $institutionId = current_institution_id() ?? 1;
        $enrollment = Enrollment::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->findOrFail($enrollmentId);

        $this->droppingEnrollmentId = $enrollment->id;
        $this->dropReason = '';
        $this->showDropModal = true;
    }

    public function closeDropModal(): void
    {
        $this->droppingEnrollmentId = null;
        $this->dropReason = '';
        $this->showDropModal = false;
        $this->resetErrorBag();
    }

    public function confirmDrop(EnrollmentService $enrollmentService): void
    {
        if (! $this->droppingEnrollmentId) {
            return;
        }

        $institutionId = current_institution_id() ?? 1;
        $enrollment = Enrollment::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->findOrFail($this->droppingEnrollmentId);

        $studentName = $enrollment->student->full_name ?? 'Student';
        $enrollmentService->drop($enrollment, $this->dropReason);

        $this->closeDropModal();
        session()->flash('success', __("Enrollment for ':name' has been dropped.", ['name' => $studentName]));
    }

    // ==========================================
    // Promote From Waitlist Action
    // ==========================================

    public function promoteWaitlisted(int $enrollmentId, EnrollmentService $enrollmentService): void
    {
        $institutionId = current_institution_id() ?? 1;
        $enrollment = Enrollment::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->findOrFail($enrollmentId);

        $result = $enrollmentService->promoteFromWaitlist($enrollment);

        if ($result['success']) {
            session()->flash('success', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    // ==========================================
    // Restore Dropped Student Action
    // ==========================================

    public function restoreDropped(int $enrollmentId, EnrollmentService $enrollmentService): void
    {
        $institutionId = current_institution_id() ?? 1;
        $enrollment = Enrollment::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->findOrFail($enrollmentId);

        if (! $enrollment->student) {
            session()->flash('error', __('Student record not found.'));

            return;
        }

        $class = $this->loadClass();
        $result = $enrollmentService->enroll($class, $enrollment->student, [
            'override_clash' => true,
            'source' => 'restored',
            'notes' => 'Restored by admin on '.now()->format('Y-m-d H:i'),
        ]);

        if ($result['success']) {
            session()->flash('success', __("Student ':name' has been re-enrolled.", ['name' => $enrollment->student->full_name]));
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;
        $class = $this->loadClass();

        // Capacity Counts
        $activeCount = Enrollment::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->where('status', EnrollmentStatus::Active->value)
            ->count();

        $waitlistCount = Enrollment::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->where('status', EnrollmentStatus::Waitlisted->value)
            ->count();

        $droppedCount = Enrollment::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->where('status', EnrollmentStatus::Dropped->value)
            ->count();

        $availableSeats = max(0, $class->capacity - $activeCount);

        // Tab Query
        $query = Enrollment::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->with(['student.user', 'student.guardians.user']);

        if ($this->activeTab === 'active') {
            $query->where('status', EnrollmentStatus::Active->value)->orderBy('enrolled_at');
        } elseif ($this->activeTab === 'waitlisted') {
            $query->where('status', EnrollmentStatus::Waitlisted->value)->orderBy('enrolled_at');
        } else {
            $query->where('status', EnrollmentStatus::Dropped->value)->orderByDesc('dropped_at');
        }

        if (! empty(trim($this->search))) {
            $term = '%'.trim($this->search).'%';
            $query->whereHas('student', function (Builder $q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('student_code', 'like', $term);
            });
        }

        $enrollments = $query->paginate(15);

        // Student search candidates for modal
        $candidateStudents = [];
        if ($this->showEnrollModal && ! empty(trim($this->studentSearch)) && ! $this->selectedStudentId) {
            $term = '%'.trim($this->studentSearch).'%';
            $candidateStudents = Student::where('institution_id', $institutionId)
                ->where(function (Builder $q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('student_code', 'like', $term);
                })
                ->whereNotIn('id', function ($sub) {
                    $sub->select('student_id')
                        ->from('enrollments')
                        ->where('class_id', $this->classId)
                        ->whereIn('status', [EnrollmentStatus::Active->value, EnrollmentStatus::Waitlisted->value]);
                })
                ->take(8)
                ->get();
        }

        return view('livewire.admin.class-enrollment-manager', [
            'class' => $class,
            'activeCount' => $activeCount,
            'waitlistCount' => $waitlistCount,
            'droppedCount' => $droppedCount,
            'availableSeats' => $availableSeats,
            'enrollments' => $enrollments,
            'candidateStudents' => $candidateStudents,
        ]);
    }
}
