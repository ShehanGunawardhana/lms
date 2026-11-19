<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Livewire\Admin\AcademicStructureManager;
use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AcademicStructureTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected User $teacher;

    protected User $student;

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

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'SECRET',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->student->assignRole(RoleName::Student->value);
    }

    public function test_guest_or_non_admin_cannot_access_academic_structure(): void
    {
        $this->get(route('admin.academics'))->assertRedirect('/login');

        $this->actingAs($this->teacher)
            ->get(route('admin.academics'))
            ->assertForbidden();

        $this->actingAs($this->student)
            ->get(route('admin.academics'))
            ->assertForbidden();
    }

    public function test_admin_without_two_factor_is_redirected(): void
    {
        $unconfirmedAdmin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $unconfirmedAdmin->assignRole(RoleName::Admin->value);

        $this->actingAs($unconfirmedAdmin)
            ->get(route('admin.academics'))
            ->assertRedirect(route('two-factor.setup'));
    }

    public function test_admin_with_two_factor_can_render_academic_structure(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.academics'));

        $response->assertOk();
        $response->assertSee('Academic Years, Terms &amp; Batches', false);
        $response->assertSee('Academic Years');
        $response->assertSee('Terms &amp; Semesters', false);
        $response->assertSee('Batches &amp; Groups', false);
    }

    public function test_admin_can_create_academic_year_and_audit_is_recorded(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AcademicStructureManager::class)
            ->call('openCreateYearModal')
            ->assertSet('showYearModal', true)
            ->set('yearName', '2026/2027 Academic Year')
            ->set('yearCode', 'AY-2026')
            ->set('yearStartsOn', '2026-01-01')
            ->set('yearEndsOn', '2026-12-31')
            ->set('yearStatus', 'active')
            ->set('yearIsCurrent', true)
            ->set('yearDescription', 'Main regular academic cycle.')
            ->call('saveYear')
            ->assertSet('showYearModal', false)
            ->assertHasNoErrors();

        $year = AcademicYear::where('institution_id', $this->institution->id)
            ->where('code', 'AY-2026')
            ->first();

        $this->assertNotNull($year);
        $this->assertSame('2026/2027 Academic Year', $year->name);
        $this->assertTrue($year->is_current);

        // Audit log check
        $activity = Activity::where('event', 'academic_year.created')
            ->where('subject_id', $year->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($this->admin->id, $activity->causer_id);
    }

    public function test_setting_academic_year_as_current_unsets_previous(): void
    {
        $this->actingAs($this->admin);

        $year1 = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => 'Year 2025',
            'code' => 'AY-2025',
            'starts_on' => '2025-01-01',
            'ends_on' => '2025-12-31',
            'is_current' => true,
            'status' => 'active',
        ]);

        $year2 = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => 'Year 2026',
            'code' => 'AY-2026',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => false,
            'status' => 'upcoming',
        ]);

        Livewire::test(AcademicStructureManager::class)
            ->call('setYearAsCurrent', $year2->id);

        $this->assertFalse($year1->fresh()->is_current);
        $this->assertTrue($year2->fresh()->is_current);
    }

    public function test_admin_can_create_terms_and_set_current_term(): void
    {
        $this->actingAs($this->admin);

        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => 'Year 2026',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
        ]);

        Livewire::test(AcademicStructureManager::class)
            ->call('setActiveTab', 'terms')
            ->call('openCreateTermModal', $year->id)
            ->set('termAcademicYearId', $year->id)
            ->set('termName', 'Semester 1')
            ->set('termStartsOn', '2026-01-01')
            ->set('termEndsOn', '2026-06-30')
            ->set('termIsCurrent', true)
            ->call('saveTerm')
            ->assertHasNoErrors();

        $term1 = Term::where('academic_year_id', $year->id)
            ->where('name', 'Semester 1')
            ->first();

        $this->assertNotNull($term1);
        $this->assertTrue($term1->is_current);

        // Add second term
        $term2 = Term::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'name' => 'Semester 2',
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-31',
            'is_current' => false,
        ]);

        Livewire::test(AcademicStructureManager::class)
            ->call('setActiveTab', 'terms')
            ->call('setTermAsCurrent', $term2->id);

        $this->assertFalse($term1->fresh()->is_current);
        $this->assertTrue($term2->fresh()->is_current);
    }

    public function test_admin_can_create_and_manage_batches_and_groups(): void
    {
        $this->actingAs($this->admin);

        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => 'Year 2026',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
        ]);

        // Create Institute Batch
        Livewire::test(AcademicStructureManager::class)
            ->call('setActiveTab', 'batches')
            ->call('openCreateBatchModal')
            ->set('batchName', '2026 A/L Physical Science')
            ->set('batchCode', 'AL-2026-PHY')
            ->set('batchKind', 'institute_batch')
            ->set('batchAcademicYearId', $year->id)
            ->set('batchStatus', 'active')
            ->call('saveBatch')
            ->assertHasNoErrors();

        $batch = Batch::where('code', 'AL-2026-PHY')->first();
        $this->assertNotNull($batch);
        $this->assertSame('institute_batch', $batch->kind);
        $this->assertSame('active', $batch->status);

        // Toggle status to archived
        Livewire::test(AcademicStructureManager::class)
            ->call('setActiveTab', 'batches')
            ->call('toggleBatchStatus', $batch->id);

        $this->assertSame('archived', $batch->fresh()->status);
    }

    public function test_admin_cannot_delete_academic_year_with_linked_classes(): void
    {
        $this->actingAs($this->admin);

        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => 'Year 2026',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
        ]);

        SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'title' => 'Calculus I',
            'kind' => 'institute_class',
            'fee_amount' => 10000,
            'capacity' => 30,
            'status' => 'active',
        ]);

        Livewire::test(AcademicStructureManager::class)
            ->call('deleteYear', $year->id)
            ->assertSee('Cannot delete academic year with 1 linked classes');

        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
    }

    public function test_multi_institution_isolation(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Apex Academy',
            'slug' => 'apex-academy',
            'code' => 'APEX',
            'type' => 'institute',
            'is_active' => true,
        ]);

        $otherYear = AcademicYear::create([
            'institution_id' => $otherInstitution->id,
            'name' => 'Apex Year 2026',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(AcademicStructureManager::class)
            ->assertDontSee('Apex Year 2026');
    }
}
