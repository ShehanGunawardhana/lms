<?php

namespace App\Livewire\Admin;

use App\Enums\PortfolioStatus;
use App\Models\TeacherProfile;
use App\Notifications\PortfolioPublishedNotification;
use App\Notifications\PortfolioRevisionRequestedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PortfolioReviewQueue extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $statusFilter = 'pending';

    public int $perPage = 10;

    // Detail Inspection Modal
    public bool $showDetailModal = false;

    public ?int $selectedProfileId = null;

    // Revision Request Modal
    public bool $showRejectModal = false;

    public ?int $rejectingProfileId = null;

    public string $rejectionReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openDetailModal(int $profileId): void
    {
        $this->selectedProfileId = $profileId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedProfileId = null;
    }

    public function openRejectModal(int $profileId): void
    {
        $this->rejectingProfileId = $profileId;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectingProfileId = null;
        $this->rejectionReason = '';
    }

    public function approveAndPublish(int $profileId): void
    {
        $institutionId = current_institution_id() ?? 1;

        $profile = TeacherProfile::where('institution_id', $institutionId)
            ->where('id', $profileId)
            ->with(['user', 'photo'])
            ->firstOrFail();

        $this->authorize('publish', $profile);

        $profile->update([
            'portfolio_status' => PortfolioStatus::Published->value,
            'published_at' => now(),
        ]);

        audit(
            'portfolio.published',
            "Lecturer portfolio for {$profile->user->name} was approved and published by administrator",
            $profile,
            ['status' => PortfolioStatus::Published->value]
        );

        $profile->user->notify(new PortfolioPublishedNotification($profile));

        session()->flash('success', __("Portfolio for ':name' has been approved and published to the public directory.", ['name' => $profile->user->name]));

        if ($this->showDetailModal) {
            $this->closeDetailModal();
        }
    }

    public function confirmReject(): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        if ($this->rejectingProfileId === null) {
            return;
        }

        $institutionId = current_institution_id() ?? 1;

        $profile = TeacherProfile::where('institution_id', $institutionId)
            ->where('id', $this->rejectingProfileId)
            ->with('user')
            ->firstOrFail();

        $this->authorize('publish', $profile);

        $profile->update([
            'portfolio_status' => PortfolioStatus::Rejected->value,
        ]);

        audit(
            'portfolio.rejected',
            "Lecturer portfolio for {$profile->user->name} returned for revision: {$this->rejectionReason}",
            $profile,
            ['reason' => $this->rejectionReason]
        );

        $profile->user->notify(new PortfolioRevisionRequestedNotification($profile, $this->rejectionReason));

        $name = $profile->user->name;
        $this->closeRejectModal();

        session()->flash('success', __("Revision request sent to ':name'.", ['name' => $name]));

        if ($this->showDetailModal) {
            $this->closeDetailModal();
        }
    }

    public function unpublish(int $profileId): void
    {
        $institutionId = current_institution_id() ?? 1;

        $profile = TeacherProfile::where('institution_id', $institutionId)
            ->where('id', $profileId)
            ->with('user')
            ->firstOrFail();

        $this->authorize('publish', $profile);

        $profile->update([
            'portfolio_status' => PortfolioStatus::Draft->value,
            'published_at' => null,
        ]);

        audit(
            'portfolio.unpublished',
            "Lecturer portfolio for {$profile->user->name} was unpublished and set to draft",
            $profile,
            ['status' => PortfolioStatus::Draft->value]
        );

        session()->flash('success', __("Portfolio for ':name' has been unpublished.", ['name' => $profile->user->name]));

        if ($this->showDetailModal) {
            $this->closeDetailModal();
        }
    }

    public function render(): View
    {
        $this->authorize('viewAny', TeacherProfile::class);

        $institutionId = current_institution_id() ?? 1;

        $metrics = [
            'pending' => TeacherProfile::where('institution_id', $institutionId)->where('portfolio_status', PortfolioStatus::Pending->value)->count(),
            'published' => TeacherProfile::where('institution_id', $institutionId)->where('portfolio_status', PortfolioStatus::Published->value)->count(),
            'rejected' => TeacherProfile::where('institution_id', $institutionId)->where('portfolio_status', PortfolioStatus::Rejected->value)->count(),
            'draft' => TeacherProfile::where('institution_id', $institutionId)->where('portfolio_status', PortfolioStatus::Draft->value)->count(),
        ];

        $query = TeacherProfile::query()
            ->where('institution_id', $institutionId)
            ->with(['user', 'photo']);

        if ($this->statusFilter !== 'all') {
            $query->where('portfolio_status', $this->statusFilter);
        }

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('headline', 'like', $term)
                    ->orWhere('bio', 'like', $term)
                    ->orWhere('qualifications', 'like', $term)
                    ->orWhere('subjects', 'like', $term)
                    ->orWhereHas('user', function (Builder $userQ) use ($term) {
                        $userQ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        $portfolios = $query->latest('updated_at')->paginate($this->perPage);

        $selectedProfile = null;
        if ($this->selectedProfileId !== null) {
            $selectedProfile = TeacherProfile::with(['user', 'photo'])->find($this->selectedProfileId);
        }

        return view('livewire.admin.portfolio-review-queue', [
            'portfolios' => $portfolios,
            'metrics' => $metrics,
            'selectedProfile' => $selectedProfile,
        ]);
    }
}
