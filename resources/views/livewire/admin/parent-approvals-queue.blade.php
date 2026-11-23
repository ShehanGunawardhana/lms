<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Parent Approvals Queue') }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Review, approve or reject parent registrations and nursery child enrollment applications (§5.2)') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
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

    @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-900 flex items-center gap-2 animate-fade-in">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Controls Bar: Status Filter Tabs & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'pending')"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/20' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <span>{{ __('Pending Review') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'pending' ? 'bg-white text-amber-700' : 'bg-amber-100 text-amber-800' }}">
                    {{ $counts['pending'] }}
                </span>
            </button>

            <button 
                type="button" 
                wire:click="$set('statusFilter', 'active')"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <span>{{ __('Approved') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'active' ? 'bg-white text-emerald-700' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $counts['active'] }}
                </span>
            </button>

            <button 
                type="button" 
                wire:click="$set('statusFilter', 'rejected')"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <span>{{ __('Rejected') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'rejected' ? 'bg-white text-rose-700' : 'bg-rose-100 text-rose-800' }}">
                    {{ $counts['rejected'] }}
                </span>
            </button>

            <button 
                type="button" 
                wire:click="$set('statusFilter', 'all')"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <span>{{ __('All') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'all' ? 'bg-white text-slate-900' : 'bg-slate-100 text-slate-700' }}">
                    {{ $counts['all'] }}
                </span>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="w-full sm:w-72">
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="{{ __('Search parent, email, child...') }}"
                    class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Applicants Queue List -->
    @if ($profiles->isEmpty())
        <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 space-y-3">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">{{ __('No applications found in this view') }}</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                {{ __('There are no registration records matching your current filter criteria.') }}
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($profiles as $profile)
                @php $user = $profile->user; @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4 shadow-xs hover:border-slate-300 transition-colors">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-slate-100">
                        <!-- Left: Parent Summary -->
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2.5">
                                <h3 class="text-sm font-bold text-slate-900">{{ $user?->name ?? __('Unknown Parent') }}</h3>
                                @if ($user?->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        {{ __('Pending Review') }}
                                    </span>
                                @elseif ($user?->status === 'active')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ __('Active / Approved') }}
                                    </span>
                                @elseif ($user?->status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        {{ __('Rejected') }}
                                    </span>
                                @endif
                                <span class="text-[11px] text-slate-400">&bull; {{ format_portal_datetime($profile->created_at) }}</span>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span>📧 {{ $user?->email }}</span>
                                <span>📱 {{ $user?->phone ?? __('No mobile') }}</span>
                                <span class="capitalize">👥 {{ str_replace('_', ' ', $profile->relationship) }}</span>
                            </div>

                            <!-- Badges: Verification & Consents -->
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <!-- Email verified -->
                                @if ($user?->email_verified_at)
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-semibold text-emerald-800 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        {{ __('Email Verified') }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-medium text-slate-600">
                                        {{ __('Email Unverified') }}
                                    </span>
                                @endif

                                <!-- Phone verified -->
                                @if ($user?->phone_verified_at)
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-semibold text-emerald-800 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        {{ __('Phone Verified') }}
                                    </span>
                                @endif

                                <!-- Photo consent -->
                                @if ($profile->consent_photo)
                                    <span class="px-2 py-0.5 rounded-md bg-brand-50 border border-brand-200 text-[10px] font-semibold text-brand-900 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /></svg>
                                        {{ __('Photo Release Granted') }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-medium text-slate-500">
                                        {{ __('No Photo Release') }}
                                    </span>
                                @endif

                                <!-- Statutory consents -->
                                @if ($profile->consent_terms && $profile->consent_child_safety)
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-medium text-slate-600">
                                        {{ __('Policies Agreed') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Action Buttons -->
                        <div class="flex items-center gap-2 self-start lg:self-center">
                            @if ($user?->status === 'pending')
                                <!-- One-Click Approve -->
                                <button 
                                    type="button" 
                                    wire:click="approve({{ $profile->id }})"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 active:scale-[0.98] shadow-xs transition-all cursor-pointer inline-flex items-center gap-1.5 btn-interactive">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    {{ __('Approve') }}
                                </button>

                                <!-- Reject Button -->
                                <button 
                                    type="button" 
                                    wire:click="openRejectModal({{ $profile->id }})"
                                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors cursor-pointer inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    {{ __('Reject') }}
                                </button>
                            @elseif ($user?->status === 'active')
                                <div class="text-right">
                                    <span class="text-xs font-semibold text-emerald-800 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        {{ __('Approved') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">{{ format_portal_datetime($profile->reviewed_at) }}</span>
                                </div>
                            @elseif ($user?->status === 'rejected')
                                <button 
                                    type="button" 
                                    wire:click="approve({{ $profile->id }})"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                    {{ __('Re-evaluate & Approve') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Rejection Reason if Rejected -->
                    @if ($user?->status === 'rejected' && $profile->rejection_reason)
                        <div class="p-3 rounded-xl bg-rose-50/70 border border-rose-200 text-xs text-rose-900 space-y-1">
                            <span class="font-bold uppercase tracking-wider text-[10px] text-rose-800">{{ __('Rejection Reason Recorded:') }}</span>
                            <p class="leading-relaxed">{{ $profile->rejection_reason }}</p>
                        </div>
                    @endif

                    <!-- Children Cards -->
                    @if ($profile->children->isNotEmpty())
                        <div class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">{{ __('Enrolling Children') }}</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach ($profile->children as $child)
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-900">{{ $child->full_name }}</span>
                                            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[10px] font-bold uppercase tracking-wider">
                                                {{ ucfirst(str_replace('_', ' ', $child->nursery_group)) }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-3">
                                            <span>🎂 {{ format_portal_datetime($child->dob, 'Y-m-d') }} ({{ $child->age_years }} {{ __('yrs') }})</span>
                                            @if ($child->gender)
                                                <span class="capitalize">⚧ {{ $child->gender }}</span>
                                            @endif
                                        </div>
                                        @if ($child->medical_notes)
                                            <div class="pt-1 text-[11px] text-rose-700 flex items-start gap-1">
                                                <span class="font-bold">{{ __('Medical:') }}</span>
                                                <span>{{ $child->medical_notes }}</span>
                                            </div>
                                        @endif
                                        @if ($child->dietary_needs)
                                            <div class="text-[11px] text-amber-800 flex items-start gap-1">
                                                <span class="font-bold">{{ __('Diet:') }}</span>
                                                <span>{{ $child->dietary_needs }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="pt-2">
                {{ $profiles->links() }}
            </div>
        </div>
    @endif

    <!-- Reject Confirmation Modal -->
    <!-- Reject Confirmation Modal -->
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="relative bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full max-h-[92vh] flex flex-col my-auto overflow-hidden animate-fade-in" @click.away="$wire.closeRejectModal()">
                <div class="px-6 py-4 border-b border-slate-100 shrink-0 flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ __('Reject Application') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('A specific reason is mandatory for rejection (§5.2).') }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeRejectModal" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    <div class="space-y-2">
                        <label for="rejectionReason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            {{ __('Rejection Reason (will be sent to parent)') }} <span class="text-rose-500">*</span>
                        </label>
                        <textarea 
                            id="rejectionReason"
                            wire:model="rejectionReason" 
                            rows="4" 
                            placeholder="{{ __('e.g., Nursery age group at full capacity for this term; Incomplete immunization records...') }}"
                            class="block w-full px-3.5 py-2.5 rounded-xl text-xs border @error('rejectionReason') border-rose-300 focus:border-rose-500 @else border-slate-300 focus:border-rose-500 @enderror focus:ring-rose-500 shadow-xs"
                        ></textarea>
                        @error('rejectionReason')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex items-center justify-end gap-3 bg-slate-50/80">
                    <button 
                        type="button" 
                        wire:click="closeRejectModal" 
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 cursor-pointer">
                        {{ __('Cancel') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="rejectProfile" 
                        wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 cursor-pointer shadow-xs">
                        {{ __('Confirm Rejection') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
