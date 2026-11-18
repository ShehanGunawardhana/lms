<?php

namespace Tests\Feature;

use App\Contracts\SmsChannelInterface;
use App\Enums\RoleName;
use App\Livewire\Admin\ParentApprovalsQueue;
use App\Models\ChildRegistration;
use App\Models\Institution;
use App\Models\ParentProfile;
use App\Models\User;
use App\Notifications\ParentApprovalNotification;
use App\Notifications\ParentRejectionNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ParentApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected User $teacher;

    protected User $parent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'Royal Palms Nursery & Institute',
            'slug' => 'royal-palms',
            'code' => 'RPNI',
            'type' => 'nursery',
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

        $this->parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'pending',
            'phone' => '+94771239999',
        ]);
        $this->parent->assignRole(RoleName::Parent->value);
    }

    public function test_guest_or_non_admin_cannot_access_parent_approvals_queue(): void
    {
        $this->get('/admin/approvals/parents')->assertRedirect('/login');

        $this->actingAs($this->teacher)
            ->get('/admin/approvals/parents')
            ->assertStatus(403);

        $this->actingAs($this->parent)
            ->get('/admin/approvals/parents')
            ->assertStatus(403);
    }

    public function test_admin_with_two_factor_can_render_approvals_queue(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/approvals/parents')
            ->assertOk()
            ->assertSee(__('Parent Approvals Queue'))
            ->assertSee(__('Pending Review'));
    }

    public function test_admin_can_approve_pending_parent(): void
    {
        Notification::fake();
        $this->actingAs($this->admin);

        $profile = ParentProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parent->id,
            'relationship' => 'mother',
            'consent_terms' => true,
            'consent_child_safety' => true,
            'consent_photo' => true,
            'consented_at' => now(),
        ]);

        $child = ChildRegistration::create([
            'institution_id' => $this->institution->id,
            'parent_profile_id' => $profile->id,
            'first_name' => 'Maya',
            'last_name' => 'Angelou',
            'dob' => '2023-01-15',
            'nursery_group' => 'playgroup',
        ]);

        $smsMock = $this->mock(SmsChannelInterface::class);
        $smsMock->shouldReceive('send')
            ->once()
            ->with('+94771239999', \Mockery::any())
            ->andReturn(true);

        Livewire::test(ParentApprovalsQueue::class)
            ->call('approve', $profile->id)
            ->assertSee(__('has been approved and notified successfully'));

        $this->parent->refresh();
        expect($this->parent->status)->toBe('active');
        expect($this->parent->isApproved())->toBeTrue();

        $profile->refresh();
        expect($profile->reviewed_by)->toBe($this->admin->id);
        expect($profile->reviewed_at)->not->toBeNull();
        expect($profile->rejection_reason)->toBeNull();

        Notification::assertSentTo($this->parent, ParentApprovalNotification::class);

        $activity = Activity::where('event', 'parent.approved')->first();
        expect($activity)->not->toBeNull();
        expect($activity->causer_id)->toBe($this->admin->id);
        expect($activity->subject_id)->toBe($this->parent->id);
    }

    public function test_admin_can_reject_parent_with_mandatory_reason(): void
    {
        Notification::fake();
        $this->actingAs($this->admin);

        $profile = ParentProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parent->id,
            'relationship' => 'father',
            'consent_terms' => true,
            'consent_child_safety' => true,
        ]);

        $child = ChildRegistration::create([
            'institution_id' => $this->institution->id,
            'parent_profile_id' => $profile->id,
            'first_name' => 'Lucas',
            'last_name' => 'Scott',
            'dob' => '2022-10-10',
            'nursery_group' => 'nursery',
        ]);

        // 1. Rejection without reason fails validation
        Livewire::test(ParentApprovalsQueue::class)
            ->call('openRejectModal', $profile->id)
            ->set('rejectionReason', '')
            ->call('reject')
            ->assertHasErrors(['rejectionReason' => 'required']);

        // 2. Rejection with valid reason succeeds
        $reason = 'Child immunization documentation is incomplete. Please submit updated card.';

        $smsMock = $this->mock(SmsChannelInterface::class);
        $smsMock->shouldReceive('send')
            ->once()
            ->with('+94771239999', \Mockery::on(fn ($msg) => str_contains((string) $msg, 'immunization')))
            ->andReturn(true);

        Livewire::test(ParentApprovalsQueue::class)
            ->call('openRejectModal', $profile->id)
            ->set('rejectionReason', $reason)
            ->call('reject')
            ->assertHasNoErrors()
            ->assertSee(__('has been rejected with the recorded reason and notified'));

        $this->parent->refresh();
        expect($this->parent->status)->toBe('rejected');
        expect($this->parent->isRejected())->toBeTrue();

        $profile->refresh();
        expect($profile->rejection_reason)->toBe($reason);
        expect($profile->reviewed_by)->toBe($this->admin->id);
        expect($profile->reviewed_at)->not->toBeNull();

        Notification::assertSentTo($this->parent, ParentRejectionNotification::class);

        $activity = Activity::where('event', 'parent.rejected')->first();
        expect($activity)->not->toBeNull();
        expect($activity->causer_id)->toBe($this->admin->id);
    }

    public function test_admin_can_filter_by_status_and_search(): void
    {
        $this->actingAs($this->admin);

        $activeParent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Aria Montgomery',
            'status' => 'active',
        ]);
        $activeParent->assignRole(RoleName::Parent->value);
        ParentProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $activeParent->id,
        ]);

        $pendingParent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Spencer Hastings',
            'status' => 'pending',
        ]);
        $pendingParent->assignRole(RoleName::Parent->value);
        ParentProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $pendingParent->id,
        ]);

        // Default filter is 'pending'
        Livewire::test(ParentApprovalsQueue::class)
            ->assertSee('Spencer Hastings')
            ->assertDontSee('Aria Montgomery')
            // Switch to 'active'
            ->set('statusFilter', 'active')
            ->assertSee('Aria Montgomery')
            ->assertDontSee('Spencer Hastings')
            // Switch to 'all' and search
            ->set('statusFilter', 'all')
            ->set('search', 'Spencer')
            ->assertSee('Spencer Hastings')
            ->assertDontSee('Aria Montgomery');
    }
}
