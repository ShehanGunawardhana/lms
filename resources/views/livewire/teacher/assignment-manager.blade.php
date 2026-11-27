<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Assignments & Coursework Manager') }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Manage class coursework, configure verified file upload types, deadlines, marks, and attachments (§8, §10)') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                type="button" 
                wire:click="openCreateModal"
                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-xs transition-all cursor-pointer inline-flex items-center gap-1.5 btn-interactive">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('New Assignment') }}
            </button>
            <a href="{{ route('teacher.dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Teacher Dashboard') }}
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between animate-fade-in shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Coursework') }}</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">{{ __('Active & Published') }}</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $metrics['published'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">{{ __('Drafts') }}</p>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $metrics['draft'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Closed / Past') }}</p>
            <p class="text-2xl font-black text-slate-700 mt-1">{{ $metrics['closed'] }}</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <!-- Status Filter -->
            <select 
                wire:model.live="statusFilter"
                class="px-3 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 font-medium shadow-xs">
                <option value="all">{{ __('All Statuses') }}</option>
                <option value="published">{{ __('Published') }}</option>
                <option value="draft">{{ __('Draft') }}</option>
                <option value="closed">{{ __('Closed') }}</option>
            </select>

            <!-- Class Filter -->
            <select 
                wire:model.live="classFilter"
                class="px-3 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 font-medium shadow-xs">
                <option value="all">{{ __('All Classes & Groups') }}</option>
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}">{{ $c->title }}</option>
                @endforeach
            </select>
        </div>

        <!-- Search Input -->
        <div class="w-full sm:w-72">
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="{{ __('Search assignments...') }}"
                    class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[11px]">
                        <th class="py-3 px-4">{{ __('Assignment / Class') }}</th>
                        <th class="py-3 px-4">{{ __('Allowed Types & Max Size') }}</th>
                        <th class="py-3 px-4">{{ __('Deadline & Late Policy') }}</th>
                        <th class="py-3 px-4">{{ __('Marks / Resubmissions') }}</th>
                        <th class="py-3 px-4">{{ __('Status') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($assignments as $assignment)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Assignment / Class -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $assignment->title }}</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="px-2 py-0.5 rounded-md bg-brand-50 text-brand-800 font-semibold text-[10px]">
                                        {{ $assignment->schoolClass?->title ?? __('No class') }}
                                    </span>
                                    @if ($assignment->attachments->isNotEmpty())
                                        <span class="text-[10px] text-slate-400 flex items-center gap-0.5">
                                            📎 {{ $assignment->attachments->count() }} {{ __('attachments') }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Allowed Types & Max Size -->
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach ($assignment->allowed_file_types as $ext)
                                        <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 font-mono text-[10px] uppercase font-bold">
                                            {{ $ext }}
                                        </span>
                                    @endforeach
                                </div>
                                <span class="text-[11px] text-slate-400 mt-1 block">
                                    {{ __('Max:') }} {{ $assignment->max_file_size_mb }} MB
                                </span>
                            </td>

                            <!-- Deadline & Late Policy -->
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">
                                    {{ format_portal_datetime($assignment->deadline, 'M d, Y h:i A') }}
                                </div>
                                <div>
                                    @if ($assignment->allow_late)
                                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded inline-block mt-0.5">
                                            {{ __('Late Submissions Allowed') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded inline-block mt-0.5">
                                            {{ __('Hard Deadline') }}
                                        </span>
                                    @endif
                                </div>
                                @if ($assignment->extensions->isNotEmpty())
                                    <div class="mt-1">
                                        <button 
                                            type="button" 
                                            wire:click="openExtensionLogModal({{ $assignment->id }})"
                                            class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 px-1.5 py-0.5 rounded-md cursor-pointer transition-colors"
                                            title="{{ __('View deadline extension history') }}">
                                            <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            <span>{{ trans_choice('{1} :count extension|[2,*] :count extensions', $assignment->extensions->count(), ['count' => $assignment->extensions->count()]) }}</span>
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <!-- Marks / Resubmissions -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">
                                    {{ $assignment->total_marks }} {{ __('marks') }}
                                    @if ($assignment->pass_marks)
                                        <span class="text-[11px] text-slate-400 font-normal">({{ __('Pass:') }} {{ $assignment->pass_marks }})</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-500">
                                    {{ $assignment->resubmission_limit > 0 ? __(':limit attempts', ['limit' => $assignment->resubmission_limit]) : __('Unlimited attempts') }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $assignment->status->badgeClasses() }}">
                                    {{ $assignment->status->label() }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                @if ($assignment->isDraft())
                                    <button 
                                        type="button" 
                                        wire:click="publishAssignment({{ $assignment->id }})"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition-colors cursor-pointer">
                                        {{ __('Publish') }}
                                    </button>
                                @elseif ($assignment->isPublished())
                                    <button 
                                        type="button" 
                                        wire:click="closeAssignment({{ $assignment->id }})"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">
                                        {{ __('Close') }}
                                    </button>
                                @elseif ($assignment->isClosed())
                                    <button 
                                        type="button" 
                                        wire:click="reopenAssignment({{ $assignment->id }})"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-800 bg-blue-50 hover:bg-blue-100 transition-colors cursor-pointer">
                                        {{ __('Reopen') }}
                                    </button>
                                @endif

                                <button 
                                    type="button" 
                                    wire:click="openEditModal({{ $assignment->id }})"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">
                                    {{ __('Edit') }}
                                </button>

                                @if (! $assignment->isDraft())
                                    <a 
                                        href="{{ route('teacher.assignments.reviews', $assignment->id) }}" 
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
                                        {{ __('Reviews') }}
                                    </a>
                                @endif

                                <button 
                                    type="button" 
                                    wire:click="openExtensionModal({{ $assignment->id }})"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 transition-colors cursor-pointer inline-flex items-center gap-1">
                                    <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __('Extend') }}
                                </button>

                                <button 
                                    type="button" 
                                    wire:click="deleteAssignment({{ $assignment->id }})"
                                    wire:confirm="{{ __('Are you sure you want to delete this assignment?') }}"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition-colors cursor-pointer">
                                    {{ __('Delete') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                {{ __('No assignments found matching criteria.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $assignments->links() }}
        </div>
    </div>

    <!-- Create / Edit Assignment Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-3xl w-full my-auto max-h-[92vh] flex flex-col overflow-hidden animate-fade-in" @click.away="$wire.closeModal()">
                <!-- Modal Header -->
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 shrink-0 bg-slate-50/70">
                    <h3 class="text-base font-black text-slate-900">
                        {{ $editingAssignmentId ? __('Edit Assignment Details') : __('Create New Assignment') }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="saveAssignment" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <div class="p-6 overflow-y-auto flex-1 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Assignment Title') }} *</label>
                                <input type="text" wire:model="title" required placeholder="{{ __('e.g. Essay 1: Modern History Analysis') }}" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium" />
                                @error('title') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Target Class') }} *</label>
                                <select wire:model="classId" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium">
                                    <option value="">{{ __('Select Class...') }}</option>
                                    @foreach ($classes as $c)
                                        <option value="{{ $c->id }}">{{ $c->title }}</option>
                                    @endforeach
                                </select>
                                @error('classId') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Short Overview / Description') }}</label>
                            <textarea wire:model="description" rows="2" placeholder="{{ __('Brief summary of the coursework topic...') }}" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium"></textarea>
                            @error('description') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Detailed Instructions & Submission Guidelines') }}</label>
                            <textarea wire:model="instructions" rows="4" placeholder="{{ __('Provide detailed questions, rubric criteria, formatting requirements...') }}" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium"></textarea>
                            @error('instructions') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                        </div>

                        <!-- Marks & Submission Rules -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Total Marks') }} *</label>
                                <input type="number" wire:model="totalMarks" min="1" max="10000" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium" />
                                @error('totalMarks') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Pass Marks') }}</label>
                                <input type="number" wire:model="passMarks" min="0" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium" />
                                @error('passMarks') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Resubmission Limit') }} *</label>
                                <input type="number" wire:model="resubmissionLimit" min="0" max="50" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium" title="0 for unlimited" />
                                <span class="text-[10px] text-slate-400">0 = {{ __('unlimited') }}</span>
                                @error('resubmissionLimit') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Max File Size (MB)') }} *</label>
                                <input type="number" wire:model="maxFileSizeMb" min="1" max="50" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium" />
                                <span class="text-[10px] text-slate-400">{{ __('Max 50 MB (§10)') }}</span>
                                @error('maxFileSizeMb') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Deadline & Late Policy -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Submission Deadline') }} *</label>
                                <input type="datetime-local" wire:model="deadline" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium" />
                                @error('deadline') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Initial Status') }} *</label>
                                <select wire:model="status" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium">
                                    <option value="draft">{{ __('Draft (Hidden)') }}</option>
                                    <option value="published">{{ __('Published (Active)') }}</option>
                                    <option value="closed">{{ __('Closed') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">{{ __('Allow Late Submissions') }}</span>
                                <span class="text-[11px] text-slate-500 block">{{ __('Submissions after the deadline will be accepted but flagged with a late indicator.') }}</span>
                            </div>
                            <input type="checkbox" wire:model="allowLate" class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500 cursor-pointer" />
                        </div>

                        <!-- Allowed File Types Selection (§8, §10) -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-700">{{ __('Allowed File Types for Submissions') }} *</label>
                            <p class="text-[11px] text-slate-400">{{ __('Students/parents may only upload files matching chosen formats. Server verifies magic-bytes.') }}</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1">
                                @foreach ($availableFileTypes as $key => $name)
                                    <label class="p-2.5 rounded-xl border {{ in_array($key, $allowedFileTypes, true) ? 'border-brand-500 bg-brand-50/50 text-brand-900' : 'border-slate-200 bg-white text-slate-700' }} flex items-center gap-2 cursor-pointer transition-colors text-xs font-medium">
                                        <input type="checkbox" wire:model="allowedFileTypes" value="{{ $key }}" class="w-3.5 h-3.5 text-brand-600 rounded border-slate-300" />
                                        <span>{{ $name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('allowedFileTypes') <p class="text-[10px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Attachments & Reference Materials -->
                        <div class="space-y-3 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-900">{{ __('Assignment Attachments (Question Papers / Templates)') }}</label>
                                <span class="text-[11px] text-slate-400">{{ count($attachments) }} {{ __('attached') }}</span>
                            </div>

                            @if (! empty($attachments))
                                <div class="space-y-2">
                                    @foreach ($attachments as $idx => $att)
                                        <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/70 flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <span>{{ $att['type'] === 'file' ? '📄' : '🔗' }}</span>
                                                <span class="font-bold text-slate-800">{{ $att['title'] }}</span>
                                                @if (! empty($att['file_size']))
                                                    <span class="text-slate-400">({{ $att['file_size'] }})</span>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="removeAttachment({{ $idx }})" class="text-rose-600 hover:text-rose-800 font-bold p-1 cursor-pointer">
                                                &times;
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Mini Upload / Link Input Box -->
                            <div class="p-3.5 rounded-2xl bg-slate-50/60 border border-slate-200 space-y-3">
                                <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2">
                                    <button type="button" wire:click="$set('attachmentTab', 'file')" class="text-xs font-bold {{ $attachmentTab === 'file' ? 'text-brand-600 underline' : 'text-slate-500' }}">
                                        📄 {{ __('Upload File') }}
                                    </button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" wire:click="$set('attachmentTab', 'link')" class="text-xs font-bold {{ $attachmentTab === 'link' ? 'text-brand-600 underline' : 'text-slate-500' }}">
                                        🔗 {{ __('Web Link') }}
                                    </button>
                                </div>

                                @if ($attachmentError)
                                    <p class="text-[11px] text-rose-600 font-semibold">{{ $attachmentError }}</p>
                                @endif

                                @if ($attachmentTab === 'file')
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div class="sm:col-span-1">
                                            <input type="text" wire:model="attachmentTitle" placeholder="{{ __('Title (optional)') }}" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200 bg-white" />
                                        </div>
                                        <div class="sm:col-span-1">
                                            <input type="file" wire:model="attachmentFile" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-brand-50 file:text-brand-700" />
                                        </div>
                                        <div class="sm:col-span-1 flex justify-end">
                                            <button type="button" wire:click="uploadFileAttachment" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 cursor-pointer shadow-xs">
                                                + {{ __('Attach File') }}
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div class="sm:col-span-1">
                                            <input type="text" wire:model="linkTitle" placeholder="{{ __('Link Title') }}" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200 bg-white" />
                                        </div>
                                        <div class="sm:col-span-1">
                                            <input type="url" wire:model="linkUrl" placeholder="{{ __('https://...') }}" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200 bg-white" />
                                        </div>
                                        <div class="sm:col-span-1 flex justify-end">
                                            <button type="button" wire:click="addLinkAttachment" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 cursor-pointer shadow-xs">
                                                + {{ __('Add Link') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100 shrink-0 bg-slate-50/80">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer shadow-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 cursor-pointer shadow-xs inline-flex items-center gap-1.5 btn-interactive">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ $editingAssignmentId ? __('Update Assignment') : __('Save Assignment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Extend Deadline Modal (§8, T-5.3) -->
    @if ($showExtensionModal && $extendingAssignment)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden animate-fade-in" @click.away="$wire.closeExtensionModal()">
                <!-- Header -->
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 bg-slate-50/70">
                    <div>
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('Grant Deadline Extension') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $extendingAssignment->title }}</p>
                    </div>
                    <button type="button" wire:click="closeExtensionModal" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- Body -->
                <form wire:submit.prevent="saveExtension" class="p-6 space-y-4">
                    @if ($extensionError)
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-700 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $extensionError }}</span>
                        </div>
                    @endif

                    <!-- Extension Scope Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Extension Scope') }} *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-2xl border {{ $extensionTarget === 'everyone' ? 'border-brand-500 bg-brand-50/50 text-brand-900' : 'border-slate-200 bg-white text-slate-700' }} flex items-center gap-2.5 cursor-pointer transition-colors text-xs font-medium">
                                <input type="radio" wire:model.live="extensionTarget" value="everyone" class="w-4 h-4 text-brand-600 border-slate-300" />
                                <div>
                                    <span class="font-bold block">{{ __('Everyone in Class') }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ __('All enrolled students') }}</span>
                                </div>
                            </label>

                            <label class="p-3 rounded-2xl border {{ $extensionTarget === 'individual' ? 'border-brand-500 bg-brand-50/50 text-brand-900' : 'border-slate-200 bg-white text-slate-700' }} flex items-center gap-2.5 cursor-pointer transition-colors text-xs font-medium">
                                <input type="radio" wire:model.live="extensionTarget" value="individual" class="w-4 h-4 text-brand-600 border-slate-300" />
                                <div>
                                    <span class="font-bold block">{{ __('Specific Student') }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ __('Individual extension') }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Student Dropdown if individual -->
                    @if ($extensionTarget === 'individual')
                        <div class="animate-fade-in">
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Select Enrolled Student') }} *</label>
                            <select wire:model="extensionStudentId" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium">
                                <option value="">{{ __('Choose a student...') }}</option>
                                @if ($extendingAssignment->schoolClass)
                                    @foreach ($extendingAssignment->schoolClass->students as $student)
                                        <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->admission_number ?? 'ID: '.$student->id }})</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('extensionStudentId') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <!-- New Extended Deadline -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">{{ __('New Extended Deadline') }} *</label>
                            <span class="text-[10px] text-slate-400">
                                {{ __('Current: :date', ['date' => $extendingAssignment->effectiveDeadlineForStudent($extensionTarget === 'individual' ? $extensionStudentId : null)->format('M d, Y h:i A')]) }}
                            </span>
                        </div>
                        <input type="datetime-local" wire:model="extensionNewDeadline" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium" />
                        @error('extensionNewDeadline') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Mandatory Reason (§8) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Mandatory Reason for Extension') }} *</label>
                        <textarea wire:model="extensionReason" rows="3" required placeholder="{{ __('Provide detailed explanation (e.g. Approved medical leave, lab outage, extra research time granted)...') }}" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium"></textarea>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Required. This reason is logged to audit trail and visible to affected students/guardians.') }}</p>
                        @error('extensionReason') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="closeExtensionModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer shadow-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 cursor-pointer shadow-xs inline-flex items-center gap-1.5 btn-interactive">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('Grant Extension') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Extension Log / History Modal (§8, §14) -->
    @if ($showExtensionLogModal && $extensionLogAssignment)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in" @click.away="$wire.closeExtensionLogModal()">
                <!-- Header -->
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 shrink-0 bg-slate-50/70">
                    <div>
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('Deadline Extension History & Audit') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $extensionLogAssignment->title }}</p>
                    </div>
                    <button type="button" wire:click="closeExtensionLogModal" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    @if ($extensionLogAssignment->extensions->isEmpty())
                        <div class="p-8 text-center text-slate-400">
                            {{ __('No deadline extensions have been granted for this assignment.') }}
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($extensionLogAssignment->extensions->sortByDesc('created_at') as $ext)
                                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-2">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            @if ($ext->student)
                                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold text-[10px]">
                                                    👤 {{ $ext->student->full_name }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold text-[10px]">
                                                    👥 {{ __('Class-wide (Everyone)') }}
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-slate-500">
                                                {{ __('Extended to') }} <strong class="text-slate-800">{{ $ext->extended_deadline->format('M d, Y • h:i A') }}</strong>
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-slate-400">
                                            {{ $ext->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <div class="text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-100">
                                        <span class="font-bold text-slate-500">{{ __('Reason:') }}</span> {{ $ext->reason }}
                                    </div>

                                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1">
                                        <span>
                                            {{ __('Original Deadline:') }} {{ $ext->original_deadline ? $ext->original_deadline->format('M d, Y • h:i A') : __('None') }}
                                        </span>
                                        <span>
                                            {{ __('Granted by:') }} <strong class="text-slate-600">{{ $ext->extendedBy?->name ?? __('Staff') }}</strong>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-6 py-3 flex items-center justify-end border-t border-slate-100 shrink-0 bg-slate-50/80">
                    <button type="button" wire:click="closeExtensionLogModal" class="px-4 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer shadow-xs">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
