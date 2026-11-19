<?php

namespace App\Livewire\Teacher;

use App\Enums\EnrollmentStatus;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\TeacherProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TeacherDashboard extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $activeSection = 'overview'; // 'overview', 'classes', 'sessions', 'students'

    public ?int $selectedClassId = null;

    public string $studentSearch = '';

    public function updatingStudentSearch(): void
    {
        $this->resetPage();
    }

    public function selectClassForRoster(?int $classId): void
    {
        $this->selectedClassId = $classId;
        $this->activeSection = 'students';
        $this->resetPage();
    }

    public function setSection(string $section): void
    {
        if (in_array($section, ['overview', 'classes', 'sessions', 'students'], true)) {
            $this->activeSection = $section;
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $user = auth()->user();
        $institutionId = current_institution_id() ?? 1;

        // 1. My Classes
        $myClasses = SchoolClass::where('institution_id', $institutionId)
            ->where('teacher_id', $user?->id)
            ->with(['academicYear', 'batch', 'schedules'])
            ->withCount(['enrollments' => function (Builder $q) {
                $q->where('status', EnrollmentStatus::Active->value);
            }])
            ->orderBy('title')
            ->get();

        $classIds = $myClasses->pluck('id')->all();

        // 2. Metrics
        $totalClasses = $myClasses->count();

        $totalActiveStudents = Enrollment::where('institution_id', $institutionId)
            ->whereIn('class_id', $classIds)
            ->where('status', EnrollmentStatus::Active->value)
            ->distinct('student_id')
            ->count('student_id');

        $todayDate = now()->toDateString();

        $todaySessionsCount = ClassSession::where('institution_id', $institutionId)
            ->whereIn('class_id', $classIds)
            ->whereDate('date', $todayDate)
            ->where('status', 'scheduled')
            ->count();

        // 3. Upcoming Sessions
        $upcomingSessions = ClassSession::where('institution_id', $institutionId)
            ->whereIn('class_id', $classIds)
            ->whereDate('date', '>=', $todayDate)
            ->where('status', 'scheduled')
            ->with('schoolClass')
            ->orderBy('date')
            ->orderBy('starts_at')
            ->take(8)
            ->get();

        // 4. Teacher Profile / Portfolio Status
        $teacherProfile = TeacherProfile::where('institution_id', $institutionId)
            ->where('user_id', $user?->id)
            ->first();

        // 5. Students Roster Query (Paginated)
        $studentsQuery = Enrollment::where('institution_id', $institutionId)
            ->whereIn('class_id', $classIds)
            ->where('status', EnrollmentStatus::Active->value)
            ->with(['student.user', 'student.guardians.user', 'schoolClass']);

        if ($this->selectedClassId && in_array($this->selectedClassId, $classIds, true)) {
            $studentsQuery->where('class_id', $this->selectedClassId);
        }

        if (! empty(trim($this->studentSearch))) {
            $term = '%'.trim($this->studentSearch).'%';
            $studentsQuery->whereHas('student', function (Builder $q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('student_code', 'like', $term);
            });
        }

        $enrolledStudents = $studentsQuery->orderBy('enrolled_at')->paginate(12, ['*'], 'roster_page');

        return view('livewire.teacher.teacher-dashboard', [
            'myClasses' => $myClasses,
            'totalClasses' => $totalClasses,
            'totalActiveStudents' => $totalActiveStudents,
            'todaySessionsCount' => $todaySessionsCount,
            'upcomingSessions' => $upcomingSessions,
            'teacherProfile' => $teacherProfile,
            'enrolledStudents' => $enrolledStudents,
            'todayDate' => $todayDate,
        ]);
    }
}
