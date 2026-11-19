<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
    <!-- Header with Breadcrumb & Action Button -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">🧸</span>
                <span>{{ __('Nursery Portal') }}</span>
                <span>•</span>
                <span class="text-slate-500 dark:text-slate-400">{{ __('Early Learning Activities') }}</span>
            </div>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ __('Nursery Activities') }}
            </h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                {{ __('Create, organize, and publish engaging step-by-step activities for little learners.') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                wire:click="openCreateModal"
                type="button"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold text-sm shadow-md shadow-amber-500/25 hover:shadow-lg hover:shadow-amber-500/35 active:scale-95 transition-all duration-150"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>{{ __('New Activity') }}</span>
            </button>
        </div>
    </div>

    <!-- Feedback Flash Messages -->
    @if (session()->has('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 p-4 flex items-center gap-3 text-emerald-800 dark:text-emerald-200 text-sm shadow-sm">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 p-4 flex items-center gap-3 text-rose-800 dark:text-rose-200 text-sm shadow-sm">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Total Activities') }}</span>
                <span class="p-2 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-xl text-base">📚</span>
            </div>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('All nursery activities') }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">{{ __('Published') }}</span>
                <span class="p-2 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl text-base">🚀</span>
            </div>
            <p class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['published'] }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Live on parent feed') }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">{{ __('Drafts') }}</span>
                <span class="p-2 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-xl text-base">📝</span>
            </div>
            <p class="mt-2 text-2xl font-black text-amber-600 dark:text-amber-400">{{ $stats['draft'] }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Under preparation') }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Closed') }}</span>
                <span class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl text-base">🔒</span>
            </div>
            <p class="mt-2 text-2xl font-black text-slate-700 dark:text-slate-300">{{ $stats['closed'] }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Past activities') }}</p>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3 sm:space-y-0 sm:flex sm:items-center sm:gap-4">
        <!-- Search Input -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="{{ __('Search activities by title, description or instructions...') }}"
                class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
            />
        </div>

        <!-- Class Filter -->
        <div class="sm:w-64">
            <select
                wire:model.live="classFilter"
                class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
            >
                <option value="all">{{ __('All Nursery Groups / Classes') }}</option>
                @foreach ($classes as $classItem)
                    <option value="{{ $classItem->id }}">{{ $classItem->title }}</option>
                @endforeach
            </select>
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl overflow-x-auto text-xs font-semibold">
            <button
                wire:click="$set('statusFilter', 'all')"
                type="button"
                class="px-3 py-1.5 rounded-lg transition {{ $statusFilter === 'all' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}"
            >
                {{ __('All') }}
            </button>
            <button
                wire:click="$set('statusFilter', 'published')"
                type="button"
                class="px-3 py-1.5 rounded-lg transition {{ $statusFilter === 'published' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}"
            >
                {{ __('Published') }}
            </button>
            <button
                wire:click="$set('statusFilter', 'draft')"
                type="button"
                class="px-3 py-1.5 rounded-lg transition {{ $statusFilter === 'draft' ? 'bg-white dark:bg-slate-700 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}"
            >
                {{ __('Drafts') }}
            </button>
            <button
                wire:click="$set('statusFilter', 'closed')"
                type="button"
                class="px-3 py-1.5 rounded-lg transition {{ $statusFilter === 'closed' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}"
            >
                {{ __('Closed') }}
            </button>
        </div>
    </div>

    <!-- Activities List / Grid -->
    @if ($activities->isEmpty())
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-12 text-center shadow-sm">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-3xl">
                🎨
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('No activities found') }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                {{ __('Get started by creating a new fun activity with instructions, age targets, and deadlines for your nursery kids.') }}
            </p>
            <div class="mt-6">
                <button
                    wire:click="openCreateModal"
                    type="button"
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium text-sm shadow-sm transition"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>{{ __('Create First Activity') }}</span>
                </button>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($activities as $act)
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 group">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="space-y-2 flex-1">
                            <!-- Badges Row -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $act->status->badgeClasses() }}">
                                    {{ $act->status->label() }}
                                </span>

                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">
                                    👶 {{ $act->age_group }}
                                </span>

                                @if ($act->schoolClass)
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50">
                                        🏫 {{ $act->schoolClass->title }}
                                    </span>
                                @endif

                                @if ($act->publish_date)
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        📅 {{ __('Publish:') }} {{ $act->publish_date->format('M d, Y') }}
                                    </span>
                                @endif

                                @if ($act->deadline)
                                    <span class="text-xs {{ $act->isPastDeadline() ? 'text-rose-600 font-semibold' : 'text-slate-500 dark:text-slate-400' }}">
                                        ⏳ {{ __('Deadline:') }} {{ $act->deadline->format('M d, Y H:i') }}
                                        @if ($act->isPastDeadline())
                                            <span class="ml-1 text-[10px] uppercase font-bold text-rose-500">({{ __('Expired') }})</span>
                                        @endif
                                    </span>
                                @endif
                            </div>

                            <!-- Title & Description -->
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                    {{ $act->title }}
                                </h3>
                                @if ($act->description)
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300 line-clamp-2">
                                        {{ $act->description }}
                                    </p>
                                @endif
                            </div>

                            <!-- Instructions Preview -->
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl text-xs text-slate-600 dark:text-slate-300 border border-slate-100 dark:border-slate-800">
                                <span class="font-bold text-slate-700 dark:text-slate-200 block mb-1">📋 {{ __('Instructions:') }}</span>
                                <p class="line-clamp-2 whitespace-pre-line">{{ $act->instructions }}</p>
                            </div>

                            <!-- Meta Info & Badges -->
                            <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400 pt-1">
                                <span>✍️ {{ __('Created by') }} {{ $act->creator?->name ?? __('Staff') }}</span>
                                <span>•</span>
                                <span>{{ $act->created_at?->diffForHumans() }}</span>
                                @if ($act->videos->isNotEmpty())
                                    <span>•</span>
                                    <span class="text-indigo-600 dark:text-indigo-400 font-medium">🎥 {{ $act->videos->count() }} {{ __('video(s)') }}</span>
                                @endif
                                @if ($act->materials->isNotEmpty())
                                    <span>•</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">📎 {{ $act->materials->count() }} {{ __('material(s)') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions Row -->
                        <div class="flex items-center md:flex-col justify-end gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100 dark:border-slate-800">
                            <!-- Quick Status Transitions -->
                            @if ($act->isDraft())
                                <button
                                    wire:click="publishActivity({{ $act->id }})"
                                    type="button"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white shadow-sm transition active:scale-95"
                                    title="{{ __('Publish to make visible to parents') }}"
                                >
                                    🚀 {{ __('Publish') }}
                                </button>
                            @elseif ($act->isPublished())
                                <button
                                    wire:click="closeActivity({{ $act->id }})"
                                    type="button"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 transition active:scale-95"
                                    title="{{ __('Close activity to stop regular submissions') }}"
                                >
                                    🔒 {{ __('Close') }}
                                </button>
                            @elseif ($act->isClosed())
                                <button
                                    wire:click="reopenActivity({{ $act->id }})"
                                    type="button"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition active:scale-95"
                                    title="{{ __('Reopen activity') }}"
                                >
                                    🔄 {{ __('Reopen') }}
                                </button>
                            @endif

                            <!-- Edit Button -->
                            <button
                                wire:click="openEditModal({{ $act->id }})"
                                type="button"
                                class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition"
                            >
                                ✏️ {{ __('Edit') }}
                            </button>

                            <!-- Delete Button -->
                            <button
                                wire:click="confirmDelete({{ $act->id }})"
                                type="button"
                                class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-rose-200 dark:border-rose-900/50 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 transition"
                            >
                                🗑️ {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="pt-2">
                {{ $activities->links() }}
            </div>
        </div>
    @endif

    <!-- Create / Edit Activity Modal -->
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-6 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            {{ $editingActivityId ? __('Edit Activity') : __('Create New Nursery Activity') }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('Step-by-step guidance for early childhood learning and play.') }}
                        </p>
                    </div>
                    <button
                        wire:click="$set('showFormModal', false)"
                        type="button"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveActivity" class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('Activity Title') }} <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model="title"
                            type="text"
                            placeholder="{{ __('e.g. Finger Painting Rainbows & Shapes') }}"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                        />
                        @error('title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Target Class & Age Group (2 Columns) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Target Group / Class') }} <span class="text-rose-500">*</span>
                            </label>
                            <select
                                wire:model="classId"
                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                            >
                                <option value="">{{ __('Select class or nursery group...') }}</option>
                                @foreach ($classes as $classOpt)
                                    <option value="{{ $classOpt->id }}">{{ $classOpt->title }} ({{ $classOpt->kind->label() }})</option>
                                @endforeach
                            </select>
                            @error('classId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Target Age Group') }} <span class="text-rose-500">*</span>
                            </label>
                            <div class="space-y-1.5">
                                <select
                                    wire:model="ageGroup"
                                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                                >
                                    @foreach ($ageGroupPresets as $preset)
                                        <option value="{{ $preset }}">{{ $preset }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('ageGroup') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('Brief Overview / Description') }}
                        </label>
                        <input
                            wire:model="description"
                            type="text"
                            placeholder="{{ __('Short summary for parents overview (optional)') }}"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                        />
                        @error('description') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Step-by-Step Instructions -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('Step-by-Step Instructions') }} <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            wire:model="instructions"
                            rows="5"
                            placeholder="{{ __("1. Prepare non-toxic finger paints and drawing paper.\n2. Guide your child to dip fingertips into the yellow paint.\n3. Make sun shapes and enjoy tactile sensory play!") }}"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-mono text-xs sm:text-sm"
                        ></textarea>
                        @error('instructions') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Publish Date & Deadline (2 Columns) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Publish Date') }} <span class="text-rose-500">*</span>
                            </label>
                            <input
                                wire:model="publishDate"
                                type="date"
                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                            />
                            @error('publishDate') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Submission Deadline (Optional)') }}
                            </label>
                            <input
                                wire:model="deadline"
                                type="datetime-local"
                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                            />
                            @error('deadline') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Status & Allow Late Checkbox -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Initial Status') }} <span class="text-rose-500">*</span>
                            </label>
                            <select
                                wire:model="status"
                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                            >
                                <option value="draft">{{ __('Draft (Hidden from parents)') }}</option>
                                <option value="published">{{ __('Published (Active on feed)') }}</option>
                                <option value="closed">{{ __('Closed') }}</option>
                            </select>
                            @error('status') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input
                                    wire:model="allowLate"
                                    type="checkbox"
                                    class="rounded border-slate-300 text-amber-600 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50"
                                />
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    {{ __('Allow Late Submissions') }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                        <button
                            wire:click="$set('showFormModal', false)"
                            type="button"
                            class="px-4 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2.5 text-sm font-bold rounded-xl bg-amber-500 hover:bg-amber-600 text-white shadow-md shadow-amber-500/20 active:scale-95 transition"
                        >
                            {{ $editingActivityId ? __('Save Changes') : __('Create Activity') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl mx-auto">
                    ⚠️
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Delete Activity?') }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ __('Are you sure you want to delete this activity? It will be safely archived (soft deleted) from the system.') }}
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-4">
                    <button
                        wire:click="$set('showDeleteModal', false)"
                        type="button"
                        class="px-4 py-2 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        wire:click="deleteActivity"
                        type="button"
                        class="px-4 py-2 text-sm font-bold rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20 transition"
                    >
                        {{ __('Yes, Delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
