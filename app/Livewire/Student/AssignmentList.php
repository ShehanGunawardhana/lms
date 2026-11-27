<?php

declare(strict_types=1);

namespace App\Livewire\Student;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentSubmissionStatus;
use App\Enums\RoleName;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Coursework & Assignments')]
class AssignmentList extends Component
{
    use AuthorizesRequests;

    public ?int $selectedStudentId = null;

    public string $search = '';

    public string $statusFilter = 'all'; // all, todo, submitted, graded, returned

    public string $classFilter = 'all';

    public function mount(?int $student = null): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var User $user */
        $user = auth()->user();

        if ($user->hasRole(RoleName::Student->value)) {
            $studentRecord = Student::where('institution_id', $institutionId)
                ->where('user_id', $user->id)
                ->first();

            if (! $studentRecord) {
                abort(403, __('No active student record linked to your account.'));
            }

            $this->selectedStudentId = $studentRecord->id;
        } elseif ($user->hasRole(RoleName::Parent->value)) {
            $guardian = Guardian::where('institution_id', $institutionId)
                ->where('user_id', $user->id)
                ->first();

            if (! $guardian) {
                abort(403, __('No guardian profile found.'));
            }

            /** @var Collection<int, Student> $children */
            $children = $guardian->students()
                ->whereHas('classes', fn ($q) => $q->where('enrollments.status', 'active'))
                ->get();

            if ($children->isEmpty()) {
                abort(403, __('None of your children are enrolled in coursework classes.'));
            }

            if ($student !== null && $children->contains('id', $student)) {
                $this->selectedStudentId = $student;
            } else {
                $this->selectedStudentId = $children->first()->id;
            }
        } elseif ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value, RoleName::Teacher->value])) {
            $firstStudent = Student::where('institution_id', $institutionId)
                ->whereHas('classes', fn ($q) => $q->where('enrollments.status', 'active'))
                ->first();

            $this->selectedStudentId = $student ?? $firstStudent?->id;
        } else {
            abort(403);
        }
    }

    public function selectStudent(int $studentId): void
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->hasRole(RoleName::Parent->value)) {
            $guardian = Guardian::where('user_id', $user->id)->first();
            $hasChild = $guardian && $guardian->students()->where('students.id', $studentId)->exists();
            if ($hasChild) {
                $this->selectedStudentId = $studentId;
            }
        } else {
            $this->selectedStudentId = $studentId;
        }
    }

    public function getStudent(): ?Student
    {
        if (! $this->selectedStudentId) {
            return null;
        }

        return Student::find($this->selectedStudentId);
    }

    /**
     * @return Collection<int, Student>
     */
    public function getChildrenProperty(): Collection
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->hasRole(RoleName::Parent->value)) {
            return new Collection;
        }

        $guardian = Guardian::where('user_id', $user->id)->first();
        if (! $guardian) {
            return new Collection;
        }

        return $guardian->students()
            ->whereHas('classes', fn ($q) => $q->where('enrollments.status', 'active'))
            ->get();
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;
        $student = $this->getStudent();

        if (! $student) {
            return view('livewire.student.assignment-list', [
                'assignmentsData' => [],
                'enrolledClasses' => new Collection,
                'totalCount' => 0,
                'todoCount' => 0,
                'submittedCount' => 0,
                'gradedCount' => 0,
                'returnedCount' => 0,
                'averagePercentage' => null,
                'passedCount' => 0,
                'children' => $this->getChildrenProperty(),
                'student' => null,
            ]);
        }

        // 1. Classes student is actively enrolled in
        /** @var Collection<int, SchoolClass> $enrolledClasses */
        $enrolledClasses = $student->classes()
            ->wherePivot('status', 'active')
            ->orderBy('title')
            ->get();

        $classIds = $enrolledClasses->pluck('id')->all();

        // 2. Published assignments for enrolled classes
        /** @var Collection<int, Assignment> $assignments */
        $assignments = Assignment::where('institution_id', $institutionId)
            ->whereIn('class_id', $classIds)
            ->where('status', AssignmentStatus::Published)
            ->with(['schoolClass', 'attachments', 'extensions.student', 'creator'])
            ->get();

        // 3. Submissions for this student
        /** @var Collection<int, AssignmentSubmission> $rawSubmissions */
        $rawSubmissions = AssignmentSubmission::where('institution_id', $institutionId)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->where('student_id', $student->id)
            ->with(['reviewer', 'fileAsset'])
            ->orderByDesc('version')
            ->get();

        $latestSubmissions = $rawSubmissions->groupBy('assignment_id')->map(fn ($group) => $group->first());

        // 4. Compute metrics and filter items
        $totalCount = $assignments->count();
        $todoCount = 0;
        $submittedCount = 0;
        $gradedCount = 0;
        $returnedCount = 0;
        $passedCount = 0;
        $gradedScoreSum = 0;
        $gradedScoreTotal = 0;

        /** @var list<array{assignment: Assignment, submission: AssignmentSubmission|null, effective_deadline: Carbon, is_extended: bool, is_past_deadline: bool, status_key: string, percentage: int|null, is_passed: bool|null}> $items */
        $items = [];

        foreach ($assignments as $assignment) {
            /** @var AssignmentSubmission|null $submission */
            $submission = $latestSubmissions->get($assignment->id);
            $effectiveDeadline = $assignment->effectiveDeadlineForStudent($student->id);
            $isExtended = $assignment->isExtendedForStudent($student->id);
            $isPastDeadline = $assignment->isPastDeadlineForStudent($student->id);

            $statusKey = $submission !== null ? $submission->status->value : 'not_submitted';

            // Metrics
            if ($submission === null) {
                $todoCount++;
            } elseif ($submission->status === AssignmentSubmissionStatus::Graded) {
                $gradedCount++;
                if ($submission->marks !== null && $assignment->total_marks > 0) {
                    $gradedScoreSum += $submission->marks;
                    $gradedScoreTotal += $assignment->total_marks;
                    if ($assignment->pass_marks === null || $submission->marks >= $assignment->pass_marks) {
                        $passedCount++;
                    }
                }
            } elseif ($submission->status === AssignmentSubmissionStatus::Returned) {
                $returnedCount++;
            } else {
                $submittedCount++;
            }

            // Filtering
            if ($this->classFilter !== 'all' && (string) $assignment->class_id !== (string) $this->classFilter) {
                continue;
            }

            if (trim($this->search) !== '') {
                $needle = mb_strtolower(trim($this->search));
                $matchesTitle = str_contains(mb_strtolower($assignment->title), $needle);
                $matchesDesc = str_contains(mb_strtolower((string) $assignment->description), $needle);
                $matchesClass = str_contains(mb_strtolower((string) $assignment->schoolClass?->title), $needle);
                if (! $matchesTitle && ! $matchesDesc && ! $matchesClass) {
                    continue;
                }
            }

            if ($this->statusFilter === 'todo' && $submission !== null) {
                continue;
            }
            if ($this->statusFilter === 'submitted' && (! $submission || ! in_array($submission->status, [AssignmentSubmissionStatus::Submitted, AssignmentSubmissionStatus::Late], true))) {
                continue;
            }
            if ($this->statusFilter === 'graded' && (! $submission || $submission->status !== AssignmentSubmissionStatus::Graded)) {
                continue;
            }
            if ($this->statusFilter === 'returned' && (! $submission || $submission->status !== AssignmentSubmissionStatus::Returned)) {
                continue;
            }

            $percentage = ($submission && $submission->marks !== null && $assignment->total_marks > 0)
                ? (int) round(($submission->marks / $assignment->total_marks) * 100)
                : null;

            $isPassed = ($submission && $submission->marks !== null)
                ? ($assignment->pass_marks === null || $submission->marks >= $assignment->pass_marks)
                : null;

            $items[] = [
                'assignment' => $assignment,
                'submission' => $submission,
                'effective_deadline' => $effectiveDeadline,
                'is_extended' => $isExtended,
                'is_past_deadline' => $isPastDeadline,
                'status_key' => $statusKey,
                'percentage' => $percentage,
                'is_passed' => $isPassed,
            ];
        }

        // Sort items: Actionable (Returned > Todo > Submitted > Graded), then by deadline
        usort($items, function ($a, $b) {
            $priority = [
                'returned' => 0,
                'not_submitted' => 1,
                'submitted' => 2,
                'late' => 2,
                'graded' => 3,
            ];
            $pA = $priority[$a['status_key']] ?? 4;
            $pB = $priority[$b['status_key']] ?? 4;

            if ($pA !== $pB) {
                return $pA <=> $pB;
            }

            return $a['effective_deadline'] <=> $b['effective_deadline'];
        });

        $averagePercentage = $gradedScoreTotal > 0
            ? (int) round(($gradedScoreSum / $gradedScoreTotal) * 100)
            : null;

        return view('livewire.student.assignment-list', [
            'items' => $items,
            'enrolledClasses' => $enrolledClasses,
            'totalCount' => $totalCount,
            'todoCount' => $todoCount,
            'submittedCount' => $submittedCount,
            'gradedCount' => $gradedCount,
            'returnedCount' => $returnedCount,
            'averagePercentage' => $averagePercentage,
            'passedCount' => $passedCount,
            'children' => $this->getChildrenProperty(),
            'student' => $student,
        ]);
    }
}
