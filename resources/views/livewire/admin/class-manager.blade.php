<div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <span>{{ __('Academics') }}</span>
                <span>/</span>
                <span class="text-brand-600">{{ __('Class Management') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                {{ __('Classes & Nursery Groups') }}
            </h1>
            <p class="text-sm text-slate-600 mt-1">
                {{ __('Manage classes, nursery groups, assign lecturers, define capacities, fees, and lecture venues.') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                type="button" 
                wire:click="openCreateModal"
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer inline-flex items-center gap-2 btn-interactive">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Create Class / Group') }}
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500">{{ __('Total Classes') }}</span>
                <p class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $metrics['total'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500">{{ __('Active Classes') }}</span>
                <p class="text-xl font-extrabold text-emerald-700 tracking-tight">{{ $metrics['active'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342"/></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500">{{ __('Institute Classes') }}</span>
                <p class="text-xl font-extrabold text-indigo-700 tracking-tight">{{ $metrics['institute'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z"/></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500">{{ __('Nursery Groups') }}</span>
                <p class="text-xl font-extrabold text-amber-700 tracking-tight">{{ $metrics['nursery'] }}</p>
            </div>
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

    <!-- Controls Bar: Search & Filters -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Kind Filter -->
            <button 
                type="button" 
                wire:click="$set('kindFilter', 'all')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $kindFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ __('All') }}
            </button>
            <button 
                type="button" 
                wire:click="$set('kindFilter', 'institute_class')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $kindFilter === 'institute_class' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ __('Institute') }}
            </button>
            <button 
                type="button" 
                wire:click="$set('kindFilter', 'nursery_group')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $kindFilter === 'nursery_group' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ __('Nursery') }}
            </button>

            <!-- Status Filter -->
            <div class="h-4 w-px bg-slate-200 mx-1 hidden sm:block"></div>
            <select 
                wire:model.live="statusFilter"
                class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white font-semibold">
                <option value="all">{{ __('All Status') }}</option>
                <option value="active">{{ __('Active Only') }}</option>
                <option value="archived">{{ __('Archived') }}</option>
            </select>

            <!-- Academic Year Filter -->
            <select 
                wire:model.live="yearFilter"
                class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white font-semibold">
                <option value="">{{ __('— Any Academic Year —') }}</option>
                @foreach ($academicYears as $y)
                    <option value="{{ $y->id }}">{{ $y->name }} {{ $y->is_current ? '★' : '' }}</option>
                @endforeach
            </select>

            <!-- Teacher Filter -->
            <select 
                wire:model.live="teacherFilter"
                class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white font-semibold">
                <option value="">{{ __('— Any Teacher —') }}</option>
                @foreach ($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full lg:w-72">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search by title, subject, room, teacher...') }}"
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
        </div>
    </div>

    <!-- Classes Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">{{ __('Class / Program') }}</th>
                        <th class="px-4 py-3.5">{{ __('Kind') }}</th>
                        <th class="px-4 py-3.5">{{ __('Lecturer / Teacher') }}</th>
                        <th class="px-4 py-3.5">{{ __('Cycle & Cohort') }}</th>
                        <th class="px-4 py-3.5">{{ __('Capacity / Seats') }}</th>
                        <th class="px-4 py-3.5">{{ __('Monthly Fee') }}</th>
                        <th class="px-4 py-3.5">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($classes as $class)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $class->title }}</div>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                    @if ($class->subject)
                                        <span class="font-semibold text-slate-700">{{ $class->subject }}</span>
                                    @endif
                                    @if ($class->room)
                                        <span>• {{ $class->room }}</span>
                                    @endif
                                    @if ($class->online_url)
                                        <span>• <a href="{{ $class->online_url }}" target="_blank" class="text-brand-600 hover:underline">Online</a></span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @if (($class->kind instanceof \App\Enums\ClassKind ? $class->kind->value : (string) $class->kind) === 'nursery_group')
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-[10px] font-bold text-amber-700">
                                        {{ __('Nursery Group') }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-brand-50 border border-brand-200 text-[10px] font-bold text-brand-700">
                                        {{ __('Institute Class') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($class->teacher)
                                    <div class="font-bold text-slate-800">{{ $class->teacher->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $class->teacher->email }}</div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">{{ __('Unassigned') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-medium text-slate-800">{{ $class->academicYear?->name ?? '—' }}</div>
                                @if ($class->batch)
                                    <div class="text-[11px] text-slate-500 font-semibold">{{ $class->batch->name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $class->enrollments_count }} / {{ $class->capacity }}</span>
                                    @if ($class->capacity > 0)
                                        <span class="text-[10px] font-semibold text-slate-500">
                                            ({{ round(($class->enrollments_count / $class->capacity) * 100) }}%)
                                        </span>
                                    @endif
                                </div>
                                <div class="w-24 bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                    @php
                                        $percent = $class->capacity > 0 ? min(100, round(($class->enrollments_count / $class->capacity) * 100)) : 0;
                                    @endphp
                                    <div class="h-full rounded-full {{ $percent >= 90 ? 'bg-rose-500' : ($percent >= 60 ? 'bg-amber-500' : 'bg-brand-600') }}" style="width: {{ $percent }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-mono font-bold text-slate-900">
                                @if ($class->fee_amount > 0)
                                    LKR {{ number_format((float) $class->fee_amount, 2) }}
                                @else
                                    <span class="text-emerald-600 font-sans font-bold text-[11px]">{{ __('Free / Included') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($class->status === 'active')
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-700">{{ __('Active') }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-600">{{ __('Archived') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        type="button" 
                                        wire:click="toggleStatus({{ $class->id }})"
                                        class="px-2 py-1 rounded-lg text-[11px] font-bold transition-colors {{ $class->status === 'active' ? 'text-slate-600 hover:bg-slate-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                                        {{ $class->status === 'active' ? __('Archive') : __('Activate') }}
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="openEditModal({{ $class->id }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="deleteClass({{ $class->id }})"
                                        wire:confirm="{{ __('Delete this class? This action cannot be undone.') }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                                    </div>
                                    <p class="font-bold text-slate-800 text-sm">{{ __('No classes found') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('Try adjusting your filters or create a new class or nursery group.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($classes->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $classes->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: CLASS CREATE / EDIT -->
    <!-- ============================================================== -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">
                            {{ $editingClassId ? __('Edit Class / Group') : __('Create New Class or Nursery Group') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('Define class parameters, lecturer, capacity, monthly fee and venue.') }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="saveClass" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Class Title') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="text" 
                                wire:model="title"
                                placeholder="e.g. Advanced Level Physics 2026 or Sunflowers Playgroup"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                            @error('title') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Subject / Discipline') }}</label>
                            <input 
                                type="text" 
                                wire:model="subject"
                                placeholder="e.g. Physics, Calculus, Early Childhood"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('subject') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Kind / Type') }} <span class="text-rose-500">*</span></label>
                            <select 
                                wire:model="kind"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="institute_class">{{ __('Institute Class / Program') }}</option>
                                <option value="nursery_group">{{ __('Nursery Group') }}</option>
                            </select>
                            @error('kind') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Academic Year') }}</label>
                            <select 
                                wire:model="academic_year_id"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="">{{ __('— None / Standalone —') }}</option>
                                @foreach ($academicYears as $y)
                                    <option value="{{ $y->id }}">{{ $y->name }} {{ $y->is_current ? '★' : '' }}</option>
                                @endforeach
                            </select>
                            @error('academic_year_id') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Batch / Group') }}</label>
                            <select 
                                wire:model="batch_id"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="">{{ __('— None / Unlinked —') }}</option>
                                @foreach ($batches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                                @endforeach
                            </select>
                            @error('batch_id') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Assigned Lecturer / Teacher') }}</label>
                            <select 
                                wire:model="teacher_id"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="">{{ __('— Select Lecturer —') }}</option>
                                @foreach ($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Monthly Fee (LKR)') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="number" 
                                step="0.01"
                                min="0"
                                wire:model="fee_amount"
                                placeholder="0.00"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-mono font-bold">
                            @error('fee_amount') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Capacity (Seats)') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="number" 
                                min="1"
                                max="1000"
                                wire:model="capacity"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-bold">
                            @error('capacity') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Physical Room / Hall') }}</label>
                            <input 
                                type="text" 
                                wire:model="room"
                                placeholder="e.g. Hall 401, Lab 2, Sunshine Room"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                            @error('room') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Online Lecture URL') }}</label>
                            <input 
                                type="url" 
                                wire:model="online_url"
                                placeholder="https://zoom.us/j/... or https://meet.google.com/..."
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-mono">
                            @error('online_url') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Description / Syllabus Overview') }}</label>
                            <textarea 
                                wire:model="description"
                                rows="3"
                                placeholder="{{ __('Brief description of curriculum, target audience, prerequisites...') }}"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs"></textarea>
                            @error('description') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Class Status') }} <span class="text-rose-500">*</span></label>
                            <select 
                                wire:model="status"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="archived">{{ __('Archived') }}</option>
                            </select>
                            @error('status') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all btn-interactive">
                            {{ $editingClassId ? __('Update Class') : __('Save Class') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
