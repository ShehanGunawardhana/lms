<div class="space-y-6">
    <!-- Header with Breadcrumbs & Back link -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('admin.classes') }}" class="hover:text-brand-600 transition-colors">{{ __('Classes') }}</a>
                <span>/</span>
                <span class="text-slate-700 truncate max-w-xs">{{ $class->title }}</span>
                <span>/</span>
                <span class="text-brand-600">{{ __('Schedules & Sessions') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ __('Schedule & Session Planner') }}
            </h1>
            <p class="text-sm text-slate-600 mt-1">
                {{ __('Define weekly recurring timetable slots and generate dated sessions for attendance and materials.') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.classes') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to Classes') }}
            </a>
        </div>
    </div>

    <!-- Class Info Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-brand-950 to-slate-900 text-white p-5 rounded-3xl shadow-lg border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="text-lg font-black tracking-tight text-white">{{ $class->title }}</span>
                @if ($class->kind === \App\Enums\ClassKind::NurseryGroup || $class->kind === 'nursery_group')
                    <span class="px-2 py-0.5 rounded-md bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[10px] font-bold">{{ __('Nursery Group') }}</span>
                @else
                    <span class="px-2 py-0.5 rounded-md bg-brand-400/20 text-brand-300 border border-brand-400/30 text-[10px] font-bold">{{ __('Institute Class') }}</span>
                @endif
                <span class="px-2 py-0.5 rounded-md bg-white/10 text-slate-300 text-[10px] font-medium">{{ $class->academicYear?->name ?? __('No Year') }}</span>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-300 flex-wrap">
                @if ($class->teacher)
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        {{ $class->teacher->name }}
                    </span>
                @endif
                @if ($class->room)
                    <span>• {{ __('Venue:') }} {{ $class->room }}</span>
                @endif
                @if ($class->fee_amount > 0)
                    <span>• {{ __('Fee:') }} LKR {{ number_format((float) $class->fee_amount, 2) }}</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-white/10 text-xs font-bold text-slate-200">
                {{ $schedules->count() }} {{ __('recurring slots') }}
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-brand-500/30 border border-brand-400/30 text-xs font-bold text-brand-200">
                {{ $sessions->total() }} {{ __('dated sessions') }}
            </span>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    <!-- Two Column Layout: Weekly Timetable Slots & Session Generator -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Weekly Timetable Slots (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">{{ __('Weekly Recurring Timetable') }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Recurring days and times when this class meets.') }}</p>
                    </div>
                    <button 
                        type="button" 
                        wire:click="openCreateSlotModal"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 transition-colors inline-flex items-center gap-1.5 btn-interactive">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        {{ __('Add Slot') }}
                    </button>
                </div>

                <div class="space-y-2.5">
                    @forelse ($schedules as $slot)
                        <div class="p-3.5 rounded-2xl border border-slate-100 bg-slate-50/70 hover:bg-slate-50 transition-colors flex items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-brand-50 text-brand-700 text-xs font-extrabold">{{ $slot->weekday_name }}</span>
                                    <span class="font-mono font-bold text-slate-900 text-xs">
                                        {{ date('h:i A', strtotime($slot->starts_at)) }} – {{ date('h:i A', strtotime($slot->ends_at)) }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2">
                                    @if ($slot->room)
                                        <span>{{ $slot->room }}</span>
                                    @endif
                                    @if ($slot->online_url)
                                        <span>• <a href="{{ $slot->online_url }}" target="_blank" class="text-brand-600 hover:underline">Online link</a></span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1">
                                <button 
                                    type="button" 
                                    wire:click="openEditSlotModal({{ $slot->id }})"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="deleteSlot({{ $slot->id }})"
                                    wire:confirm="{{ __('Delete this recurring slot?') }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 rounded-2xl border border-dashed border-slate-200 text-center">
                            <p class="text-xs font-bold text-slate-700">{{ __('No recurring slots defined') }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Add weekly meeting times to generate dated calendar sessions.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Session Generator & Safe Sync (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">{{ __('Dated Session Generator & Safe Sync') }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Automatically creates calendar dates for classes according to weekly slots.') }}
                        <span class="font-semibold text-brand-600">{{ __('Safe sync never deletes sessions with existing attendance or materials.') }}</span>
                    </p>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Start Date') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="generatorStartsOn"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('generatorStartsOn') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('End Date') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="generatorEndsOn"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('generatorEndsOn') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-3 flex-wrap">
                        <button 
                            type="button" 
                            wire:click="generateSessions"
                            wire:loading.attr="disabled"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 shadow-sm transition-all inline-flex items-center gap-2 btn-interactive">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                            {{ __('Generate Sessions') }}
                        </button>

                        <button 
                            type="button" 
                            wire:click="safeRegenerateSessions"
                            wire:loading.attr="disabled"
                            title="{{ __('Safely sync timetable changes without deleting sessions with recorded data') }}"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors inline-flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            {{ __('Safe Sync / Regenerate') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generated Sessions List Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">{{ __('Dated Class Sessions') }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Chronological schedule of individual lecture/activity sessions.') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    wire:click="$set('sessionStatusFilter', 'all')"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $sessionStatusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ __('All') }}
                </button>
                <button 
                    type="button" 
                    wire:click="$set('sessionStatusFilter', 'scheduled')"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $sessionStatusFilter === 'scheduled' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ __('Scheduled') }}
                </button>
                <button 
                    type="button" 
                    wire:click="$set('sessionStatusFilter', 'completed')"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $sessionStatusFilter === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ __('Completed') }}
                </button>
                <button 
                    type="button" 
                    wire:click="$set('sessionStatusFilter', 'cancelled')"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $sessionStatusFilter === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ __('Cancelled') }}
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">{{ __('Session Date') }}</th>
                        <th class="px-4 py-3.5">{{ __('Weekday') }}</th>
                        <th class="px-4 py-3.5">{{ __('Time') }}</th>
                        <th class="px-4 py-3.5">{{ __('Venue') }}</th>
                        <th class="px-4 py-3.5">{{ __('Status') }}</th>
                        <th class="px-4 py-3.5">{{ __('Attached Data') }}</th>
                        <th class="px-5 py-3.5 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($sessions as $session)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-900 text-sm">
                                {{ $session->date->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-600">
                                {{ $session->date->format('l') }}
                            </td>
                            <td class="px-4 py-4 font-mono font-medium text-slate-800">
                                {{ date('h:i A', strtotime($session->starts_at)) }} – {{ date('h:i A', strtotime($session->ends_at)) }}
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                @if ($session->room)
                                    <span>{{ $session->room }}</span>
                                @elseif ($session->online_url)
                                    <a href="{{ $session->online_url }}" target="_blank" class="text-brand-600 hover:underline">Online Link</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($session->status === 'scheduled')
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-[10px] font-bold text-blue-700">{{ __('Scheduled') }}</span>
                                @elseif ($session->status === 'completed')
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-700">{{ __('Completed') }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-rose-50 border border-rose-200 text-[10px] font-bold text-rose-700">{{ __('Cancelled') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($session->hasAttachedData())
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-800 flex items-center gap-1 w-fit">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        {{ __('Has Data') }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if ($session->status === 'scheduled')
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button 
                                            type="button" 
                                            wire:click="openRescheduleModal({{ $session->id }})"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition-colors">
                                            {{ __('Reschedule') }}
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click="openCancelModal({{ $session->id }})"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                                            {{ __('Cancel') }}
                                        </button>
                                    </div>
                                @elseif ($session->cancel_reason)
                                    <span class="text-[11px] text-slate-500 italic max-w-xs truncate inline-block" title="{{ $session->cancel_reason }}">
                                        {{ $session->cancel_reason }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                    </div>
                                    <p class="font-bold text-slate-800 text-sm">{{ __('No sessions generated yet') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('Define weekly recurring timetable slots and generate sessions above.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sessions->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: RECURRING SLOT CREATE / EDIT -->
    <!-- ============================================================== -->
    @if ($showSlotModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h3 class="text-lg font-extrabold text-slate-900">
                        {{ $editingSlotId ? __('Edit Recurring Slot') : __('Add Weekly Timetable Slot') }}
                    </h3>
                    <button type="button" wire:click="closeSlotModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="saveSlot" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Weekday') }} <span class="text-rose-500">*</span></label>
                        <select 
                            wire:model="slotWeekday"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                            <option value="1">{{ __('Monday') }}</option>
                            <option value="2">{{ __('Tuesday') }}</option>
                            <option value="3">{{ __('Wednesday') }}</option>
                            <option value="4">{{ __('Thursday') }}</option>
                            <option value="5">{{ __('Friday') }}</option>
                            <option value="6">{{ __('Saturday') }}</option>
                            <option value="7">{{ __('Sunday') }}</option>
                        </select>
                        @error('slotWeekday') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Starts At') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="time" 
                                wire:model="slotStartsAt"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-mono font-bold">
                            @error('slotStartsAt') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Ends At') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="time" 
                                wire:model="slotEndsAt"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-mono font-bold">
                            @error('slotEndsAt') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Physical Room / Venue') }}</label>
                            <input 
                                type="text" 
                                wire:model="slotRoom"
                                placeholder="{{ $class->room ?: 'e.g. Hall 401' }}"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('slotRoom') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Online Link') }}</label>
                            <input 
                                type="url" 
                                wire:model="slotOnlineUrl"
                                placeholder="https://zoom.us/..."
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-mono">
                            @error('slotOnlineUrl') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Valid From') }}</label>
                            <input 
                                type="date" 
                                wire:model="slotValidFrom"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('slotValidFrom') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Valid To (Optional)') }}</label>
                            <input 
                                type="date" 
                                wire:model="slotValidTo"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('slotValidTo') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeSlotModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all btn-interactive">
                            {{ $editingSlotId ? __('Update Slot') : __('Save Slot') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL: CANCEL SESSION WITH REASON -->
    <!-- ============================================================== -->
    @if ($showCancelModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-extrabold text-slate-900">{{ __('Cancel Session') }}</h3>
                    <button type="button" wire:click="closeCancelModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <p class="text-xs text-slate-600 mb-4">
                    {{ __('Please provide a cancellation reason. Affected students and parents can be notified.') }}
                </p>

                <form wire:submit="cancelSession" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Reason for Cancellation') }} <span class="text-rose-500">*</span></label>
                        <textarea 
                            wire:model="cancelReason"
                            rows="3"
                            placeholder="e.g. Lecturer unwell, public holiday observed, venue maintenance..."
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 shadow-2xs font-medium"></textarea>
                        @error('cancelReason') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeCancelModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                            {{ __('Close') }}
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition-colors shadow-xs">
                            {{ __('Confirm Cancellation') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL: RESCHEDULE SESSION WITH REASON -->
    <!-- ============================================================== -->
    @if ($showRescheduleModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">{{ __('Reschedule Class Session') }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Select a new date, time, and record mandatory reason.') }}</p>
                    </div>
                    <button type="button" wire:click="closeRescheduleModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="rescheduleSession" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('New Session Date') }} <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            wire:model="rescheduleDate"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                        @error('rescheduleDate') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('New Start Time') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="time" 
                                wire:model="rescheduleStartsAt"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-mono font-bold">
                            @error('rescheduleStartsAt') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('New End Time') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="time" 
                                wire:model="rescheduleEndsAt"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-mono font-bold">
                            @error('rescheduleEndsAt') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Room / Venue') }}</label>
                        <input 
                            type="text" 
                            wire:model="rescheduleRoom"
                            placeholder="{{ __('Leave blank to keep current room...') }}"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                        @error('rescheduleRoom') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Reason for Rescheduling') }} <span class="text-rose-500">*</span></label>
                        <textarea 
                            wire:model="rescheduleReason"
                            rows="3"
                            placeholder="e.g. Moved to Saturday morning due to lecturer conference..."
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-medium"></textarea>
                        @error('rescheduleReason') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeRescheduleModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 transition-all shadow-sm btn-interactive">
                            {{ __('Confirm Reschedule & Notify') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
