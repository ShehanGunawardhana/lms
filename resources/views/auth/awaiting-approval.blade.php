@extends('layouts.guest', [
    'title' => __('Registration Under Review'),
    'heading' => __('Application Received'),
    'subheading' => __('Your parent registration is being reviewed by the institution admissions office (§5.2)')
])

@section('content')
<div class="space-y-6" x-data="{ showPhoneVerify: false }">
    <!-- Status Banner -->
    @if ($user && $user->isRejected())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 space-y-2 animate-fade-in shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <span class="font-bold text-sm">{{ __('Registration Not Approved') }}</span>
            </div>
            @if ($profile && $profile->rejection_reason)
                <p class="text-xs text-rose-800 bg-white/70 p-3 rounded-xl border border-rose-100 font-medium">
                    <span class="font-bold">{{ __('Reason provided:') }}</span> {{ $profile->rejection_reason }}
                </p>
            @endif
            <p class="text-xs text-rose-700">
                {{ __('If you believe this is an error, please contact the admissions department directly.') }}
            </p>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-amber-950 space-y-2 shadow-xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                    </span>
                    <span class="font-bold text-sm text-amber-950">{{ __('Status: Pending Review') }}</span>
                </div>
                <span class="text-[11px] font-semibold text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full">
                    {{ __('Step 2 of 3') }}
                </span>
            </div>
            <p class="text-xs text-amber-900 leading-relaxed">
                {{ __('Thank you for registering with us! Our admissions team verifies all incoming guardian credentials and nursery class placements before enabling classroom activities.') }}
            </p>
        </div>
    @endif

    <!-- Feedback messages -->
    @if (session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center gap-2 animate-fade-in">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-900 flex items-center gap-2 animate-fade-in">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($user)
        <!-- Registration Summary Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 space-y-4 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $user->email }} &bull; {{ $user->phone ?? __('No phone provided') }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                    {{ ucfirst($profile->relationship ?? 'Parent') }}
                </span>
            </div>

            <!-- Verification Status Badges -->
            <div class="space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block">{{ __('Identity & Contact Verification (§5.2)') }}</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <!-- Email verification -->
                    <div class="p-3 rounded-xl border {{ $user->email_verified_at ? 'bg-emerald-50/50 border-emerald-200 text-emerald-950' : 'bg-slate-50 border-slate-200 text-slate-800' }} flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            @if ($user->email_verified_at)
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                <span class="font-bold">{{ __('Email Verified') }}</span>
                            @else
                                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                <span class="font-medium text-slate-700">{{ __('Email Pending') }}</span>
                            @endif
                        </div>
                        @if (! $user->email_verified_at)
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 hover:underline cursor-pointer">
                                    {{ __('Resend Email') }}
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Phone verification -->
                    <div class="p-3 rounded-xl border {{ $user->phone_verified_at ? 'bg-emerald-50/50 border-emerald-200 text-emerald-950' : 'bg-slate-50 border-slate-200 text-slate-800' }} flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            @if ($user->phone_verified_at)
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                <span class="font-bold">{{ __('Phone Verified') }}</span>
                            @else
                                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                                <span class="font-medium text-slate-700">{{ __('SMS OTP Pending') }}</span>
                            @endif
                        </div>
                        @if (! $user->phone_verified_at && ! empty($user->phone))
                            <button 
                                type="button" 
                                @click="showPhoneVerify = !showPhoneVerify" 
                                class="text-[11px] font-bold text-brand-600 hover:text-brand-700 hover:underline cursor-pointer">
                                {{ __('Verify via SMS') }}
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Inline SMS OTP Verification Drawer -->
                @if (! $user->phone_verified_at && ! empty($user->phone))
                    <div x-show="showPhoneVerify" x-cloak class="p-4 rounded-xl bg-brand-50/70 border border-brand-200 space-y-3 animate-fade-in">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-brand-950">{{ __('Verify Mobile Phone via SMS OTP') }}</span>
                            <form method="POST" action="{{ route('verification.phone.send') }}">
                                @csrf
                                <button type="submit" class="text-[11px] font-bold text-brand-700 hover:underline cursor-pointer">
                                    {{ __('Send OTP Code') }}
                                </button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('verification.phone.verify') }}" class="flex items-center gap-2">
                            @csrf
                            <input 
                                type="text" 
                                name="code" 
                                required 
                                maxlength="6" 
                                placeholder="123456" 
                                class="w-32 px-3 py-1.5 text-center font-mono text-sm font-bold bg-white border border-brand-300 rounded-xl focus:ring-brand-600"
                            />
                            <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 cursor-pointer shadow-xs">
                                {{ __('Confirm Code') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Child Registration Details -->
            @if ($profile && $profile->children->isNotEmpty())
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block">{{ __('Submitted Child Details') }}</span>
                    @foreach ($profile->children as $child)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                            <div>
                                <span class="font-bold text-slate-900">{{ $child->full_name }}</span>
                                <span class="text-slate-500 ml-1">({{ format_portal_datetime($child->dob, 'Y-m-d') }}, {{ __('Age') }}: {{ $child->age_years }}y)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 font-bold uppercase tracking-wider text-[10px]">
                                    {{ ucfirst(str_replace('_', ' ', $child->nursery_group)) }}
                                </span>
                                @if ($profile->consent_photo)
                                    <span class="px-2 py-0.5 rounded-md bg-brand-100 text-brand-900 font-medium text-[10px] flex items-center gap-1">
                                        <svg class="w-3 h-3 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /></svg>
                                        {{ __('Photo Release Granted') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Workflow Roadmap -->
    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-3 text-xs">
        <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">{{ __('Admission Workflow (§5.2)') }}</h4>
        <ol class="space-y-2.5 text-slate-600">
            <li class="flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center shrink-0 text-[11px]">✓</span>
                <div>
                    <span class="font-bold text-slate-900">{{ __('Registration Submitted:') }}</span>
                    <span>{{ __('Parent profile and child details recorded in portal.') }}</span>
                </div>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center shrink-0 text-[11px]">2</span>
                <div>
                    <span class="font-bold text-slate-900">{{ __('Staff Verification:') }}</span>
                    <span>{{ __('Nursery administrator verifies child age, emergency contacts, and class room capacity.') }}</span>
                </div>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 font-bold flex items-center justify-center shrink-0 text-[11px]">3</span>
                <div>
                    <span class="font-bold text-slate-900">{{ __('Classroom Activation:') }}</span>
                    <span>{{ __('Instant notification sent via SMS/Email with direct access to weekly learning feed, activities, and daily reports.') }}</span>
                </div>
            </li>
        </ol>
    </div>

    <!-- Actions -->
    <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
        <a href="{{ route('awaiting-approval') }}" class="w-full sm:w-1/2 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
            {{ __('Check Status Refresh') }}
        </a>

        @if ($user)
            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-1/2">
                @csrf
                <button 
                    type="submit" 
                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                    {{ __('Sign Out for Now') }}
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="w-full sm:w-1/2 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-xs transition-colors">
                {{ __('Sign In to Check') }}
            </a>
        @endif
    </div>
</div>
@endsection
