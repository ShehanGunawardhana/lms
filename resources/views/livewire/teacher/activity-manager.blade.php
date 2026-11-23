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
                                        ⏳ {{ __('Deadline:') }} {{ $act->effectiveDeadlineForStudent()?->format('M d, Y H:i') }}
                                        @if ($act->isExtendedForStudent())
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] uppercase font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                ✨ {{ __('Extended') }}
                                            </span>
                                        @elseif ($act->isPastDeadline())
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

                            <!-- Attached Videos Gallery (§6.1, T-4.2) -->
                            @if ($act->videos->isNotEmpty())
                                <div class="pt-2">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">🎥 {{ __('Video Lessons (:count)', ['count' => $act->videos->count()]) }}</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium">youtube-nocookie.com</span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                        @foreach ($act->videos as $vid)
                                            <div
                                                wire:click="watchVideo('{{ $vid->video_id }}', '{{ addslashes($vid->title ?? __('Video Lesson')) }}')"
                                                class="group/vid relative cursor-pointer overflow-hidden rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-900 shadow-sm hover:shadow-md transition-all duration-150 hover:-translate-y-0.5"
                                                title="{{ __('Click to watch video') }}"
                                            >
                                                <div class="aspect-video w-full overflow-hidden bg-slate-950 relative">
                                                    <img
                                                        src="{{ $vid->thumbnailUrl() }}"
                                                        alt="{{ $vid->title }}"
                                                        class="w-full h-full object-cover group-hover/vid:scale-105 transition-transform duration-300"
                                                        loading="lazy"
                                                    />
                                                    <div class="absolute inset-0 bg-black/30 group-hover/vid:bg-black/10 transition-colors flex items-center justify-center">
                                                        <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center shadow-lg group-hover/vid:scale-110 transition-transform">
                                                            <svg class="w-4 h-4 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M8 5v14l11-7z"/>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="p-2 bg-white dark:bg-slate-900">
                                                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate group-hover/vid:text-amber-600 transition-colors">
                                                        {{ $vid->title ?: __('Video Lesson') }}
                                                    </p>
                                                    <span class="text-[10px] text-slate-400 font-mono">{{ $vid->video_id }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Attached Learning Materials (§6.1, T-4.3) -->
                            @if ($act->materials->isNotEmpty())
                                <div class="pt-2">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">📎 {{ __('Learning Materials (:count)', ['count' => $act->materials->count()]) }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                        @foreach ($act->materials as $mat)
                                            <div class="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 flex items-center justify-between gap-3 group/mat hover:border-amber-400 dark:hover:border-amber-600 transition">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span class="text-xl shrink-0">{{ $mat->icon() }}</span>
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" title="{{ $mat->title }}">
                                                            {{ $mat->title }}
                                                        </p>
                                                        <p class="text-[10px] text-slate-400 truncate">
                                                            @if ($mat->isFile())
                                                                {{ $mat->fileAsset?->original_name }} • {{ $mat->formattedSize() }}
                                                            @else
                                                                {{ parse_url($mat->url ?? '', PHP_URL_HOST) ?: $mat->url }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="shrink-0">
                                                    @if ($mat->isFile() && $mat->fileAsset)
                                                        <a
                                                            href="{{ $mat->downloadUrl() }}"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 transition"
                                                            title="{{ __('Download File') }}"
                                                            target="_blank"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                            <span>{{ __('Get') }}</span>
                                                        </a>
                                                    @elseif ($mat->isLink() && $mat->url)
                                                        <a
                                                            href="{{ $mat->url }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/80 text-emerald-700 dark:text-emerald-300 transition"
                                                            title="{{ __('Open Link') }}"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                            <span>{{ __('Visit') }}</span>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

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
                            <!-- Review Submissions Button (§6.4, T-4.7) -->
                            <a
                                href="{{ route('teacher.activities.reviews', $act) }}"
                                class="px-3 py-1.5 text-xs font-bold rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-xs transition active:scale-95 flex items-center gap-1.5"
                                title="{{ __('Review Student Submissions (§6.4)') }}"
                            >
                                <span>📝</span>
                                <span>{{ __('Reviews') }}</span>
                                @php
                                    $subCount = $act->submissions()->count();
                                @endphp
                                @if ($subCount > 0)
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 text-white font-mono font-bold">{{ $subCount }}</span>
                                @endif
                            </a>

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

                            <!-- Extend Deadline Button (§6.2, T-4.5) -->
                            <button
                                wire:click="openExtensionModal({{ $act->id }})"
                                type="button"
                                class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 transition active:scale-95 flex items-center gap-1"
                                title="{{ __('Extend deadline for everyone or individual child (§6.2)') }}"
                            >
                                <span>⏰</span>
                                <span>{{ __('Extend') }}</span>
                            </button>

                            @if ($act->extensions->isNotEmpty())
                                <button
                                    wire:click="openExtensionLogModal({{ $act->id }})"
                                    type="button"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800 transition flex items-center gap-1"
                                    title="{{ __('View extension history and audit log') }}"
                                >
                                    <span>✨</span>
                                    <span>{{ __(':count ext', ['count' => $act->extensions->count()]) }}</span>
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
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full max-h-[92vh] sm:max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 my-auto overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Sticky Header -->
                <div class="px-6 py-4 sm:px-8 sm:py-5 border-b border-slate-100 dark:border-slate-800 shrink-0 flex items-center justify-between bg-white dark:bg-slate-900 z-10">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
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

                <form wire:submit="saveActivity" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <!-- Modal Scrollable Body -->
                    <div class="p-6 sm:p-8 overflow-y-auto flex-1 space-y-4">
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

                    <!-- Video Lessons (YouTube) (§6.1, T-4.2) -->
                    <div class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    🎥 {{ __('Video Lessons (YouTube)') }}
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ __('Paste any YouTube link (watch, youtu.be, embed, shorts). We fetch preview & embed securely via youtube-nocookie.com.') }}
                                </p>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                {{ count($videos) }} {{ __('added') }}
                            </span>
                        </div>

                        <!-- Add Video Input with Live Preview Trigger -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <div class="relative flex-1">
                                <input
                                    wire:model.live.debounce.400ms="newVideoUrl"
                                    type="text"
                                    placeholder="{{ __('https://www.youtube.com/watch?v=... or youtu.be/...') }}"
                                    class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-mono text-xs"
                                />
                            </div>
                            <button
                                wire:click="addVideo"
                                type="button"
                                class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white shadow-sm transition active:scale-95 shrink-0 inline-flex items-center justify-center gap-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>{{ __('Add Video') }}</span>
                            </button>
                        </div>

                        <!-- Video Error Feedback -->
                        @if ($videoError)
                            <p class="text-xs text-rose-500 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $videoError }}
                            </p>
                        @endif

                        <!-- Live oEmbed Preview Card before adding -->
                        @if ($pendingVideoPreview && $pendingVideoPreview['is_valid'])
                            <div class="p-3 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 flex items-center justify-between gap-3 animate-in fade-in duration-150">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img
                                        src="{{ $pendingVideoPreview['thumbnail_url'] }}"
                                        alt="{{ $pendingVideoPreview['title'] }}"
                                        class="w-20 h-12 object-cover rounded-lg shrink-0 shadow-xs border border-amber-200 dark:border-amber-900"
                                    />
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                            {{ $pendingVideoPreview['title'] }}
                                        </p>
                                        <p class="text-[10px] text-amber-700 dark:text-amber-400 font-mono mt-0.5">
                                            ID: {{ $pendingVideoPreview['video_id'] }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    wire:click="addVideo"
                                    type="button"
                                    class="px-3 py-1.5 text-xs font-bold rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs shrink-0"
                                >
                                    {{ __('Confirm & Add') }}
                                </button>
                            </div>
                        @endif

                        <!-- Current Attached Videos List -->
                        @if (! empty($videos))
                            <div class="space-y-2 pt-1">
                                @foreach ($videos as $idx => $vItem)
                                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="w-5 h-5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-black flex items-center justify-center shrink-0">
                                                {{ $idx + 1 }}
                                            </span>
                                            <img
                                                src="{{ $vItem['thumbnail_url'] }}"
                                                alt="{{ $vItem['title'] }}"
                                                class="w-14 h-9 object-cover rounded shrink-0 shadow-xs"
                                            />
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                                    {{ $vItem['title'] }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 font-mono truncate">
                                                    {{ $vItem['youtube_url'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <!-- Up Button -->
                                            <button
                                                wire:click="moveVideoUp({{ $idx }})"
                                                type="button"
                                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 disabled:opacity-30"
                                                @if ($idx === 0) disabled @endif
                                                title="{{ __('Move up') }}"
                                            >
                                                ▲
                                            </button>
                                            <!-- Down Button -->
                                            <button
                                                wire:click="moveVideoDown({{ $idx }})"
                                                type="button"
                                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 disabled:opacity-30"
                                                @if ($idx === count($videos) - 1) disabled @endif
                                                title="{{ __('Move down') }}"
                                            >
                                                ▼
                                            </button>
                                            <!-- Preview Watch Button -->
                                            <button
                                                wire:click="watchVideo('{{ $vItem['video_id'] }}', '{{ addslashes($vItem['title']) }}')"
                                                type="button"
                                                class="px-2 py-1 text-[10px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300"
                                                title="{{ __('Watch preview') }}"
                                            >
                                                ▶ {{ __('Play') }}
                                            </button>
                                            <!-- Remove Button -->
                                            <button
                                                wire:click="removeVideo({{ $idx }})"
                                                type="button"
                                                class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition"
                                                title="{{ __('Remove video') }}"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Additional Learning Materials (Files & Links) (§6.1, T-4.3) -->
                    <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    📎 {{ __('Additional Learning Materials (Files & Links)') }}
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ __('Attach activity sheets, PDFs, images, or web links for parents and children to explore.') }}
                                </p>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300">
                                {{ count($materials) }} {{ __('attached') }}
                            </span>
                        </div>

                        <!-- Segmented Switcher: Upload File vs Web Link -->
                        <div class="flex items-center gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs font-semibold w-fit">
                            <button
                                wire:click="$set('materialTab', 'file')"
                                type="button"
                                class="px-3 py-1.5 rounded-lg transition {{ $materialTab === 'file' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}"
                            >
                                📄 {{ __('Upload File') }}
                            </button>
                            <button
                                wire:click="$set('materialTab', 'link')"
                                type="button"
                                class="px-3 py-1.5 rounded-lg transition {{ $materialTab === 'link' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}"
                            >
                                🔗 {{ __('Web Link') }}
                            </button>
                        </div>

                        <!-- Tab 1: File Upload -->
                        @if ($materialTab === 'file')
                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Material Title (Optional)') }}
                                        </label>
                                        <input
                                            wire:model="materialTitle"
                                            type="text"
                                            placeholder="{{ __('e.g. Rainbow Coloring Worksheet') }}"
                                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Choose File (PDF, Image, Doc, Zip up to 50MB)') }}
                                        </label>
                                        <input
                                            wire:model="materialFile"
                                            type="file"
                                            class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 dark:file:bg-slate-700 file:text-slate-800 dark:file:text-slate-200 hover:file:bg-slate-300"
                                        />
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-[11px] text-slate-400" wire:loading.remove wire:target="materialFile">
                                        {{ __('Files are validated with magic-byte checks and stored privately.') }}
                                    </span>
                                    <span class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold" wire:loading wire:target="materialFile">
                                        ⏳ {{ __('Uploading file...') }}
                                    </span>

                                    <button
                                        wire:click="uploadFileMaterial"
                                        wire:loading.attr="disabled"
                                        type="button"
                                        class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white shadow-xs transition active:scale-95 disabled:opacity-50"
                                    >
                                        <span wire:loading.remove wire:target="uploadFileMaterial">📎 {{ __('Attach File') }}</span>
                                        <span wire:loading wire:target="uploadFileMaterial">{{ __('Processing...') }}</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- Tab 2: External Link -->
                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Link Title') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            wire:model="materialTitle"
                                            type="text"
                                            placeholder="{{ __('e.g. Online Animal Sounds Game') }}"
                                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Destination URL') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            wire:model="materialUrl"
                                            type="url"
                                            placeholder="{{ __('https://example.com/activity') }}"
                                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-mono"
                                        />
                                    </div>
                                </div>

                                <div class="flex items-center justify-end pt-1">
                                    <button
                                        wire:click="addLinkMaterial"
                                        type="button"
                                        class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white shadow-xs transition active:scale-95"
                                    >
                                        🔗 {{ __('Attach Web Link') }}
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Material Error Feedback -->
                        @if ($materialError)
                            <p class="text-xs text-rose-500 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $materialError }}
                            </p>
                        @endif

                        <!-- Attached Materials List -->
                        @if (! empty($materials))
                            <div class="space-y-2 pt-1">
                                @foreach ($materials as $mIdx => $mItem)
                                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="w-5 h-5 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-black flex items-center justify-center shrink-0">
                                                {{ $mIdx + 1 }}
                                            </span>
                                            <span class="text-xl shrink-0">
                                                {{ $mItem['type'] === 'link' ? '🔗' : '📄' }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                                    {{ $mItem['title'] }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 font-mono truncate">
                                                    @if ($mItem['type'] === 'file')
                                                        {{ $mItem['file_name'] }} • {{ $mItem['file_size'] }}
                                                    @else
                                                        {{ $mItem['url'] }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <!-- Up Button -->
                                            <button
                                                wire:click="moveMaterialUp({{ $mIdx }})"
                                                type="button"
                                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 disabled:opacity-30"
                                                @if ($mIdx === 0) disabled @endif
                                                title="{{ __('Move up') }}"
                                            >
                                                ▲
                                            </button>
                                            <!-- Down Button -->
                                            <button
                                                wire:click="moveMaterialDown({{ $mIdx }})"
                                                type="button"
                                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 disabled:opacity-30"
                                                @if ($mIdx === count($materials) - 1) disabled @endif
                                                title="{{ __('Move down') }}"
                                            >
                                                ▼
                                            </button>
                                            <!-- Remove Button -->
                                            <button
                                                wire:click="removeMaterial({{ $mIdx }})"
                                                type="button"
                                                class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition"
                                                title="{{ __('Remove material') }}"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
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
                </div>

                <!-- Modal Sticky Footer Buttons -->
                <div class="px-6 py-4 sm:px-8 sm:py-4 border-t border-slate-100 dark:border-slate-800 shrink-0 flex items-center justify-end gap-3 bg-slate-50/80 dark:bg-slate-800/50">
                    <button
                        wire:click="$set('showFormModal', false)"
                        type="button"
                        class="px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-amber-500 hover:bg-amber-600 text-white shadow-md shadow-amber-500/20 active:scale-95 transition"
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
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full my-auto max-h-[92vh] overflow-y-auto p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 animate-in fade-in zoom-in-95 duration-200">
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

    <!-- Watch Video Modal (§6.1, T-4.2) -->
    @if ($showWatchModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-3xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 my-auto overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-3.5 border-b border-slate-100 dark:border-slate-800 shrink-0 flex items-center justify-between">
                    <div class="min-w-0 pr-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-red-600 flex items-center gap-1">
                                <span>▶</span> {{ __('Video Lesson') }}
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 font-medium">
                                youtube-nocookie.com
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white truncate mt-0.5">
                            {{ $watchVideoTitle ?? __('YouTube Video Player') }}
                        </h3>
                    </div>
                    <button
                        wire:click="closeWatchModal"
                        type="button"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    <div class="rounded-2xl overflow-hidden bg-black shadow-lg">
                        <x-youtube-player :videoId="$watchVideoId" :title="$watchVideoTitle ?? 'Video Lesson'" :autoplay="true" />
                    </div>
                </div>

                <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-800 shrink-0 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-800/30">
                    <span class="flex items-center gap-1">
                        🛡️ {{ __('Embedded using privacy-enhanced domain youtube-nocookie.com (no tracking cookies).') }}
                    </span>
                    <button
                        wire:click="closeWatchModal"
                        type="button"
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition"
                    >
                        {{ __('Close Player') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Deadline Extension Modal (§6.2, T-4.5) -->
    @if ($showExtensionModal && $extendingActivity)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 my-auto overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 mb-0.5">
                            <span>⏰</span>
                            <span>{{ __('Deadline Extension (§6.2)') }}</span>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white truncate max-w-sm">
                            {{ $extendingActivity->title }}
                        </h2>
                    </div>
                    <button
                        wire:click="closeExtensionModal"
                        type="button"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 space-y-4">

                @if ($extensionError)
                    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300">
                        {{ $extensionError }}
                    </div>
                @endif

                <!-- Extension Target Toggle -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        {{ __('Extension Scope') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl">
                        <button
                            type="button"
                            wire:click="$set('extensionTarget', 'everyone')"
                            class="py-2 text-xs font-bold rounded-lg transition {{ $extensionTarget === 'everyone' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}"
                        >
                            👥 {{ __('All Students in Class') }}
                        </button>
                        <button
                            type="button"
                            wire:click="$set('extensionTarget', 'individual')"
                            class="py-2 text-xs font-bold rounded-lg transition {{ $extensionTarget === 'individual' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}"
                        >
                            👤 {{ __('Individual Child') }}
                        </button>
                    </div>
                </div>

                <!-- Individual Child Select (if individual) -->
                @if ($extensionTarget === 'individual')
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            {{ __('Select Student') }} <span class="text-rose-500">*</span>
                        </label>
                        <select
                            wire:model="extensionStudentId"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- {{ __('Choose student to extend deadline for') }} --</option>
                            @foreach ($extendingActivity->schoolClass?->students ?? [] as $st)
                                <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->student_code }})</option>
                            @endforeach
                        </select>
                        @error('extensionStudentId') <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                    </div>
                @endif

                <!-- Current Deadline Display -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-500 dark:text-slate-400">{{ __('Current Effective Deadline:') }}</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">
                        {{ $extendingActivity->effectiveDeadlineForStudent($extensionTarget === 'individual' ? $extensionStudentId : null)?->format('M j, Y • g:i A') ?? __('None') }}
                    </span>
                </div>

                <!-- New Deadline -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        {{ __('New Extended Deadline') }} <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        wire:model="newDeadline"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500"
                    />
                    @error('newDeadline') <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                </div>

                <!-- Reason for Extension -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        {{ __('Reason for Extension') }} <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        wire:model="extensionReason"
                        rows="3"
                        placeholder="{{ __('e.g., Extended by 2 days due to weather disruption or parent request. Logged in audit trail and notified to parents.') }}"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                    @error('extensionReason') <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 shrink-0 flex items-center justify-end gap-3 bg-slate-50/80 dark:bg-slate-800/50">
                <button
                    wire:click="closeExtensionModal"
                    type="button"
                    class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition"
                >
                    {{ __('Cancel') }}
                </button>
                <button
                    wire:click="saveExtension"
                    type="button"
                    class="px-5 py-2 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition active:scale-95 flex items-center gap-1.5"
                >
                    <span>⏰</span>
                    <span>{{ __('Grant Extension & Notify') }}</span>
                </button>
            </div>
        </div>
    </div>
@endif

    <!-- Extension Log History Modal (§6.2, T-4.5) -->
    @if ($showExtensionLogModal && $extensionLogActivity)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 my-auto overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-amber-600 dark:text-amber-400 mb-0.5">
                            <span>📜</span>
                            <span>{{ __('Extension Audit History (§6.2)') }}</span>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white truncate max-w-sm">
                            {{ $extensionLogActivity->title }}
                        </h2>
                    </div>
                    <button
                        wire:click="closeExtensionLogModal"
                        type="button"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 space-y-3">
                    @forelse ($extensionLogActivity->extensions as $ext)
                        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="px-2.5 py-0.5 rounded-full font-bold {{ $ext->student ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' }}">
                                    {{ $ext->student ? '👤 '.$ext->student->full_name : '👥 '.__('All Students in Class') }}
                                </span>
                                <span class="text-slate-400 text-[11px]">{{ $ext->created_at?->diffForHumans() }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ __('Previous Deadline') }}</span>
                                    <span class="font-medium text-slate-600 dark:text-slate-300 line-through">
                                        {{ $ext->original_deadline?->format('M j, Y • g:i A') ?? __('None') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-emerald-600 dark:text-emerald-400 block text-[10px] uppercase font-bold">{{ __('Extended Deadline') }}</span>
                                    <span class="font-bold text-emerald-700 dark:text-emerald-300">
                                        {{ $ext->extended_deadline->format('M j, Y • g:i A') }}
                                    </span>
                                </div>
                            </div>

                            <div class="pt-1 text-xs text-slate-700 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800">
                                <span class="font-bold text-slate-500">{{ __('Reason:') }}</span>
                                <span class="italic">"{{ $ext->reason }}"</span>
                            </div>

                            <div class="text-[10px] text-slate-400 flex items-center justify-between">
                                <span>✍️ {{ __('Granted by') }} {{ $ext->extendedBy?->name ?? __('Staff') }}</span>
                                <span>{{ $ext->created_at?->format('Y-m-d H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-400">
                            {{ __('No deadline extensions have been recorded for this activity.') }}
                        </div>
                    @endforelse
                </div>

                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 shrink-0 flex justify-end bg-slate-50/80 dark:bg-slate-800/50">
                    <button
                        wire:click="closeExtensionLogModal"
                        type="button"
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition"
                    >
                        {{ __('Close History') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
