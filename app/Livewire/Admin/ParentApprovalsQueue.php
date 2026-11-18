<?php

namespace App\Livewire\Admin;

use App\Contracts\SmsChannelInterface;
use App\Enums\StudentKind;
use App\Enums\StudentStatus;
use App\Models\Guardian;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Notifications\ParentApprovalNotification;
use App\Notifications\ParentRejectionNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ParentApprovalsQueue extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'pending';

    public bool $showRejectModal = false;

    public ?int $selectedProfileId = null;

    public string $rejectionReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function approve(int $profileId): void
    {
        $institutionId = current_institution_id() ?? 1;

        $profile = ParentProfile::where('institution_id', $institutionId)
            ->where('id', $profileId)
            ->with(['user', 'children'])
            ->firstOrFail();

        $user = $profile->user;
        if (! $user) {
            session()->flash('error', __('Associated parent user account not found.'));

            return;
        }

        $user->update([
            'status' => 'active',
        ]);

        $profile->update([
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        // Create or link Guardian and Student records (§6.5, T-1.6)
        $guardian = Guardian::firstOrCreate(
            [
                'institution_id' => $institutionId,
                'user_id' => $user->id,
            ],
            [
                'relationship' => $profile->relationship,
            ]
        );

        foreach ($profile->children as $childReg) {
            $student = Student::create([
                'institution_id' => $institutionId,
                'student_code' => Student::generateStudentCode($institutionId, 'NUR'),
                'first_name' => $childReg->first_name,
                'last_name' => $childReg->last_name,
                'dob' => $childReg->dob,
                'gender' => $childReg->gender,
                'status' => StudentStatus::Active,
                'kind' => StudentKind::Nursery,
                'registered_by' => auth()->id(),
                'registered_at' => now(),
            ]);

            $guardian->students()->syncWithoutDetaching([
                $student->id => [
                    'institution_id' => $institutionId,
                    'relationship' => $profile->relationship,
                    'is_primary' => true,
                ],
            ]);
        }

        // Send notifications (§5.2, §12)
        $user->notify(new ParentApprovalNotification($profile));

        if (! empty($user->phone)) {
            $sms = app(SmsChannelInterface::class);
            $inst = current_institution();
            $instName = $inst !== null ? $inst->name : 'EduPortal';
            $firstChild = $profile->children->first();
            $childName = $firstChild !== null ? $firstChild->full_name : __('your child');
            $sms->send($user->phone, __(':inst: Congratulations! Your parent account and application for :child have been approved. You can now sign in to the portal.', [
                'inst' => $instName,
                'child' => $childName,
            ]));
        }

        // Audit Log (§14)
        audit(
            'parent.approved',
            "Approved parent {$user->name} ({$user->email}) and child enrollment",
            $user,
            [
                'parent_profile_id' => $profile->id,
                'reviewed_by' => auth()->id(),
                'children_count' => $profile->children->count(),
            ]
        );

        session()->flash('success', __('Parent :name has been approved and notified successfully.', ['name' => $user->name]));
    }

    public function openRejectModal(int $profileId): void
    {
        $this->selectedProfileId = $profileId;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->selectedProfileId = null;
        $this->rejectionReason = '';
        $this->showRejectModal = false;
        $this->resetValidation();
    }

    public function reject(): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        if (! $this->selectedProfileId) {
            return;
        }

        $institutionId = current_institution_id() ?? 1;

        $profile = ParentProfile::where('institution_id', $institutionId)
            ->where('id', $this->selectedProfileId)
            ->with(['user', 'children'])
            ->firstOrFail();

        $user = $profile->user;
        if (! $user) {
            session()->flash('error', __('Associated parent user account not found.'));
            $this->closeRejectModal();

            return;
        }

        $reason = trim($this->rejectionReason);

        $user->update([
            'status' => 'rejected',
        ]);

        $profile->update([
            'rejection_reason' => $reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Send notification with reason (§5.2)
        $user->notify(new ParentRejectionNotification($reason, $profile));

        if (! empty($user->phone)) {
            $sms = app(SmsChannelInterface::class);
            $inst = current_institution();
            $instName = $inst !== null ? $inst->name : 'EduPortal';
            $sms->send($user->phone, __(':inst: Update regarding your registration: Your application was not approved. Reason: :reason', [
                'inst' => $instName,
                'reason' => $reason,
            ]));
        }

        // Audit Log (§14)
        audit(
            'parent.rejected',
            "Rejected parent {$user->name} ({$user->email}). Reason: {$reason}",
            $user,
            [
                'parent_profile_id' => $profile->id,
                'reason' => $reason,
                'reviewed_by' => auth()->id(),
            ]
        );

        $parentName = $user->name;
        $this->closeRejectModal();

        session()->flash('success', __('Application for :name has been rejected with the recorded reason and notified.', ['name' => $parentName]));
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;

        $counts = [
            'pending' => ParentProfile::where('institution_id', $institutionId)
                ->whereHas('user', fn (Builder $q) => $q->where('status', 'pending'))
                ->count(),
            'active' => ParentProfile::where('institution_id', $institutionId)
                ->whereHas('user', fn (Builder $q) => $q->where('status', 'active'))
                ->count(),
            'rejected' => ParentProfile::where('institution_id', $institutionId)
                ->whereHas('user', fn (Builder $q) => $q->where('status', 'rejected'))
                ->count(),
            'all' => ParentProfile::where('institution_id', $institutionId)->count(),
        ];

        $query = ParentProfile::where('institution_id', $institutionId)
            ->with(['user', 'children', 'reviewer']);

        if ($this->statusFilter !== 'all') {
            $query->whereHas('user', fn (Builder $q) => $q->where('status', $this->statusFilter));
        }

        if (! empty(trim($this->search))) {
            $searchTerm = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->whereHas('user', function (Builder $userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm)
                        ->orWhere('phone', 'like', $searchTerm);
                })->orWhereHas('children', function (Builder $childQuery) use ($searchTerm) {
                    $childQuery->where('first_name', 'like', $searchTerm)
                        ->orWhere('last_name', 'like', $searchTerm)
                        ->orWhere('nursery_group', 'like', $searchTerm);
                });
            });
        }

        $profiles = $query->latest()->paginate(10);

        return view('livewire.admin.parent-approvals-queue', [
            'profiles' => $profiles,
            'counts' => $counts,
        ]);
    }
}
