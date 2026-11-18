<?php

namespace Tests\Feature;

use App\Enums\PortfolioStatus;
use App\Enums\RoleName;
use App\Livewire\Teacher\PortfolioEditor;
use App\Models\Institution;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class LecturerPortfolioEditorTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $teacher;

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
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);
    }

    public function test_guest_or_non_teacher_cannot_access_portfolio_editor(): void
    {
        $this->get(route('teacher.portfolio'))->assertRedirect(route('login'));

        $student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $student->assignRole(RoleName::Student->value);

        $this->actingAs($student)->get(route('teacher.portfolio'))->assertForbidden();
    }

    public function test_teacher_can_render_portfolio_editor(): void
    {
        $this->actingAs($this->teacher)->get(route('teacher.portfolio'))
            ->assertOk()
            ->assertSee('Lecturer Portfolio Editor')
            ->assertSee('Draft Mode');
    }

    public function test_teacher_can_save_portfolio_draft_and_audit(): void
    {
        $this->actingAs($this->teacher);

        Livewire::test(PortfolioEditor::class)
            ->set('headline', 'Senior Lecturer in Advanced Mathematics')
            ->set('bio', 'Passionate about differential equations, linear algebra, and pure geometry.')
            ->set('qualifications', 'Ph.D. in Applied Mathematics (Cambridge), B.Sc. First Class.')
            ->set('subjectsInput', 'Calculus, Linear Algebra, Statistics')
            ->call('addSampleMaterial')
            ->set('sampleMaterials.0.title', 'Lecture 1: Limits & Continuity Notes')
            ->set('sampleMaterials.0.type', 'document')
            ->set('sampleMaterials.0.url', 'https://example.com/notes1.pdf')
            ->set('sampleMaterials.0.description', 'Introductory formula cheat sheet and theorem proofs.')
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertSee('Portfolio draft saved successfully');

        $profile = TeacherProfile::where('user_id', $this->teacher->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('Senior Lecturer in Advanced Mathematics', $profile->headline);
        $this->assertEquals(PortfolioStatus::Draft->value, $profile->portfolio_status);
        $this->assertCount(3, $profile->subjects);
        $this->assertCount(1, $profile->sample_materials);

        // Audit log verified
        $activity = Activity::where('event', 'portfolio.saved')
            ->where('subject_id', $profile->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->teacher->id, $activity->causer_id);
    }

    public function test_teacher_can_upload_photo(): void
    {
        Storage::fake('private');
        $this->actingAs($this->teacher);

        $image = UploadedFile::fake()->image('profile.jpg', 400, 400);

        Livewire::test(PortfolioEditor::class)
            ->set('photo', $image)
            ->call('saveDraft')
            ->assertHasNoErrors();

        $profile = TeacherProfile::where('user_id', $this->teacher->id)->first();
        $this->assertNotNull($profile->photo_file_id);
    }

    public function test_submit_for_approval_validates_required_fields(): void
    {
        $this->actingAs($this->teacher);

        Livewire::test(PortfolioEditor::class)
            ->set('headline', '')
            ->set('bio', 'short')
            ->set('qualifications', '')
            ->set('subjectsInput', '')
            ->call('submitForApproval')
            ->assertHasErrors(['headline', 'bio', 'qualifications', 'subjectsInput']);
    }

    public function test_teacher_can_submit_for_approval_and_transition_to_pending(): void
    {
        $this->actingAs($this->teacher);

        Livewire::test(PortfolioEditor::class)
            ->set('headline', 'Senior Lecturer in Computer Science')
            ->set('bio', 'Dedicated computer scientist and algorithms researcher with over a decade of teaching experience.')
            ->set('qualifications', 'Ph.D. in Computer Science, M.Sc. Data Science')
            ->set('subjectsInput', 'Algorithms, Data Structures, Operating Systems')
            ->call('submitForApproval')
            ->assertHasNoErrors()
            ->assertSet('portfolioStatus', PortfolioStatus::Pending->value)
            ->assertSee('Under Administrative Review');

        $profile = TeacherProfile::where('user_id', $this->teacher->id)->first();
        $this->assertEquals(PortfolioStatus::Pending->value, $profile->portfolio_status);

        // Verify audit log
        $activity = Activity::where('event', 'portfolio.submitted')
            ->where('subject_id', $profile->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->teacher->id, $activity->causer_id);
    }

    public function test_teacher_can_recall_portfolio_back_to_draft(): void
    {
        $profile = TeacherProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->teacher->id,
            'headline' => 'Lecturer in Physics',
            'bio' => 'Classical mechanics and quantum optics researcher.',
            'qualifications' => 'B.Sc. Physics (Hons)',
            'subjects' => ['Physics'],
            'portfolio_status' => PortfolioStatus::Pending->value,
        ]);

        $this->actingAs($this->teacher);

        Livewire::test(PortfolioEditor::class)
            ->assertSet('portfolioStatus', PortfolioStatus::Pending->value)
            ->call('recallToDraft')
            ->assertSet('portfolioStatus', PortfolioStatus::Draft->value);

        $profile->refresh();
        $this->assertEquals(PortfolioStatus::Draft->value, $profile->portfolio_status);

        // Verify audit log
        $activity = Activity::where('event', 'portfolio.recalled')
            ->where('subject_id', $profile->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->teacher->id, $activity->causer_id);
    }
}
