<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class ScheduleGenerator
{
    /**
     * Generate dated sessions for a class from its recurring weekly schedules
     * across the given date range. Existing sessions are preserved.
     *
     * @return array{created: int, existing: int}
     */
    public function generateForClass(SchoolClass $class, CarbonInterface|string $startDate, CarbonInterface|string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($end->lt($start)) {
            return ['created' => 0, 'existing' => 0];
        }

        $schedules = $class->schedules()->get();
        if ($schedules->isEmpty()) {
            return ['created' => 0, 'existing' => 0];
        }

        $created = 0;
        $existing = 0;

        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $isoWeekday = $cursor->isoWeekday(); // 1 = Monday, ..., 7 = Sunday
            $dateStr = $cursor->toDateString();

            /** @var ClassSchedule $schedule */
            foreach ($schedules as $schedule) {
                if ($schedule->weekday !== $isoWeekday) {
                    continue;
                }

                // Check validity range of schedule if set
                if ($schedule->valid_from && $cursor->lt($schedule->valid_from->startOfDay())) {
                    continue;
                }
                if ($schedule->valid_to && $cursor->gt($schedule->valid_to->endOfDay())) {
                    continue;
                }

                $timeStr = date('H:i:s', strtotime($schedule->starts_at));
                $endTimeStr = date('H:i:s', strtotime($schedule->ends_at));

                // Check if session already exists for this exact time
                $sessionExists = ClassSession::where('class_id', $class->id)
                    ->whereDate('date', $dateStr)
                    ->where(function ($q) use ($timeStr) {
                        $q->where('starts_at', $timeStr)
                            ->orWhere('starts_at', substr($timeStr, 0, 5));
                    })
                    ->exists();

                if ($sessionExists) {
                    $existing++;

                    continue;
                }

                ClassSession::create([
                    'institution_id' => $class->institution_id,
                    'class_id' => $class->id,
                    'schedule_id' => $schedule->id,
                    'title' => "{$class->title} ({$cursor->format('M d')})",
                    'date' => $dateStr,
                    'starts_at' => $timeStr,
                    'ends_at' => $endTimeStr,
                    'room' => $schedule->room ?: $class->room,
                    'online_url' => $schedule->online_url ?: $class->online_url,
                    'status' => 'scheduled',
                ]);

                $created++;
            }

            $cursor->addDay();
        }

        return [
            'created' => $created,
            'existing' => $existing,
        ];
    }

    /**
     * Safely regenerate sessions for a class across a date range.
     * Sessions that have attached operational data (attendance records, materials, quizzes)
     * are NEVER deleted even if weekly schedules have changed (§7.1, T-3.3).
     *
     * @return array{created: int, preserved_with_data: int, removed_empty: int}
     */
    public function regenerateForClass(SchoolClass $class, CarbonInterface|string $startDate, CarbonInterface|string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $existingSessions = ClassSession::where('class_id', $class->id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get();

        $activeSchedules = $class->schedules()->get();
        $preservedWithData = 0;
        $removedEmpty = 0;

        /** @var ClassSession $session */
        foreach ($existingSessions as $session) {
            $sessionWeekday = Carbon::parse($session->date)->isoWeekday();

            // Check if this session still matches an active schedule slot
            $matchesCurrentSchedule = $activeSchedules->contains(function (ClassSchedule $s) use ($session, $sessionWeekday) {
                return $s->weekday === $sessionWeekday &&
                    substr((string) $s->starts_at, 0, 5) === substr((string) $session->starts_at, 0, 5);
            });

            if (! $matchesCurrentSchedule) {
                // If schedule changed, check if session has data
                if ($session->hasAttachedData() || $session->status === 'completed') {
                    $preservedWithData++;
                } else {
                    $session->delete();
                    $removedEmpty++;
                }
            }
        }

        // Now generate missing sessions for new schedules
        $generationResult = $this->generateForClass($class, $start, $end);

        return [
            'created' => $generationResult['created'],
            'preserved_with_data' => $preservedWithData,
            'removed_empty' => $removedEmpty,
        ];
    }
}
