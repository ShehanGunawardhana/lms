<div class="space-y-6">
    <!-- Header with Breadcrumbs & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <span>{{ __('Academics') }}</span>
                <span>/</span>
                <span class="text-brand-600">{{ __('Structure & Calendars') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                {{ __('Academic Years, Terms & Batches') }}
            </h1>
            <p class="text-sm text-slate-600 mt-1">
                {{ __('Configure academic cycles, terms, semesters, and student cohort batches or nursery groups.') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if ($activeTab === 'years')
                <button 
                    type="button" 
                    wire:click="openCreateYearModal"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer inline-flex items-center gap-2 btn-interactive">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('New Academic Year') }}
                </button>
            @elseif ($activeTab === 'terms')
                <button 
                    type="button" 
                    wire:click="openCreateTermModal()"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer inline-flex items-center gap-2 btn-interactive">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('New Term / Semester') }}
                </button>
            @else
                <button 
                    type="button" 
                    wire:click="openCreateBatchModal"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer inline-flex items-center gap-2 btn-interactive">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('New Batch or Group') }}
                </button>
            @endif
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

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 flex items-center gap-2 overflow-x-auto pb-1">
        <button 
            type="button" 
            wire:click="setActiveTab('years')"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeTab === 'years' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
            {{ __('Academic Years') }}
            <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] {{ $activeTab === 'years' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $years->total() }}</span>
        </button>

        <button 
            type="button" 
            wire:click="setActiveTab('terms')"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeTab === 'terms' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            {{ __('Terms & Semesters') }}
            <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] {{ $activeTab === 'terms' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $terms->total() }}</span>
        </button>

        <button 
            type="button" 
            wire:click="setActiveTab('batches')"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeTab === 'batches' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
            {{ __('Batches & Groups') }}
            <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] {{ $activeTab === 'batches' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $batches->total() }}</span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: ACADEMIC YEARS -->
    <!-- ============================================================== -->
    @if ($activeTab === 'years')
        <div class="space-y-4">
            <!-- Search & Filters -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="$set('yearStatusFilter', 'all')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $yearStatusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('All') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('yearStatusFilter', 'active')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $yearStatusFilter === 'active' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('Active') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('yearStatusFilter', 'upcoming')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $yearStatusFilter === 'upcoming' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('Upcoming') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('yearStatusFilter', 'archived')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $yearStatusFilter === 'archived' ? 'bg-slate-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('Archived') }}
                    </button>
                </div>

                <div class="w-full sm:w-72">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="yearSearch"
                        placeholder="{{ __('Search year by name or code...') }}"
                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                </div>
            </div>

            <!-- Years List Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">{{ __('Academic Year') }}</th>
                                <th class="px-4 py-3.5">{{ __('Code') }}</th>
                                <th class="px-4 py-3.5">{{ __('Duration') }}</th>
                                <th class="px-4 py-3.5">{{ __('Status') }}</th>
                                <th class="px-4 py-3.5">{{ __('Terms') }}</th>
                                <th class="px-4 py-3.5">{{ __('Classes') }}</th>
                                <th class="px-5 py-3.5 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($years as $year)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 text-sm">{{ $year->name }}</span>
                                            @if ($year->is_current)
                                                <span class="px-2 py-0.5 rounded-md bg-brand-50 border border-brand-200 text-[10px] font-bold text-brand-700 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                                                    {{ __('Current Year') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($year->description)
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">{{ $year->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 font-mono font-medium text-slate-600">
                                        {{ $year->code ?: '—' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ $year->starts_on->format('M d, Y') }}</span>
                                            <span class="text-slate-400">→</span>
                                            <span>{{ $year->ends_on->format('M d, Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($year->status === 'active')
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-700">{{ __('Active') }}</span>
                                        @elseif ($year->status === 'upcoming')
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-[10px] font-bold text-amber-700">{{ __('Upcoming') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-600">{{ __('Archived') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <button 
                                            type="button" 
                                            wire:click="$set('termYearFilter', {{ $year->id }}); setActiveTab('terms');"
                                            class="font-bold text-brand-600 hover:text-brand-800 hover:underline">
                                            {{ $year->terms_count }} {{ __('Terms') }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-2 py-1 rounded-md bg-slate-100 font-semibold text-slate-700">
                                            {{ $year->classes_count }} {{ __('classes') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if (! $year->is_current)
                                                <button 
                                                    type="button" 
                                                    wire:click="setYearAsCurrent({{ $year->id }})"
                                                    title="{{ __('Set as active current year') }}"
                                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition-colors">
                                                    {{ __('Make Current') }}
                                                </button>
                                            @endif
                                            <button 
                                                type="button" 
                                                wire:click="openEditYearModal({{ $year->id }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="deleteYear({{ $year->id }})"
                                                wire:confirm="{{ __('Are you sure you want to delete this academic year? This action cannot be undone.') }}"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center">
                                        <div class="max-w-xs mx-auto space-y-2">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                            </div>
                                            <p class="font-bold text-slate-800 text-sm">{{ __('No academic years found') }}</p>
                                            <p class="text-xs text-slate-500">{{ __('Create the first academic year to structure terms and classes.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($years->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $years->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- TAB 2: TERMS & SEMESTERS -->
    <!-- ============================================================== -->
    @if ($activeTab === 'terms')
        <div class="space-y-4">
            <!-- Filter by Academic Year -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <label class="text-xs font-bold text-slate-700 whitespace-nowrap">{{ __('Academic Year:') }}</label>
                    <select 
                        wire:model.live="termYearFilter"
                        class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                        <option value="">{{ __('— All Academic Years —') }}</option>
                        @foreach ($allYears as $y)
                            <option value="{{ $y->id }}">{{ $y->name }} {{ $y->is_current ? '★ (Current)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Terms List Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">{{ __('Term / Semester Name') }}</th>
                                <th class="px-4 py-3.5">{{ __('Academic Year') }}</th>
                                <th class="px-4 py-3.5">{{ __('Dates') }}</th>
                                <th class="px-4 py-3.5">{{ __('Current Indicator') }}</th>
                                <th class="px-5 py-3.5 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($terms as $term)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4 font-bold text-slate-900 text-sm">
                                        {{ $term->name }}
                                    </td>
                                    <td class="px-4 py-4 font-medium text-slate-700">
                                        {{ $term->academicYear?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ $term->starts_on->format('M d, Y') }}</span>
                                            <span class="text-slate-400">→</span>
                                            <span>{{ $term->ends_on->format('M d, Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($term->is_current)
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-700 flex items-center gap-1 w-fit">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                {{ __('Current Term') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[11px]">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if (! $term->is_current)
                                                <button 
                                                    type="button" 
                                                    wire:click="setTermAsCurrent({{ $term->id }})"
                                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                                                    {{ __('Make Current') }}
                                                </button>
                                            @endif
                                            <button 
                                                type="button" 
                                                wire:click="openEditTermModal({{ $term->id }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="deleteTerm({{ $term->id }})"
                                                wire:confirm="{{ __('Delete this term?') }}"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center">
                                        <div class="max-w-xs mx-auto space-y-2">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            </div>
                                            <p class="font-bold text-slate-800 text-sm">{{ __('No terms found') }}</p>
                                            <p class="text-xs text-slate-500">{{ __('Create terms or semesters for the selected academic cycle.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($terms->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $terms->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- TAB 3: BATCHES & GROUPS -->
    <!-- ============================================================== -->
    @if ($activeTab === 'batches')
        <div class="space-y-4">
            <!-- Filter & Search Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <button 
                        type="button" 
                        wire:click="$set('batchKindFilter', 'all')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $batchKindFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('All Kinds') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('batchKindFilter', 'institute_batch')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $batchKindFilter === 'institute_batch' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('Institute Batches') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('batchKindFilter', 'nursery_group')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $batchKindFilter === 'nursery_group' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('Nursery Groups') }}
                    </button>

                    <select 
                        wire:model.live="batchYearFilter"
                        class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white font-semibold ml-2">
                        <option value="">{{ __('— Any Year —') }}</option>
                        @foreach ($allYears as $y)
                            <option value="{{ $y->id }}">{{ $y->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-72">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="batchSearch"
                        placeholder="{{ __('Search batch by name or code...') }}"
                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                </div>
            </div>

            <!-- Batches Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">{{ __('Batch / Group Name') }}</th>
                                <th class="px-4 py-3.5">{{ __('Code') }}</th>
                                <th class="px-4 py-3.5">{{ __('Kind') }}</th>
                                <th class="px-4 py-3.5">{{ __('Academic Year') }}</th>
                                <th class="px-4 py-3.5">{{ __('Status') }}</th>
                                <th class="px-4 py-3.5">{{ __('Classes') }}</th>
                                <th class="px-5 py-3.5 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($batches as $batch)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4">
                                        <span class="font-bold text-slate-900 text-sm">{{ $batch->name }}</span>
                                        @if ($batch->description)
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">{{ $batch->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 font-mono font-medium text-slate-600">
                                        {{ $batch->code ?: '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($batch->kind === 'nursery_group')
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-[10px] font-bold text-amber-700">
                                                {{ __('Nursery Group') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-brand-50 border border-brand-200 text-[10px] font-bold text-brand-700">
                                                {{ __('Institute Batch') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 font-medium text-slate-700">
                                        {{ $batch->academicYear?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($batch->status === 'active')
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-600">{{ __('Archived') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-2 py-1 rounded-md bg-slate-100 font-semibold text-slate-700">
                                            {{ $batch->classes_count }} {{ __('classes') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                type="button" 
                                                wire:click="toggleBatchStatus({{ $batch->id }})"
                                                class="px-2 py-1 rounded-lg text-[11px] font-bold transition-colors {{ $batch->status === 'active' ? 'text-slate-600 hover:bg-slate-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                                                {{ $batch->status === 'active' ? __('Archive') : __('Activate') }}
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="openEditBatchModal({{ $batch->id }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="deleteBatch({{ $batch->id }})"
                                                wire:confirm="{{ __('Delete this batch/group?') }}"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center">
                                        <div class="max-w-xs mx-auto space-y-2">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                                            </div>
                                            <p class="font-bold text-slate-800 text-sm">{{ __('No batches or groups found') }}</p>
                                            <p class="text-xs text-slate-500">{{ __('Create a student batch (e.g. 2026 A/L Bio Science) or nursery group.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($batches->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $batches->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL 1: ACADEMIC YEAR CREATE / EDIT -->
    <!-- ============================================================== -->
    @if ($showYearModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h3 class="text-lg font-extrabold text-slate-900">
                        {{ $editingYearId ? __('Edit Academic Year') : __('Create Academic Year') }}
                    </h3>
                    <button type="button" wire:click="closeYearModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="saveYear" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Year Name') }} <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            wire:model="yearName"
                            placeholder="e.g. 2026/2027 Academic Year"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                        @error('yearName') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Year Code') }}</label>
                            <input 
                                type="text" 
                                wire:model="yearCode"
                                placeholder="e.g. AY-2026"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-mono">
                            @error('yearCode') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Status') }} <span class="text-rose-500">*</span></label>
                            <select 
                                wire:model="yearStatus"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="upcoming">{{ __('Upcoming') }}</option>
                                <option value="archived">{{ __('Archived') }}</option>
                            </select>
                            @error('yearStatus') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Starts On') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="yearStartsOn"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('yearStartsOn') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Ends On') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="yearEndsOn"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('yearEndsOn') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Description / Notes') }}</label>
                        <textarea 
                            wire:model="yearDescription"
                            rows="2"
                            placeholder="{{ __('Optional notes on this academic cycle...') }}"
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs"></textarea>
                        @error('yearDescription') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input 
                            type="checkbox" 
                            id="yearIsCurrent"
                            wire:model="yearIsCurrent"
                            class="rounded-md border-slate-300 text-brand-600 focus:ring-brand-500/20">
                        <label for="yearIsCurrent" class="text-xs font-bold text-slate-700 cursor-pointer">
                            {{ __('Set as current academic year') }}
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeYearModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all btn-interactive">
                            {{ $editingYearId ? __('Update Academic Year') : __('Save Academic Year') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL 2: TERM CREATE / EDIT -->
    <!-- ============================================================== -->
    @if ($showTermModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h3 class="text-lg font-extrabold text-slate-900">
                        {{ $editingTermId ? __('Edit Term / Semester') : __('Create Term / Semester') }}
                    </h3>
                    <button type="button" wire:click="closeTermModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="saveTerm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Academic Year') }} <span class="text-rose-500">*</span></label>
                        <select 
                            wire:model="termAcademicYearId"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                            <option value="">{{ __('— Select Academic Year —') }}</option>
                            @foreach ($allYears as $y)
                                <option value="{{ $y->id }}">{{ $y->name }}</option>
                            @endforeach
                        </select>
                        @error('termAcademicYearId') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Term / Semester Name') }} <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            wire:model="termName"
                            placeholder="e.g. Term 1, First Semester, Spring 2026"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                        @error('termName') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Starts On') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="termStartsOn"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('termStartsOn') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Ends On') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="termEndsOn"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('termEndsOn') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input 
                            type="checkbox" 
                            id="termIsCurrent"
                            wire:model="termIsCurrent"
                            class="rounded-md border-slate-300 text-brand-600 focus:ring-brand-500/20">
                        <label for="termIsCurrent" class="text-xs font-bold text-slate-700 cursor-pointer">
                            {{ __('Set as current active term') }}
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeTermModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all btn-interactive">
                            {{ $editingTermId ? __('Update Term') : __('Save Term') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL 3: BATCH / GROUP CREATE / EDIT -->
    <!-- ============================================================== -->
    @if ($showBatchModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <h3 class="text-lg font-extrabold text-slate-900">
                        {{ $editingBatchId ? __('Edit Batch or Group') : __('Create Batch or Group') }}
                    </h3>
                    <button type="button" wire:click="closeBatchModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="saveBatch" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Batch / Group Name') }} <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            wire:model="batchName"
                            placeholder="e.g. 2026 A/L Bio Science or Little Pandas Daycare"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                        @error('batchName') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Batch Code') }}</label>
                            <input 
                                type="text" 
                                wire:model="batchCode"
                                placeholder="e.g. AL-26-BIO"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-mono">
                            @error('batchCode') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Kind') }} <span class="text-rose-500">*</span></label>
                            <select 
                                wire:model="batchKind"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="institute_batch">{{ __('Institute Batch') }}</option>
                                <option value="nursery_group">{{ __('Nursery Group') }}</option>
                            </select>
                            @error('batchKind') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Academic Year') }}</label>
                            <select 
                                wire:model="batchAcademicYearId"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="">{{ __('— None / Standalone —') }}</option>
                                @foreach ($allYears as $y)
                                    <option value="{{ $y->id }}">{{ $y->name }}</option>
                                @endforeach
                            </select>
                            @error('batchAcademicYearId') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Status') }} <span class="text-rose-500">*</span></label>
                            <select 
                                wire:model="batchStatus"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="archived">{{ __('Archived') }}</option>
                            </select>
                            @error('batchStatus') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Description') }}</label>
                        <textarea 
                            wire:model="batchDescription"
                            rows="2"
                            placeholder="{{ __('Optional description...') }}"
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs"></textarea>
                        @error('batchDescription') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeBatchModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all btn-interactive">
                            {{ $editingBatchId ? __('Update Batch/Group') : __('Save Batch/Group') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
