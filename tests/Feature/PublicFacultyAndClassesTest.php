<?php

namespace Tests\Feature;

use App\Enums\ClassKind;
use App\Enums\PortfolioStatus;
use App\Enums\RoleName;
use App\Livewire\Public\ClassCatalog;
use App\Livewire\Public\LecturerDirectory;
use App\Livewire\Public\LecturerPortfolioPage;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PublicFacultyAndClassesTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $teacher;

    protected TeacherProfile $publishedProfile;

    protected SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $this->institution = Institution::first();
        app(InstitutionContext::class)->set($this->institution);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'name' => 'Prof. Katherine Johnson',
            'email' => 'katherine@nasa.test',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->publishedProfile = TeacherProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->teacher->id,
            'headline' => 'Senior Professor of Orbital Mechanics & Applied Calculus',
            'bio' => 'Mathematician whose calculations of orbital mechanics were critical to the success of the first U.S. crewed spaceflights.',
            'qualifications' => 'B.Sc. in Mathematics and French (summa cum laude), Presidential Medal of Freedom.',
            'subjects' => ['Calculus', 'Astrodynamics', 'Geometry'],
            'sample_materials' => [
                [
                    'title' => 'Orbital Trajectory Cheat Sheet',
                    'type' => 'document',
                    'url' => 'https://example.com/orbit.pdf',
                    'description' => 'Summary of ellipse equations and Keplerian laws.',
                ],
            ],
            'portfolio_status' => PortfolioStatus::Published->value,
            'published_at' => now(),
        ]);

        $this->class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Orbital Mechanics & Calculus 101',
            'subject' => 'Calculus',
            'kind' => ClassKind::InstituteClass->value,
            'fee_amount' => 15000.00,
            'capacity' => 60,
            'room' => 'Hall 401',
            'description' => 'Foundational undergraduate calculus tailored for astronautics and engineering.',
            'status' => 'active',
        ]);
    }

    public function test_guest_can_render_lecturers_directory(): void
    {
        $response = $this->get(route('public.lecturers'));

        $response->assertOk();
        $response->assertSee('Lecturers &amp; Teaching Faculty', false);
        $response->assertSee('Prof. Katherine Johnson');
        $response->assertSee('Senior Professor of Orbital Mechanics');
    }

    public function test_draft_or_pending_portfolios_are_hidden_from_public_directory(): void
    {
        $draftTeacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'name' => 'Draft Lecturer Person',
        ]);
        $draftTeacher->assignRole(RoleName::Teacher->value);

        TeacherProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $draftTeacher->id,
            'headline' => 'Draft Teacher Headline',
            'portfolio_status' => PortfolioStatus::Draft->value,
        ]);

        $response = $this->get(route('public.lecturers'));

        $response->assertOk();
        $response->assertDontSee('Draft Lecturer Person');
    }

    public function test_guest_can_search_and_filter_lecturer_directory(): void
    {
        Livewire::test(LecturerDirectory::class)
            ->set('search', 'Calculus')
            ->assertSee('Prof. Katherine Johnson')
            ->set('search', 'UnrelatedKeywordXYZ')
            ->assertDontSee('Prof. Katherine Johnson')
            ->set('search', '')
            ->set('selectedSubject', 'Calculus')
            ->assertSee('Prof. Katherine Johnson')
            ->set('selectedSubject', 'NonExistentSubject')
            ->assertDontSee('Prof. Katherine Johnson');
    }

    public function test_guest_can_view_published_lecturer_portfolio_page(): void
    {
        $response = $this->get(route('public.lecturers.show', $this->publishedProfile->id));

        $response->assertOk();
        $response->assertSee('Prof. Katherine Johnson');
        $response->assertSee('Senior Professor of Orbital Mechanics');
        $response->assertSee('Orbital Trajectory Cheat Sheet');
        $response->assertSee('Orbital Mechanics &amp; Calculus 101', false);
        $response->assertSee('LKR 15,000.00');
        $response->assertSee('Apply for this Class');
    }

    public function test_draft_portfolio_returns_404_for_guest_but_visible_to_owner(): void
    {
        $draftTeacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'name' => 'Draft Teacher Specimen',
        ]);
        $draftTeacher->assignRole(RoleName::Teacher->value);

        $draftProfile = TeacherProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $draftTeacher->id,
            'headline' => 'Private Draft',
            'portfolio_status' => PortfolioStatus::Draft->value,
        ]);

        // Guest gets 404
        $this->get(route('public.lecturers.show', $draftProfile->id))->assertNotFound();

        // Owner teacher can view preview
        $this->actingAs($draftTeacher)
            ->get(route('public.lecturers.show', $draftProfile->id))
            ->assertOk()
            ->assertSee('Private Preview Mode');
    }

    public function test_applicant_can_submit_class_application_from_portfolio_page(): void
    {
        Livewire::test(LecturerPortfolioPage::class, ['id' => $this->publishedProfile->id])
            ->call('openApplyModal', $this->class->id)
            ->assertSet('showApplyModal', true)
            ->set('applicantName', 'Ada Lovelace')
            ->set('applicantEmail', 'ada@computing.test')
            ->set('applicantPhone', '+94779998877')
            ->set('applicationNotes', 'Keen to pursue astrophysics coursework.')
            ->call('submitApplication')
            ->assertSet('showApplyModal', false)
            ->assertSee('Your application for');

        // Verify audit log
        $activity = Activity::where('event', 'class.application_submitted')
            ->where('subject_id', $this->class->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Ada Lovelace', $activity->description);
    }

    public function test_guest_can_render_public_classes_catalog_and_apply(): void
    {
        $response = $this->get(route('public.classes'));

        $response->assertOk();
        $response->assertSee('Academic Classes &amp; Programs', false);
        $response->assertSee('Orbital Mechanics &amp; Calculus 101', false);
        $response->assertSee('LKR 15,000.00');
        $response->assertSee('Prof. Katherine Johnson');

        Livewire::test(ClassCatalog::class)
            ->set('search', 'Astrophysics')
            ->assertDontSee('Orbital Mechanics')
            ->set('search', 'Calculus')
            ->assertSee('Orbital Mechanics')
            ->call('openApplyModal', $this->class->id)
            ->assertSet('showApplyModal', true)
            ->set('applicantName', 'Grace Hopper')
            ->set('applicantEmail', 'grace@navy.test')
            ->set('applicantPhone', '+94775554433')
            ->call('submitApplication')
            ->assertSet('showApplyModal', false);
    }

    public function test_multi_institution_isolation_in_directory(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Foreign Institute',
            'slug' => 'foreign-inst',
            'timezone' => 'Asia/Colombo',
            'locale' => 'en',
            'type' => 'institute',
        ]);

        $foreignTeacher = User::factory()->create([
            'institution_id' => $otherInstitution->id,
            'status' => 'active',
            'name' => 'Dr. External Alien',
        ]);
        $foreignTeacher->assignRole(RoleName::Teacher->value);

        TeacherProfile::create([
            'institution_id' => $otherInstitution->id,
            'user_id' => $foreignTeacher->id,
            'headline' => 'Foreign Astronomy',
            'portfolio_status' => PortfolioStatus::Published->value,
            'published_at' => now(),
        ]);

        $this->get(route('public.lecturers'))
            ->assertOk()
            ->assertDontSee('Dr. External Alien');
    }
}
