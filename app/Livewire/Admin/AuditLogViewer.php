<?php

namespace App\Livewire\Admin;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $search = '';

    public string $eventFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEventFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $institutionId = current_institution_id();

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();

        if ($institutionId !== null) {
            $query->where(function ($q) use ($institutionId) {
                $q->whereJsonContains('properties->institution_id', $institutionId)
                    ->orWhereNull('properties->institution_id');
            });
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhere('event', 'like', "%{$this->search}%");
            });
        }

        if ($this->eventFilter !== '') {
            $query->where('event', $this->eventFilter);
        }

        $logs = $query->paginate(15);
        $events = Activity::query()->distinct()->pluck('event')->filter()->values()->all();

        return view('livewire.admin.audit-log-viewer', [
            'logs' => $logs,
            'events' => $events,
        ])->layout('layouts.app', [
            'title' => __('Audit Log'),
            'header' => __('System Audit Log'),
        ]);
    }
}
