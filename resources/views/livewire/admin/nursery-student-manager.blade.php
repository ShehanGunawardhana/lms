<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Nursery Students & Guardians Roster') }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Manage nursery children, guardian links, multi-child parents, and classroom group enrollments (§6.5)') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                type="button" 
                wire:click="openCreateModal"
                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-xs transition-all cursor-pointer inline-flex items-center gap-1.5 btn-interactive">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Add New Child') }}
            </button>
            <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Admin Dashboard') }}
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between animate-fade-in">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Children') }}</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_students'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Active Enrolled') }}</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['active_students'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Nursery Groups') }}</p>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $stats['total_classes'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Registered Guardians') }}</p>
            <p class="text-2xl font-black text-brand-600 mt-1">{{ $stats['total_guardians'] }}</p>
        </div>
    </div>

    <!-- Controls Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <!-- Status Filter -->
            <select 
                wire:model.live="statusFilter"
                class="px-3 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 font-medium">
                <option value="all">{{ __('All Statuses') }}</option>
                <option value="active">{{ __('Active') }}</option>
                <option value="registered">{{ __('Registered') }}</option>
                <option value="suspended">{{ __('Suspended') }}</option>
                <option value="left">{{ __('Left') }}</option>
            </select>

            <!-- Class Filter -->
            <select 
                wire:model.live="classFilter"
                class="px-3 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 font-medium">
                <option value="all">{{ __('All Nursery Classes') }}</option>
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
                    placeholder="{{ __('Search child, code, guardian...') }}"
                    class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Roster Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[11px]">
                        <th class="py-3 px-4">{{ __('Student / Code') }}</th>
                        <th class="py-3 px-4">{{ __('Date of Birth & Age') }}</th>
                        <th class="py-3 px-4">{{ __('Linked Guardians (§6.5)') }}</th>
                        <th class="py-3 px-4">{{ __('Nursery Class Group') }}</th>
                        <th class="py-3 px-4">{{ __('Status') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($students as $student)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Student / Code -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $student->full_name }}</div>
                                <span class="font-mono text-[11px] text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md inline-block mt-0.5">
                                    {{ $student->student_code }}
                                </span>
                            </td>

                            <!-- DOB & Age -->
                            <td class="py-3.5 px-4 text-slate-600">
                                <div>{{ format_portal_datetime($student->dob, 'Y-m-d') }}</div>
                                <span class="text-[11px] text-slate-400">{{ $student->age_years }} {{ __('years old') }} ({{ ucfirst($student->gender ?? 'unspecified') }})</span>
                            </td>

                            <!-- Guardians -->
                            <td class="py-3.5 px-4">
                                <div class="space-y-1.5">
                                    @forelse ($student->guardians as $g)
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-semibold text-slate-800">{{ $g->name }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold {{ $g->pivot->is_primary ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-600' }}">
                                                {{ ucfirst($g->pivot->relationship) }}
                                                @if ($g->pivot->is_primary) &bull; {{ __('Primary') }} @endif
                                            </span>
                                            @if ($student->guardians->count() > 1)
                                                <button 
                                                    type="button" 
                                                    wire:click="unlinkGuardian({{ $student->id }}, {{ $g->id }})"
                                                    title="{{ __('Unlink Guardian') }}"
                                                    class="text-slate-300 hover:text-rose-500 cursor-pointer ml-1">
                                                    &times;
                                                </button>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-rose-500 italic text-[11px]">{{ __('No guardian linked') }}</span>
                                    @endforelse
                                    <button 
                                        type="button" 
                                        wire:click="openLinkGuardianModal({{ $student->id }})"
                                        class="text-[11px] font-bold text-brand-600 hover:underline inline-flex items-center gap-1 mt-1 cursor-pointer">
                                        + {{ __('Link Parent / Guardian') }}
                                    </button>
                                </div>
                            </td>

                            <!-- Nursery Class -->
                            <td class="py-3.5 px-4">
                                @if ($student->classes->isNotEmpty())
                                    @foreach ($student->classes as $c)
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-950 font-bold text-[11px]">
                                                {{ $c->title }}
                                            </span>
                                            <button 
                                                type="button" 
                                                wire:click="unenrollClass({{ $student->id }}, {{ $c->id }})"
                                                title="{{ __('Remove from class') }}"
                                                class="text-slate-300 hover:text-rose-500 cursor-pointer">
                                                &times;
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-amber-600 italic text-[11px] block">{{ __('Not enrolled') }}</span>
                                @endif
                                <button 
                                    type="button" 
                                    wire:click="openEnrollModal({{ $student->id }})"
                                    class="text-[11px] font-bold text-amber-700 hover:underline inline-flex items-center gap-1 mt-1 cursor-pointer">
                                    + {{ __('Enroll in Group') }}
                                </button>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4">
                                @php $sVal = $student->status instanceof \App\Enums\StudentStatus ? $student->status->value : (string) $student->status; @endphp
                                @if ($sVal === 'active')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ __('Active') }}
                                    </span>
                                @elseif ($sVal === 'registered')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        {{ __('Registered') }}
                                    </span>
                                @elseif ($sVal === 'suspended')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        {{ __('Suspended') }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ ucfirst($sVal) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <button 
                                    type="button" 
                                    wire:click="openSafetyModal({{ $student->id }})"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition-colors cursor-pointer inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __('Health & Safety') }}
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="openEditModal({{ $student->id }})"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">
                                    {{ __('Edit') }}
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="deleteStudent({{ $student->id }})"
                                    wire:confirm="{{ __('Are you sure you want to remove this child from the roster?') }}"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition-colors cursor-pointer">
                                    {{ __('Delete') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                {{ __('No students found matching current criteria.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $students->links() }}
        </div>
    </div>

    <!-- Create / Edit Student Modal -->
    @if ($showStudentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full my-auto max-h-[92vh] flex flex-col overflow-hidden animate-fade-in" @click.away="$wire.closeStudentModal()">
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 shrink-0">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $editingStudentId ? __('Edit Child Details') : __('Enroll New Child') }}
                    </h3>
                    <button type="button" wire:click="closeStudentModal" class="text-slate-400 hover:text-slate-600">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="saveStudent" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('First Name') }} *</label>
                                <input type="text" wire:model="firstName" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600" />
                                @error('firstName') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Last Name') }} *</label>
                                <input type="text" wire:model="lastName" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600" />
                                @error('lastName') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Date of Birth') }} *</label>
                                <input type="date" wire:model="dob" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600" />
                                @error('dob') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Gender') }}</label>
                                <select wire:model="gender" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600">
                                    <option value="male">{{ __('Male') }}</option>
                                    <option value="female">{{ __('Female') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Status') }}</label>
                            <select wire:model="status" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="registered">{{ __('Registered') }}</option>
                                <option value="suspended">{{ __('Suspended') }}</option>
                                <option value="left">{{ __('Left') }}</option>
                            </select>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Assign Primary Guardian') }}</label>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-2">
                                    <select wire:model="selectedGuardianId" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600">
                                        <option value="">{{ __('Select Registered Guardian...') }}</option>
                                        @foreach ($guardians as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->user?->phone ?? 'no phone' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <select wire:model="guardianRelationship" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600">
                                        <option value="mother">{{ __('Mother') }}</option>
                                        <option value="father">{{ __('Father') }}</option>
                                        <option value="legal_guardian">{{ __('Guardian') }}</option>
                                        <option value="grandparent">{{ __('Grandparent') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Initial Class / Group Placement') }}</label>
                            <select wire:model="selectedClassId" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600">
                                <option value="">{{ __('Select Nursery Group...') }}</option>
                                @foreach ($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100 shrink-0 bg-slate-50/80">
                        <button type="button" wire:click="closeStudentModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 cursor-pointer">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 cursor-pointer shadow-xs">
                            {{ __('Save Student') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Link Additional Guardian Modal -->
    @if ($showLinkGuardianModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full my-auto max-h-[92vh] flex flex-col overflow-hidden animate-fade-in" @click.away="$wire.closeLinkGuardianModal()">
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 shrink-0">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Link Additional Guardian (§6.5)') }}</h3>
                    <button type="button" wire:click="closeLinkGuardianModal" class="text-slate-400 hover:text-slate-600">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="linkGuardian" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Select Guardian / Parent') }} *</label>
                            <select wire:model="linkGuardianId" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600">
                                <option value="">{{ __('Select Guardian...') }}</option>
                                @foreach ($guardians as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->user?->phone ?? 'no phone' }})</option>
                                @endforeach
                            </select>
                            @error('linkGuardianId') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Relationship to Child') }} *</label>
                            <select wire:model="linkRelationship" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600">
                                <option value="father">{{ __('Father') }}</option>
                                <option value="mother">{{ __('Mother') }}</option>
                                <option value="legal_guardian">{{ __('Legal Guardian') }}</option>
                                <option value="grandparent">{{ __('Grandparent') }}</option>
                                <option value="authorized_pickup">{{ __('Authorized Pickup / Relative') }}</option>
                            </select>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer pt-1">
                            <input type="checkbox" wire:model="isPrimary" class="rounded text-brand-600 focus:ring-brand-500 border-slate-300 w-4 h-4" />
                            <span class="text-xs text-slate-700 font-semibold">{{ __('Designate as Primary Guardian') }}</span>
                        </label>
                    </div>

                    <div class="px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100 shrink-0 bg-slate-50/80">
                        <button type="button" wire:click="closeLinkGuardianModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 cursor-pointer">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 cursor-pointer shadow-xs">
                            {{ __('Link Guardian') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Enroll Class Modal -->
    @if ($showEnrollModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full my-auto max-h-[92vh] flex flex-col overflow-hidden animate-fade-in" @click.away="$wire.closeEnrollModal()">
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 shrink-0">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Enroll into Nursery Class') }}</h3>
                    <button type="button" wire:click="closeEnrollModal" class="text-slate-400 hover:text-slate-600">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="enrollClass" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Select Nursery Group') }} *</label>
                            <select wire:model="enrollClassId" required class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600">
                                <option value="">{{ __('Select Group...') }}</option>
                                @foreach ($classes as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->title }} ({{ __('Capacity') }}: {{ $c->capacity }}, {{ __('Available') }}: {{ $c->availableSeats() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('enrollClassId') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100 shrink-0 bg-slate-50/80">
                        <button type="button" wire:click="closeEnrollModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 cursor-pointer">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 cursor-pointer shadow-xs">
                            {{ __('Enroll Child') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Health & Safety Modal (§6.5) -->
    @if ($showSafetyModal && $safetyStudent)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full my-auto max-h-[92vh] flex flex-col overflow-hidden animate-fade-in" @click.away="$wire.closeSafetyModal()">
                <!-- Modal Header -->
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 shrink-0 bg-slate-50/70">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-black text-slate-900">{{ $safetyStudent->full_name }}</h3>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-200/70 text-slate-700">{{ $safetyStudent->student_code }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                    {{ __('AES-256 Encrypted at Rest (§6.5)') }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeSafetyModal" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex border-b border-slate-100 bg-white px-6 shrink-0 gap-2 pt-2">
                    <button 
                        type="button" 
                        wire:click="setSafetyTab('medical')"
                        class="pb-2.5 px-3 text-xs font-bold transition-all border-b-2 cursor-pointer {{ $safetyActiveTab === 'medical' ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        🩺 {{ __('Medical & Allergies') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="setSafetyTab('contacts')"
                        class="pb-2.5 px-3 text-xs font-bold transition-all border-b-2 cursor-pointer inline-flex items-center gap-1.5 {{ $safetyActiveTab === 'contacts' ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        📞 {{ __('Emergency Contacts') }}
                        @if ($safetyStudent->emergencyContacts->isNotEmpty())
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-mono">{{ $safetyStudent->emergencyContacts->count() }}</span>
                        @endif
                    </button>
                    <button 
                        type="button" 
                        wire:click="setSafetyTab('pickups')"
                        class="pb-2.5 px-3 text-xs font-bold transition-all border-b-2 cursor-pointer inline-flex items-center gap-1.5 {{ $safetyActiveTab === 'pickups' ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        🚗 {{ __('Authorized Pickups') }}
                        @if ($safetyStudent->authorizedPickups->isNotEmpty())
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-mono">{{ $safetyStudent->authorizedPickups->count() }}</span>
                        @endif
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto flex-1 space-y-5">
                    @if (session('safety_success'))
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            <span>{{ session('safety_success') }}</span>
                        </div>
                    @endif

                    <!-- TAB 1: MEDICAL & ALLERGIES -->
                    @if ($safetyActiveTab === 'medical')
                        <div class="space-y-4">
                            <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-2xl text-[11px] text-amber-900 leading-relaxed">
                                <span class="font-bold">{{ __('Privacy Notice:') }}</span>
                                {{ __('Medical information and allergy notes are strictly restricted under policy to active classroom teachers, authorized guardians, and administrators.') }}
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Blood Group') }}</label>
                                    <select wire:model="bloodGroup" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium">
                                        <option value="">{{ __('Select Blood Group...') }}</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                    @error('bloodGroup') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Known Allergies (Encrypted)') }}</label>
                                    <input 
                                        type="text" 
                                        wire:model="allergies" 
                                        placeholder="{{ __('e.g. Peanuts, Dairy, Penicillin') }}"
                                        class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium" 
                                    />
                                    @error('allergies') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Medical Notes & Chronic Conditions (Encrypted)') }}</label>
                                <textarea 
                                    wire:model="medicalNotes" 
                                    rows="3" 
                                    placeholder="{{ __('Enter any dietary restrictions, inhaler needs, seizures, or special care instructions...') }}"
                                    class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium"></textarea>
                                @error('medicalNotes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Emergency Physician / Hospital Instructions') }}</label>
                                <textarea 
                                    wire:model="emergencyNotes" 
                                    rows="2" 
                                    placeholder="{{ __('e.g. Dr. Silva (+94 11 234 5678) at Asiri Central Hospital') }}"
                                    class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium"></textarea>
                                @error('emergencyNotes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button 
                                    type="button" 
                                    wire:click="saveMedicalNotes" 
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 cursor-pointer shadow-xs inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    {{ __('Save Medical Information') }}
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 2: EMERGENCY CONTACTS -->
                    @if ($safetyActiveTab === 'contacts')
                        <div class="space-y-5">
                            <!-- Existing Contacts List -->
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">{{ __('Current Emergency Contacts') }}</h4>
                                @forelse ($safetyStudent->emergencyContacts as $contact)
                                    <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/70 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="w-7 h-7 rounded-xl font-black text-xs flex items-center justify-center {{ $contact->priority === 1 ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-200 text-slate-700' }}">
                                                P{{ $contact->priority }}
                                            </span>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-900 text-xs">{{ $contact->name }}</span>
                                                    <span class="text-[10px] font-semibold text-slate-500 uppercase px-1.5 py-0.5 rounded bg-white border border-slate-200">
                                                        {{ $contact->relationship }}
                                                    </span>
                                                    @if ($contact->isPrimary())
                                                        <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">{{ __('Primary') }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-600 mt-0.5 flex flex-wrap items-center gap-3">
                                                    <span>📞 {{ $contact->phone_primary }}</span>
                                                    @if ($contact->phone_secondary)
                                                        <span class="text-slate-400">Alt: {{ $contact->phone_secondary }}</span>
                                                    @endif
                                                    @if ($contact->notes)
                                                        <span class="text-slate-500 italic">({{ $contact->notes }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <button 
                                            type="button" 
                                            wire:click="deleteEmergencyContact({{ $contact->id }})"
                                            wire:confirm="{{ __('Remove this emergency contact?') }}"
                                            class="text-xs text-rose-600 hover:text-rose-800 font-bold p-1 cursor-pointer">
                                            &times; {{ __('Remove') }}
                                        </button>
                                    </div>
                                @empty
                                    <div class="p-4 rounded-xl border border-dashed border-slate-200 text-center text-xs text-slate-400">
                                        {{ __('No emergency contacts added yet.') }}
                                    </div>
                                @endforelse
                            </div>

                            <!-- Add New Emergency Contact Form -->
                            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">{{ __('Add Emergency Contact') }}</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Full Name') }} *</label>
                                        <input type="text" wire:model="contactName" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('Contact name') }}" />
                                        @error('contactName') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Relationship') }} *</label>
                                        <input type="text" wire:model="contactRelationship" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('e.g. Mother, Father, Uncle') }}" />
                                        @error('contactRelationship') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Priority') }} *</label>
                                        <select wire:model="contactPriority" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200 font-medium">
                                            <option value="1">1 (Primary Contact)</option>
                                            <option value="2">2 (Secondary Contact)</option>
                                            <option value="3">3 (Tertiary Contact)</option>
                                            <option value="4">4 (Backup)</option>
                                        </select>
                                        @error('contactPriority') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Primary Phone') }} *</label>
                                        <input type="text" wire:model="contactPhonePrimary" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('+94 77 123 4567') }}" />
                                        @error('contactPhonePrimary') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Secondary Phone') }}</label>
                                        <input type="text" wire:model="contactPhoneSecondary" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('Optional alternative') }}" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Notes') }}</label>
                                    <input type="text" wire:model="contactNotes" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('e.g. Lives next door, can arrive in 5 mins') }}" />
                                </div>

                                <div class="flex justify-end pt-1">
                                    <button 
                                        type="button" 
                                        wire:click="addEmergencyContact"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 cursor-pointer shadow-xs">
                                        + {{ __('Add Contact') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 3: AUTHORIZED PICKUPS -->
                    @if ($safetyActiveTab === 'pickups')
                        <div class="space-y-5">
                            <!-- Existing Pickups List -->
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">{{ __('Authorized Pickups (§6.5)') }}</h4>
                                @forelse ($safetyStudent->authorizedPickups as $pickup)
                                    <div class="p-3.5 rounded-2xl border {{ $pickup->is_active ? 'border-slate-200 bg-white' : 'border-slate-200 bg-slate-50/70 opacity-60' }} flex items-center justify-between gap-3 shadow-xs">
                                        <div class="flex items-center gap-3">
                                            @if ($pickup->photoUrl())
                                                <img src="{{ $pickup->photoUrl() }}" alt="{{ $pickup->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200" />
                                            @else
                                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs">
                                                    {{ strtoupper(substr($pickup->name, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-900 text-xs">{{ $pickup->name }}</span>
                                                    <span class="text-[10px] font-semibold text-slate-500 uppercase px-1.5 py-0.5 rounded bg-slate-100">
                                                        {{ $pickup->relationship }}
                                                    </span>
                                                    @if ($pickup->is_active)
                                                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">{{ __('Active Authorized') }}</span>
                                                    @else
                                                        <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-1.5 py-0.5 rounded">{{ __('Suspended') }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-600 mt-0.5 flex flex-wrap items-center gap-3">
                                                    <span>📱 {{ $pickup->phone }}</span>
                                                    @if ($pickup->national_id)
                                                        <span class="font-mono text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded">NIC: {{ $pickup->national_id }}</span>
                                                    @endif
                                                    @if ($pickup->notes)
                                                        <span class="text-slate-500 italic">({{ $pickup->notes }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button 
                                                type="button" 
                                                wire:click="togglePickupActive({{ $pickup->id }})"
                                                class="px-2 py-1 rounded-lg text-[11px] font-bold transition-colors cursor-pointer {{ $pickup->is_active ? 'text-amber-800 bg-amber-50 hover:bg-amber-100' : 'text-emerald-800 bg-emerald-50 hover:bg-emerald-100' }}">
                                                {{ $pickup->is_active ? __('Suspend') : __('Activate') }}
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="deleteAuthorizedPickup({{ $pickup->id }})"
                                                wire:confirm="{{ __('Remove this authorized pickup person?') }}"
                                                class="text-xs text-rose-600 hover:text-rose-800 font-bold p-1 cursor-pointer">
                                                &times;
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 rounded-xl border border-dashed border-slate-200 text-center text-xs text-slate-400">
                                        {{ __('No authorized pickup persons recorded yet.') }}
                                    </div>
                                @endforelse
                            </div>

                            <!-- Add New Pickup Form -->
                            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">{{ __('Add Authorized Pickup Person') }}</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Full Name') }} *</label>
                                        <input type="text" wire:model="pickupName" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('Person\'s name') }}" />
                                        @error('pickupName') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Relationship to Child') }} *</label>
                                        <input type="text" wire:model="pickupRelationship" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('e.g. Grandparent, Nanny, Driver') }}" />
                                        @error('pickupRelationship') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Phone Number') }} *</label>
                                        <input type="text" wire:model="pickupPhone" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('+94 77 123 4567') }}" />
                                        @error('pickupPhone') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('National ID / NIC') }}</label>
                                        <input type="text" wire:model="pickupNationalId" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('e.g. 198512345678') }}" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Notes & Verification Instructions') }}</label>
                                    <input type="text" wire:model="pickupNotes" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200" placeholder="{{ __('e.g. Authorized for Friday afternoon pickup only') }}" />
                                </div>

                                <div class="flex justify-end pt-1">
                                    <button 
                                        type="button" 
                                        wire:click="addAuthorizedPickup"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 cursor-pointer shadow-xs">
                                        + {{ __('Authorize Pickup Person') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 flex items-center justify-end border-t border-slate-100 shrink-0 bg-slate-50/80">
                    <button type="button" wire:click="closeSafetyModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer shadow-xs">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
