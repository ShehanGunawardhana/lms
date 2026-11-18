<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Lecturer Portfolios Review Queue') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Review faculty biographies, academic credentials, sample materials, and publish to the public site (§5.5)') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-xs">
                    {{ __('User Directory') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Toast / Session Alerts -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Metrics Overview -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Pending Review') }}</p>
                <p class="text-2xl font-black text-brand-600 mt-1">{{ $metrics['pending'] }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Awaiting administrative action') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Published (Live)') }}</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $metrics['published'] }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Active on public directory') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-rose-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Revisions Requested') }}</p>
                <p class="text-2xl font-black text-rose-600 mt-1">{{ $metrics['rejected'] }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Returned with feedback') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-amber-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Drafts') }}</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $metrics['draft'] }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Under faculty editing') }}</p>
            </x-card>
        </div>

        <!-- Filter Bar -->
        <x-card>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Status Tabs -->
                <div class="inline-flex rounded-xl p-1 bg-slate-100 border border-slate-200 text-xs font-bold">
                    <button type="button"
                            wire:click="$set('statusFilter', 'pending')"
                            class="px-3.5 py-1.5 rounded-lg transition-all cursor-pointer {{ $statusFilter === 'pending' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('Pending Review') }}
                        @if ($metrics['pending'] > 0)
                            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-brand-100 text-brand-700 font-black">
                                {{ $metrics['pending'] }}
                            </span>
                        @endif
                    </button>

                    <button type="button"
                            wire:click="$set('statusFilter', 'published')"
                            class="px-3.5 py-1.5 rounded-lg transition-all cursor-pointer {{ $statusFilter === 'published' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('Published (Live)') }}
                    </button>

                    <button type="button"
                            wire:click="$set('statusFilter', 'rejected')"
                            class="px-3.5 py-1.5 rounded-lg transition-all cursor-pointer {{ $statusFilter === 'rejected' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('Revision Required') }}
                    </button>

                    <button type="button"
                            wire:click="$set('statusFilter', 'all')"
                            class="px-3.5 py-1.5 rounded-lg transition-all cursor-pointer {{ $statusFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ __('All Portfolios') }}
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative sm:w-72">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </span>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="{{ __('Search lecturer, subject, headline...') }}"
                           class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white shadow-xs" />
                </div>
            </div>
        </x-card>

        <!-- Portfolios Review List -->
        <x-card>
            @if ($portfolios->isEmpty())
                <div class="py-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center font-bold text-lg">
                        🎓
                    </div>
                    <p class="text-sm font-bold text-slate-800">{{ __('No portfolios found') }}</p>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ __('There are currently no faculty portfolios matching the selected status or search filter.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                                <th class="py-3 px-3">{{ __('Lecturer / Faculty') }}</th>
                                <th class="py-3 px-3">{{ __('Professional Headline') }}</th>
                                <th class="py-3 px-3">{{ __('Subjects') }}</th>
                                <th class="py-3 px-3">{{ __('Status') }}</th>
                                <th class="py-3 px-3">{{ __('Last Updated') }}</th>
                                <th class="py-3 px-3 text-right">{{ __('Review & Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($portfolios as $item)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <!-- Lecturer info -->
                                    <td class="py-3.5 px-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-800 font-bold flex items-center justify-center text-xs shrink-0 overflow-hidden">
                                                @if ($item->photo)
                                                    <img src="{{ $item->photo->temporarySignedUrl(now()->addHour()) }}" class="w-full h-full object-cover" alt="" />
                                                @else
                                                    {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $item->user->name }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono">{{ $item->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Headline -->
                                    <td class="py-3.5 px-3 max-w-xs">
                                        <p class="font-medium text-slate-800 line-clamp-1">
                                            {{ $item->headline ?? __('Not specified') }}
                                        </p>
                                    </td>

                                    <!-- Subjects -->
                                    <td class="py-3.5 px-3">
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @if (is_array($item->subjects) && !empty($item->subjects))
                                                @foreach (array_slice($item->subjects, 0, 3) as $subj)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                        {{ $subj }}
                                                    </span>
                                                @endforeach
                                                @if (count($item->subjects) > 3)
                                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-50 text-slate-400">
                                                        +{{ count($item->subjects) - 3 }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-400 italic text-[11px]">{{ __('None added') }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-3">
                                        @php
                                            $badgeClasses = match ($item->portfolio_status) {
                                                'published' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                'pending' => 'bg-brand-100 text-brand-800 border-brand-200',
                                                'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                                                default => 'bg-amber-100 text-amber-800 border-amber-200',
                                            };
                                            $label = match ($item->portfolio_status) {
                                                'published' => __('Published (Live)'),
                                                'pending' => __('Pending Review'),
                                                'rejected' => __('Revision Required'),
                                                default => __('Draft'),
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClasses }}">
                                            {{ $label }}
                                        </span>
                                    </td>

                                    <!-- Updated At -->
                                    <td class="py-3.5 px-3 text-slate-500 font-mono text-[11px]">
                                        {{ $item->updated_at->diffForHumans() }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-3.5 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                            <button type="button"
                                                    wire:click="openDetailModal({{ $item->id }})"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs cursor-pointer">
                                                {{ __('Review Details') }}
                                            </button>

                                            @if ($item->portfolio_status === 'pending')
                                                <button type="button"
                                                        wire:click="approveAndPublish({{ $item->id }})"
                                                        class="px-2.5 py-1 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-xs cursor-pointer">
                                                    {{ __('Publish') }}
                                                </button>
                                                <button type="button"
                                                        wire:click="openRejectModal({{ $item->id }})"
                                                        class="px-2.5 py-1 rounded-lg text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 cursor-pointer">
                                                    {{ __('Request Revision') }}
                                                </button>
                                            @elseif ($item->portfolio_status === 'published')
                                                <button type="button"
                                                        wire:click="unpublish({{ $item->id }})"
                                                        wire:confirm="{{ __('Unpublish this portfolio from the public directory?') }}"
                                                        class="px-2 py-1 rounded-lg text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer">
                                                    {{ __('Unpublish') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    {{ $portfolios->links() }}
                </div>
            @endif
        </x-card>
    </div>

    <!-- Portfolio Detail Preview Modal -->
    @if ($showDetailModal && $selectedProfile)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 space-y-5 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-brand-100 text-brand-800 font-bold flex items-center justify-center text-lg shrink-0 overflow-hidden shadow-inner">
                            @if ($selectedProfile->photo)
                                <img src="{{ $selectedProfile->photo->temporarySignedUrl(now()->addHour()) }}" class="w-full h-full object-cover" alt="" />
                            @else
                                {{ strtoupper(substr($selectedProfile->user->name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900">{{ $selectedProfile->user->name }}</h3>
                            <p class="text-xs text-brand-600 font-semibold">{{ $selectedProfile->headline ?? __('No headline') }}</p>
                            <p class="text-[11px] text-slate-400 font-mono">{{ $selectedProfile->user->email }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Academic Bio -->
                <div class="space-y-1">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Academic Biography') }}</h4>
                    <p class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                        {{ $selectedProfile->bio ?: __('No biography provided.') }}
                    </p>
                </div>

                <!-- Qualifications -->
                <div class="space-y-1">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Qualifications & Degrees') }}</h4>
                    <p class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                        {{ $selectedProfile->qualifications ?: __('No qualifications listed.') }}
                    </p>
                </div>

                <!-- Subjects -->
                <div class="space-y-1">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Subjects Taught') }}</h4>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        @if (is_array($selectedProfile->subjects) && !empty($selectedProfile->subjects))
                            @foreach ($selectedProfile->subjects as $subject)
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $subject }}
                                </span>
                            @endforeach
                        @else
                            <p class="text-xs text-slate-400 italic">{{ __('None specified') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Sample Materials -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Attached Sample Materials (§5.5)') }}</h4>
                    @if (is_array($selectedProfile->sample_materials) && !empty($selectedProfile->sample_materials))
                        <div class="space-y-2">
                            @foreach ($selectedProfile->sample_materials as $material)
                                <div class="p-3 rounded-xl border border-slate-200 bg-white flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                            <span>{{ $material['title'] ?? 'Material' }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-slate-100 text-slate-600">
                                                {{ $material['type'] ?? 'document' }}
                                            </span>
                                        </div>
                                        @if (!empty($material['description']))
                                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $material['description'] }}</p>
                                        @endif
                                    </div>
                                    @if (!empty($material['url']))
                                        <a href="{{ $material['url'] }}" target="_blank" class="px-2.5 py-1 rounded-lg text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 inline-flex items-center gap-1">
                                            <span>{{ __('Open Link') }}</span>
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">{{ __('No sample materials attached.') }}</p>
                    @endif
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button"
                            wire:click="closeDetailModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        {{ __('Close') }}
                    </button>
                    @if ($selectedProfile->portfolio_status === 'pending')
                        <button type="button"
                                wire:click="openRejectModal({{ $selectedProfile->id }})"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                            {{ __('Request Revision') }}
                        </button>
                        <button type="button"
                                wire:click="approveAndPublish({{ $selectedProfile->id }})"
                                class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/20 transition-all btn-interactive">
                            {{ __('Approve & Publish to Directory') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Request Revision / Rejection Modal -->
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold">
                        ✍️
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Request Portfolio Revision') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Provide constructive feedback to the faculty member.') }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">{{ __('Feedback / Changes Requested (Mandatory)') }}</label>
                    <textarea wire:model="rejectionReason"
                              rows="3"
                              class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                              placeholder="{{ __('Explain what needs updating (e.g. upload high-res photo, add missing subjects, expand qualifications)...') }}"></textarea>
                    @error('rejectionReason')
                        <p class="text-[11px] text-rose-600 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button"
                            wire:click="closeRejectModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button"
                            wire:click="confirmReject"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition-all shadow-md shadow-rose-600/20 cursor-pointer">
                        {{ __('Send Revision Request') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
