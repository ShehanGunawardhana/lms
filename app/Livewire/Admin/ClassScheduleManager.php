<?php

namespace App\Livewire\Admin;

use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\SchoolClass;
use App\Services\ScheduleGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ClassScheduleManager extends Component
{
    use AuthorizesRequests, WithPagination;

    public int $classId;

    // Slot Modal State
    public bool $showSlotModal = false;

    public ?int $editingSlotId = null;

    public int $slotWeekday = 1; // 1 = Monday

    public string $slotStartsAt = '08:30';

    public string $slotEndsAt = '11:30';

    public string $slotRoom = '';

    public string $slotOnlineUrl = '';

    public string $slotValidFrom = '';

    public string $slotValidTo = '';

    // Generator State
    public string $generatorStartsOn = '';

    public string $generatorEndsOn = '';

    // Cancel Session Modal
    public bool $showCancelModal = false;

    public ?int $cancellingSessionId = null;

    public string $cancelReason = '';

    // Session Status Filter
    public string $sessionStatusFilter = 'all';

    public function mount(int $classId): void
    {
        $this->classId = $classId;
        $institutionId = current_institution_id() ?? 1;

        $class = SchoolClass::where('institution_id', $institutionId)->findOrFail($classId);

        // Pre-fill default generator dates based on academic year if available
        $this->generatorStartsOn = now()->format('Y-m-d');
        if ($class->academicYear) {
            $this->generatorEndsOn = min($class->academicYear->ends_on->format('Y-m-d'), now()->addMonths(3)->format('Y-m-d'));
        } else {
            $this->generatorEndsOn = now()->addMonths(3)->format('Y-m-d');
        }

        $this->slotValidFrom = now()->format('Y-m-d');
    }

    public function openCreateSlotModal(): void
    {
        $this->resetSlotForm();
        $this->showSlotModal = true;
    }

    public function openEditSlotModal(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $slot = ClassSchedule::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->findOrFail($id);

        $this->editingSlotId = $slot->id;
        $this->slotWeekday = $slot->weekday;
        $this->slotStartsAt = substr((string) $slot->starts_at, 0, 5);
        $this->slotEndsAt = substr((string) $slot->ends_at, 0, 5);
        $this->slotRoom = $slot->room ?? '';
        $this->slotOnlineUrl = $slot->online_url ?? '';
        $this->slotValidFrom = $slot->valid_from ? $slot->valid_from->format('Y-m-d') : '';
        $this->slotValidTo = $slot->valid_to ? $slot->valid_to->format('Y-m-d') : '';

        $this->showSlotModal = true;
    }

    public function closeSlotModal(): void
    {
        $this->showSlotModal = false;
        $this->resetSlotForm();
    }

    private function resetSlotForm(): void
    {
        $this->editingSlotId = null;
        $this->slotWeekday = 1;
        $this->slotStartsAt = '08:30';
        $this->slotEndsAt = '11:30';
        $this->slotRoom = '';
        $this->slotOnlineUrl = '';
        $this->slotValidFrom = now()->format('Y-m-d');
        $this->slotValidTo = '';
        $this->resetErrorBag();
    }

    public function saveSlot(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'slotWeekday' => ['required', 'integer', 'between:1,7'],
            'slotStartsAt' => ['required', 'date_format:H:i'],
            'slotEndsAt' => ['required', 'date_format:H:i', 'after:slotStartsAt'],
            'slotRoom' => ['nullable', 'string', 'max:100'],
            'slotOnlineUrl' => ['nullable', 'url', 'max:255'],
            'slotValidFrom' => ['nullable', 'date'],
            'slotValidTo' => ['nullable', 'date', 'after_or_equal:slotValidFrom'],
        ]);

        $formattedStartsAt = date('H:i:s', strtotime($this->slotStartsAt));
        $formattedEndsAt = date('H:i:s', strtotime($this->slotEndsAt));

        if ($this->editingSlotId) {
            $slot = ClassSchedule::where('institution_id', $institutionId)
                ->where('class_id', $this->classId)
                ->findOrFail($this->editingSlotId);

            $slot->update([
                'weekday' => $this->slotWeekday,
                'starts_at' => $formattedStartsAt,
                'ends_at' => $formattedEndsAt,
                'room' => trim($this->slotRoom) ?: null,
                'online_url' => trim($this->slotOnlineUrl) ?: null,
                'valid_from' => $this->slotValidFrom ?: null,
                'valid_to' => $this->slotValidTo ?: null,
            ]);

            audit('schedule.updated', "Updated recurring schedule slot #{$slot->id} for class #{$this->classId}", $slot);
            session()->flash('success', __('Schedule slot updated successfully.'));
        } else {
            $slot = ClassSchedule::create([
                'institution_id' => $institutionId,
                'class_id' => $this->classId,
                'weekday' => $this->slotWeekday,
                'starts_at' => $formattedStartsAt,
                'ends_at' => $formattedEndsAt,
                'room' => trim($this->slotRoom) ?: null,
                'online_url' => trim($this->slotOnlineUrl) ?: null,
                'valid_from' => $this->slotValidFrom ?: null,
                'valid_to' => $this->slotValidTo ?: null,
            ]);

            audit('schedule.created', "Created recurring schedule slot ({$slot->weekday_name} {$slot->starts_at}) for class #{$this->classId}", $slot);
            session()->flash('success', __('Schedule slot created successfully.'));
        }

        $this->closeSlotModal();
    }

    public function deleteSlot(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        $slot = ClassSchedule::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->findOrFail($id);

        $day = $slot->weekday_name;
        $time = $slot->starts_at;
        $slot->delete();

        audit('schedule.deleted', "Deleted schedule slot ({$day} {$time}) for class #{$this->classId}");
        session()->flash('success', __('Schedule slot deleted successfully.'));
    }

    public function generateSessions(): void
    {
        $institutionId = current_institution_id() ?? 1;
        $class = SchoolClass::where('institution_id', $institutionId)->findOrFail($this->classId);

        $this->validate([
            'generatorStartsOn' => ['required', 'date'],
            'generatorEndsOn' => ['required', 'date', 'after_or_equal:generatorStartsOn'],
        ]);

        $generator = app(ScheduleGenerator::class);
        $result = $generator->generateForClass($class, $this->generatorStartsOn, $this->generatorEndsOn);

        audit('sessions.generated', "Generated {$result['created']} sessions for class {$class->title} ({$this->generatorStartsOn} to {$this->generatorEndsOn})", $class);

        session()->flash('success', __('Generation complete: :created new sessions created (:existing existing preserved).', [
            'created' => $result['created'],
            'existing' => $result['existing'],
        ]));
    }

    public function safeRegenerateSessions(): void
    {
        $institutionId = current_institution_id() ?? 1;
        $class = SchoolClass::where('institution_id', $institutionId)->findOrFail($this->classId);

        $this->validate([
            'generatorStartsOn' => ['required', 'date'],
            'generatorEndsOn' => ['required', 'date', 'after_or_equal:generatorStartsOn'],
        ]);

        $generator = app(ScheduleGenerator::class);
        $result = $generator->regenerateForClass($class, $this->generatorStartsOn, $this->generatorEndsOn);

        audit('sessions.regenerated', "Safely regenerated sessions for class {$class->title} ({$this->generatorStartsOn} to {$this->generatorEndsOn})", $class);

        session()->flash('success', __('Safe sync complete: :created new sessions created, :preserved sessions with data preserved, :removed empty sessions removed.', [
            'created' => $result['created'],
            'preserved' => $result['preserved_with_data'],
            'removed' => $result['removed_empty'],
        ]));
    }

    public function openCancelModal(int $sessionId): void
    {
        $this->cancellingSessionId = $sessionId;
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->cancellingSessionId = null;
        $this->cancelReason = '';
        $this->showCancelModal = false;
    }

    public function cancelSession(): void
    {
        $institutionId = current_institution_id() ?? 1;

        $this->validate([
            'cancelReason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if (! $this->cancellingSessionId) {
            return;
        }

        $session = ClassSession::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->findOrFail($this->cancellingSessionId);

        $session->update([
            'status' => 'cancelled',
            'cancel_reason' => trim($this->cancelReason),
        ]);

        audit('session.cancelled', "Cancelled session on {$session->date->format('Y-m-d')}: {$this->cancelReason}", $session);

        $this->closeCancelModal();
        session()->flash('success', __('Session cancelled successfully.'));
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;

        $class = SchoolClass::where('institution_id', $institutionId)
            ->with(['academicYear', 'teacher'])
            ->findOrFail($this->classId);

        $schedules = ClassSchedule::where('institution_id', $institutionId)
            ->where('class_id', $this->classId)
            ->orderBy('weekday')
            ->orderBy('starts_at')
            ->get();

        $sessionQuery = ClassSession::where('institution_id', $institutionId)
            ->where('class_id', $this->classId);

        if ($this->sessionStatusFilter !== 'all') {
            $sessionQuery->where('status', $this->sessionStatusFilter);
        }

        $sessions = $sessionQuery->orderBy('date')->orderBy('starts_at')->paginate(15);

        return view('livewire.admin.class-schedule-manager', [
            'class' => $class,
            'schedules' => $schedules,
            'sessions' => $sessions,
        ]);
    }
}
