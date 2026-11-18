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
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-5 animate-fade-in" @click.away="$wire.closeStudentModal()">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $editingStudentId ? __('Edit Child Details') : __('Enroll New Child') }}
                    </h3>
                    <button type="button" wire:click="closeStudentModal" class="text-slate-400 hover:text-slate-600">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="saveStudent" class="space-y-4">
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

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
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
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5 animate-fade-in" @click.away="$wire.closeLinkGuardianModal()">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Link Additional Guardian (§6.5)') }}</h3>
                    <button type="button" wire:click="closeLinkGuardianModal" class="text-slate-400 hover:text-slate-600">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="linkGuardian" class="space-y-4">
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

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
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
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5 animate-fade-in" @click.away="$wire.closeEnrollModal()">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Enroll into Nursery Class') }}</h3>
                    <button type="button" wire:click="closeEnrollModal" class="text-slate-400 hover:text-slate-600">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="enrollClass" class="space-y-4">
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

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
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
</div>
