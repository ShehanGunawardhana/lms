<?php

namespace App\Livewire\Admin;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UserManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $status = 'all';

    public string $role = 'all';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 15;

    // Suspend Modal
    public bool $showSuspendModal = false;

    public ?int $suspendingUserId = null;

    public string $suspensionReason = '';

    // Deactivate Modal
    public bool $showDeactivateModal = false;

    public ?int $deactivatingUserId = null;

    public string $deactivationReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openSuspendModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('suspend', $user);

        $this->suspendingUserId = $userId;
        $this->suspensionReason = '';
        $this->showSuspendModal = true;
    }

    public function closeSuspendModal(): void
    {
        $this->showSuspendModal = false;
        $this->suspendingUserId = null;
        $this->suspensionReason = '';
    }

    public function suspendUser(): void
    {
        $this->validate([
            'suspensionReason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($this->suspendingUserId === null) {
            return;
        }

        $user = User::findOrFail($this->suspendingUserId);
        $this->authorize('suspend', $user);

        $user->update(['status' => UserStatus::Suspended->value]);

        audit(
            'user.suspended',
            "User {$user->name} was suspended by administrator: {$this->suspensionReason}",
            $user,
            ['reason' => $this->suspensionReason]
        );

        $this->closeSuspendModal();
        session()->flash('success', __("User ':name' has been suspended.", ['name' => $user->name]));
    }

    public function reactivateUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('reactivate', $user);

        $user->update(['status' => UserStatus::Active->value]);

        audit(
            'user.reactivated',
            "User {$user->name} was reactivated by administrator",
            $user,
            []
        );

        session()->flash('success', __("User ':name' has been reactivated successfully.", ['name' => $user->name]));
    }

    public function openDeactivateModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('deactivate', $user);

        $this->deactivatingUserId = $userId;
        $this->deactivationReason = '';
        $this->showDeactivateModal = true;
    }

    public function closeDeactivateModal(): void
    {
        $this->showDeactivateModal = false;
        $this->deactivatingUserId = null;
        $this->deactivationReason = '';
    }

    public function deactivateUser(): void
    {
        if ($this->deactivatingUserId === null) {
            return;
        }

        $user = User::findOrFail($this->deactivatingUserId);
        $this->authorize('deactivate', $user);

        $user->update(['status' => UserStatus::Deactivated->value]);

        audit(
            'user.deactivated',
            "User {$user->name} account was deactivated by administrator",
            $user,
            ['reason' => $this->deactivationReason]
        );

        $this->closeDeactivateModal();
        session()->flash('success', __("User ':name' has been deactivated.", ['name' => $user->name]));
    }

    public function render(): View
    {
        $this->authorize('viewAny', User::class);

        $institutionId = current_institution_id() ?? 1;

        $metrics = [
            'total' => User::where('institution_id', $institutionId)->count(),
            'active' => User::where('institution_id', $institutionId)->where('status', UserStatus::Active->value)->count(),
            'pending' => User::where('institution_id', $institutionId)->where('status', UserStatus::Pending->value)->count(),
            'suspended' => User::where('institution_id', $institutionId)->where('status', UserStatus::Suspended->value)->count(),
            'deactivated' => User::where('institution_id', $institutionId)->where('status', UserStatus::Deactivated->value)->count(),
        ];

        $query = User::query()
            ->where('institution_id', $institutionId)
            ->with('roles');

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->role !== 'all') {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->role);
            });
        }

        $allowedSortFields = ['name', 'email', 'status', 'created_at', 'last_login_at'];
        $field = in_array($this->sortField, $allowedSortFields, true) ? $this->sortField : 'created_at';
        $direction = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($field, $direction);

        $users = $query->paginate($this->perPage);

        return view('livewire.admin.user-manager', [
            'users' => $users,
            'metrics' => $metrics,
            'rolesList' => RoleName::cases(),
        ]);
    }
}
