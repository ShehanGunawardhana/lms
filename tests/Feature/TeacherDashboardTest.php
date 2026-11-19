<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Livewire\Teacher\TeacherDashboard;
use App\Models\AcademicYear;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherProfile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $teacher;

    protected User $otherTeacher;

    protected User $studentUser;

    protected SchoolClass $class;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'Royal Academic Institute',
            'slug' => 'royal-academic',
            'code' => 'RAI',
            'type' => 'institute',
            'is_active' => true,
        ]);

        session(['current_institution_id' => $this->institution->id]);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Prof. Sunil Mendis',
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->otherTeacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Dr. Kamal Perera',
            'status' => 'active',
        ]);
        $this->otherTeacher->assignRole(RoleName::Teacher->value);

        $this->studentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->studentUser->assignRole(RoleName::Student->value);

        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
        ]);

        // Class for this teacher
        $this->class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Pure Mathematics 2026',
            'subject' => 'Mathematics',
            'kind' => 'institute_class',
            'fee_amount' => 12500,
            'capacity' => 50,
            'status' => 'active',
        ]);

        // Student enrolled in this teacher's class
        $this->student = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->studentUser->id,
            'student_code' => 'STU-101',
            'first_name' => 'Sandun',
            'last_name' => 'Fernando',
            'dob' => '2008-05-15',
            'status' => 'active',
            'kind' => 'institute',
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);
    }

    public function test_guest_or_non_teacher_cannot_access_teacher_dashboard(): void
    {
        $this->get(route('teacher.dashboard'))
            ->assertRedirect('/login');

        $this->actingAs($this->studentUser)
            ->get(route('teacher.dashboard'))
            ->assertForbidden();
    }

    public function test_teacher_can_render_dashboard_with_assigned_classes_and_metrics(): void
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.dashboard'));

        $response->assertOk();
        $response->assertSee('Teacher &amp; Lecturer Workspace', false);
        $response->assertSee('Pure Mathematics 2026');
        $response->assertSee('Mathematics');
        $response->assertSee('My Classes');
        $response->assertSee('Enrolled Students');
    }

    public function test_teacher_only_sees_their_own_assigned_classes(): void
    {
        // Class assigned to other teacher
        $otherClass = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $this->class->academic_year_id,
            'teacher_id' => $this->otherTeacher->id,
            'title' => 'Organic Chemistry 2026',
            'subject' => 'Chemistry',
            'kind' => 'institute_class',
            'capacity' => 40,
            'status' => 'active',
        ]);

        $this->actingAs($this->teacher);

        Livewire::test(TeacherDashboard::class)
            ->assertSee('Pure Mathematics 2026')
            ->assertDontSee('Organic Chemistry 2026');
    }

    public function test_upcoming_sessions_and_today_badge_render_correctly(): void
    {
        // Create session for today
        ClassSession::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'title' => 'Math Today Session',
            'date' => now()->toDateString(),
            'starts_at' => '10:00:00',
            'ends_at' => '12:00:00',
            'room' => 'Hall A',
            'status' => 'scheduled',
        ]);

        // Create session for next week
        ClassSession::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'title' => 'Math Next Week Session',
            'date' => now()->addDays(7)->toDateString(),
            'starts_at' => '10:00:00',
            'ends_at' => '12:00:00',
            'room' => 'Hall A',
            'status' => 'scheduled',
        ]);

        $this->actingAs($this->teacher);

        Livewire::test(TeacherDashboard::class)
            ->assertSee('TODAY')
            ->assertSee('Hall A');
    }

    public function test_teacher_can_filter_student_roster_by_class_and_search(): void
    {
        $this->actingAs($this->teacher);

        // Student 2 enrolled in same class
        $student2 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-102',
            'first_name' => 'Ruwan',
            'last_name' => 'Gamage',
            'dob' => '2008-08-10',
            'status' => 'active',
            'kind' => 'institute',
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'student_id' => $student2->id,
            'class_id' => $this->class->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);

        Livewire::test(TeacherDashboard::class)
            ->set('activeSection', 'students')
            ->assertSee('Sandun Fernando')
            ->assertSee('Ruwan Gamage')
            ->set('studentSearch', 'Sandun')
            ->assertSee('Sandun Fernando')
            ->assertDontSee('Ruwan Gamage')
            ->set('studentSearch', '')
            ->assertSee('Ruwan Gamage');
    }

    public function test_teacher_dashboard_reflects_portfolio_status(): void
    {
        TeacherProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->teacher->id,
            'headline' => 'Senior Mathematics Lecturer',
            'portfolio_status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($this->teacher);

        Livewire::test(TeacherDashboard::class)
            ->assertSee('Published');
    }

    public function test_multi_institution_isolation_in_teacher_dashboard(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Other Institution',
            'slug' => 'other-inst',
            'code' => 'OI',
            'type' => 'institute',
            'is_active' => true,
        ]);

        // Class belonging to other institution (even if teacher_id matches)
        SchoolClass::create([
            'institution_id' => $otherInstitution->id,
            'title' => 'Secret Academy Class',
            'subject' => 'Secret',
            'kind' => 'institute_class',
            'teacher_id' => $this->teacher->id,
            'capacity' => 10,
            'status' => 'active',
        ]);

        $this->actingAs($this->teacher);

        Livewire::test(TeacherDashboard::class)
            ->assertDontSee('Secret Academy Class');
    }
}
