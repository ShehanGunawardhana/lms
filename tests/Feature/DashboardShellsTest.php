<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Institution;
use App\Models\User;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardShellsTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $this->institution = Institution::first();
        app(InstitutionContext::class)->set($this->institution);
    }

    public function test_admin_can_access_admin_dashboard_with_2fa(): void
    {
        $admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1'])),
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole(RoleName::Admin->value);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Institutional Administration');
        $response->assertSee('Parent Approvals');
    }

    public function test_teacher_can_access_teacher_dashboard(): void
    {
        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

        $response->assertOk();
        $response->assertSee('Faculty Workflows');
        $response->assertSee('Assigned Classes');
    }

    public function test_parent_can_access_parent_dashboard(): void
    {
        $parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $parent->assignRole(RoleName::Parent->value);

        $response = $this->actingAs($parent)->get(route('parent.dashboard'));

        $response->assertOk();
        $response->assertSee('Weekly Nursery Learning Feed');
        $response->assertSee('Attendance Today');
    }

    public function test_student_can_access_student_dashboard(): void
    {
        $student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $student->assignRole(RoleName::Student->value);

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('Student Portal');
        $response->assertSee('Scan at Kiosk');
    }

    public function test_cashier_can_access_cashier_dashboard(): void
    {
        $cashier = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $cashier->assignRole(RoleName::Cashier->value);

        $response = $this->actingAs($cashier)->get(route('cashier.dashboard'));

        $response->assertOk();
        $response->assertSee('Cashier &amp; Finance Desk', false);
        $response->assertSee('Slips Pending Review');
    }

    public function test_printing_staff_can_access_printing_dashboard(): void
    {
        $printingStaff = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $printingStaff->assignRole(RoleName::PrintingStaff->value);

        $response = $this->actingAs($printingStaff)->get(route('printing.dashboard'));

        $response->assertOk();
        $response->assertSee('Printing Staff Counter');
        $response->assertSee('Recent Handout Disbursements');
    }

    public function test_attendance_marshal_can_access_attendance_dashboard(): void
    {
        $marshal = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $marshal->assignRole(RoleName::AttendanceMarshal->value);

        $response = $this->actingAs($marshal)->get(route('attendance.dashboard'));

        $response->assertOk();
        $response->assertSee('Attendance Marshal Workspace');
        $response->assertSee('Gate & Turnstile Live Scan Feed');
    }

    public function test_unauthorized_roles_are_forbidden_from_other_dashboards(): void
    {
        $student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $student->assignRole(RoleName::Student->value);

        // Student cannot access admin or teacher dashboards
        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('teacher.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('cashier.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('attendance.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('printing.dashboard'))->assertForbidden();

        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        // Teacher cannot access cashier dashboard
        $this->actingAs($teacher)->get(route('cashier.dashboard'))->assertForbidden();
    }

    public function test_central_dashboard_route_redirects_each_role_to_their_specific_screen(): void
    {
        // Teacher
        $teacher = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);
        $teacher->assignRole(RoleName::Teacher->value);
        $this->actingAs($teacher)->get(route('dashboard'))->assertRedirect(route('teacher.dashboard'));

        // Parent
        $parent = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);
        $parent->assignRole(RoleName::Parent->value);
        $this->actingAs($parent)->get(route('dashboard'))->assertRedirect(route('parent.dashboard'));

        // Student
        $student = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);
        $student->assignRole(RoleName::Student->value);
        $this->actingAs($student)->get(route('dashboard'))->assertRedirect(route('student.dashboard'));

        // Cashier
        $cashier = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);
        $cashier->assignRole(RoleName::Cashier->value);
        $this->actingAs($cashier)->get(route('dashboard'))->assertRedirect(route('cashier.dashboard'));

        // Printing Staff
        $printing = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);
        $printing->assignRole(RoleName::PrintingStaff->value);
        $this->actingAs($printing)->get(route('dashboard'))->assertRedirect(route('printing.dashboard'));

        // Attendance Marshal
        $marshal = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);
        $marshal->assignRole(RoleName::AttendanceMarshal->value);
        $this->actingAs($marshal)->get(route('dashboard'))->assertRedirect(route('attendance.dashboard'));
    }
}
