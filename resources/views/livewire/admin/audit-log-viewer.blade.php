<div class="space-y-6">
    <!-- Header / Description -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight">{{ __('Audit Trail & Activity Log') }}</h2>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Immutable security record of sensitive decisions, approvals, reprints, manual edits, and role changes.') }}
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <x-badge variant="brand" :dot="true">{{ __('Tamper-Evident') }}</x-badge>
        </div>
    </div>

    <!-- Filters Card -->
    <x-card :padding="true" :hover="false">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <x-input 
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search by action, keyword, or description...') }}"
                />
            </div>
            <div>
                <x-select wire:model.live="eventFilter" placeholder="{{ __('All Events') }}">
                    <option value="">{{ __('All Events') }}</option>
                    @foreach ($events as $event)
                        <option value="{{ $event }}">{{ ucfirst(str_replace('.', ' ', $event)) }}</option>
                    @endforeach
                </x-select>
            </div>
        </div>
    </x-card>

    <!-- Audit Logs Table -->
    <x-card :padding="false">
        @if ($logs->count() > 0)
            <x-table :headers="[__('Timestamp'), __('Event'), __('User / Actor'), __('Description'), __('Target')]">
                @foreach ($logs as $log)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3.5 text-xs text-slate-500 font-mono whitespace-nowrap">
                            {{ $log->created_at->format('M j, Y H:i:s') }}
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <x-badge variant="info" size="sm">
                                {{ $log->event ?? 'action' }}
                            </x-badge>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-900 font-semibold whitespace-nowrap">
                            {{ $log->causer?->name ?? __('System') }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-700">
                            {{ $log->description }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 font-mono whitespace-nowrap">
                            @if ($log->subject_type)
                                {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                            @else
                                <span class="text-slate-400">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @else
            <div class="p-8">
                <x-empty-state 
                    title="{{ __('No audit entries found') }}"
                    description="{{ __('No activity logs match your filter criteria or no actions have occurred yet.') }}"
                />
            </div>
        @endif
    </x-card>
</div>
