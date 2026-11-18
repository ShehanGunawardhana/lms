<?php

namespace Tests\Feature;

use App\Enums\PortfolioStatus;
use App\Enums\RoleName;
use App\Livewire\Admin\PortfolioReviewQueue;
use App\Models\Institution;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Notifications\PortfolioPublishedNotification;
use App\Notifications\PortfolioRevisionRequestedNotification;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AdminPortfolioReviewTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected User $teacher;

    protected TeacherProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $this->institution = Institution::first();
        app(InstitutionContext::class)->set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1'])),
            'two_factor_confirmed_at' => now(),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'name' => 'Dr. Gregory House',
            'email' => 'house@princeton.test',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->profile = TeacherProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->teacher->id,
            'headline' => 'Chair of Diagnostic Medicine',
            'bio' => 'World renowned diagnostician and lecturer with double specialty in infectious diseases and nephrology.',
            'qualifications' => 'M.D. (Johns Hopkins), Board Certified.',
            'subjects' => ['Diagnostics', 'Pathology'],
            'sample_materials' => [
                [
                    'title' => 'Clinical Case Study: Autoimmune Diseases',
                    'type' => 'document',
                    'url' => 'https://example.com/case1.pdf',
                    'description' => 'Comprehensive differential diagnosis breakdown.',
                ],
            ],
            'portfolio_status' => PortfolioStatus::Pending->value,
        ]);
    }

    public function test_guest_or_non_admin_cannot_access_portfolio_queue(): void
    {
        $this->get(route('admin.portfolios'))->assertRedirect(route('login'));

        $this->actingAs($this->teacher)->get(route('admin.portfolios'))->assertForbidden();
    }

    public function test_admin_without_two_factor_is_redirected(): void
    {
        $insecureAdmin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $insecureAdmin->assignRole(RoleName::Admin->value);

        $this->actingAs($insecureAdmin)->get(route('admin.portfolios'))
            ->assertRedirect(route('two-factor.setup'));
    }

    public function test_admin_can_render_portfolio_queue_and_see_pending_profile(): void
    {
        $this->actingAs($this->admin)->get(route('admin.portfolios'))
            ->assertOk()
            ->assertSee('Lecturer Portfolios Review Queue')
            ->assertSee('Dr. Gregory House')
            ->assertSee('Chair of Diagnostic Medicine');
    }

    public function test_admin_can_filter_and_search_portfolios(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PortfolioReviewQueue::class)
            ->set('search', 'House')
            ->assertSee('Dr. Gregory House')
            ->set('search', 'NonExistentTeacher')
            ->assertDontSee('Dr. Gregory House')
            ->set('search', '')
            ->set('statusFilter', 'published')
            ->assertDontSee('Dr. Gregory House')
            ->set('statusFilter', 'pending')
            ->assertSee('Dr. Gregory House');
    }

    public function test_admin_can_inspect_portfolio_details(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PortfolioReviewQueue::class)
            ->call('openDetailModal', $this->profile->id)
            ->assertSet('showDetailModal', true)
            ->assertSee('Clinical Case Study: Autoimmune Diseases')
            ->assertSee('World renowned diagnostician');
    }

    public function test_admin_can_approve_and_publish_portfolio_with_notification_and_audit(): void
    {
        Notification::fake();
        $this->actingAs($this->admin);

        Livewire::test(PortfolioReviewQueue::class)
            ->call('approveAndPublish', $this->profile->id)
            ->assertSee('approved and published');

        $this->profile->refresh();
        $this->assertEquals(PortfolioStatus::Published->value, $this->profile->portfolio_status);
        $this->assertNotNull($this->profile->published_at);

        // Audit log verified
        $activity = Activity::where('event', 'portfolio.published')
            ->where('subject_id', $this->profile->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->admin->id, $activity->causer_id);

        // Notification verified
        Notification::assertSentTo($this->teacher, PortfolioPublishedNotification::class);
    }

    public function test_admin_can_request_revision_with_mandatory_feedback(): void
    {
        Notification::fake();
        $this->actingAs($this->admin);

        Livewire::test(PortfolioReviewQueue::class)
            ->call('openRejectModal', $this->profile->id)
            ->assertSet('showRejectModal', true)
            ->set('rejectionReason', '')
            ->call('confirmReject')
            ->assertHasErrors(['rejectionReason'])
            ->set('rejectionReason', 'Please upload a formal institutional portrait photo.')
            ->call('confirmReject')
            ->assertSet('showRejectModal', false);

        $this->profile->refresh();
        $this->assertEquals(PortfolioStatus::Rejected->value, $this->profile->portfolio_status);

        // Audit log verified
        $activity = Activity::where('event', 'portfolio.rejected')
            ->where('subject_id', $this->profile->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->admin->id, $activity->causer_id);
        $this->assertStringContainsString('formal institutional portrait', $activity->description);

        // Notification verified
        Notification::assertSentTo($this->teacher, PortfolioRevisionRequestedNotification::class);
    }

    public function test_admin_can_unpublish_portfolio(): void
    {
        $this->profile->update([
            'portfolio_status' => PortfolioStatus::Published->value,
            'published_at' => now(),
        ]);

        $this->actingAs($this->admin);

        Livewire::test(PortfolioReviewQueue::class)
            ->call('unpublish', $this->profile->id)
            ->assertSee('has been unpublished');

        $this->profile->refresh();
        $this->assertEquals(PortfolioStatus::Draft->value, $this->profile->portfolio_status);
        $this->assertNull($this->profile->published_at);

        // Audit log verified
        $activity = Activity::where('event', 'portfolio.unpublished')
            ->where('subject_id', $this->profile->id)
            ->first();

        $this->assertNotNull($activity);
    }

    public function test_multi_institution_isolation_prevents_reviewing_other_institution(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Foreign University',
            'slug' => 'foreign-university',
            'timezone' => 'Asia/Colombo',
            'locale' => 'en',
            'type' => 'university',
        ]);

        $otherTeacher = User::factory()->create([
            'institution_id' => $otherInstitution->id,
            'status' => 'active',
            'name' => 'Dr. Foreign Lecturer',
        ]);
        $otherTeacher->assignRole(RoleName::Teacher->value);

        $otherProfile = TeacherProfile::create([
            'institution_id' => $otherInstitution->id,
            'user_id' => $otherTeacher->id,
            'headline' => 'Foreign Professor',
            'portfolio_status' => PortfolioStatus::Pending->value,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(PortfolioReviewQueue::class)
            ->assertDontSee('Dr. Foreign Lecturer');
    }
}
