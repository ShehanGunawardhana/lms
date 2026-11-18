@extends('layouts.guest', [
    'title' => __('Register Account'),
    'heading' => __('Create Parent Account'),
    'subheading' => __('Register to access nursery learning activities and student tracking')
])

@section('content')
<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    <!-- Full Name -->
    <x-input 
        name="name" 
        type="text" 
        :label="__('Full Name (Parent / Guardian)')" 
        :value="old('name')" 
        required 
        autofocus 
        autocomplete="name" 
        placeholder="Jane Doe"
    />

    <!-- Email Address -->
    <x-input 
        name="email" 
        type="email" 
        :label="__('Email Address')" 
        :value="old('email')" 
        required 
        autocomplete="username" 
        placeholder="parent@example.com"
    />

    <!-- Phone Number -->
    <x-input 
        name="phone" 
        type="tel" 
        :label="__('Phone / Mobile Number')" 
        :value="old('phone')" 
        required 
        autocomplete="tel" 
        placeholder="+94 77 123 4567"
        :helper="__('Required for instant SMS alerts and verification')"
    />

    <!-- Password -->
    <div>
        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
            {{ __('Password') }}
        </label>
        <input 
            id="password" 
            name="password" 
            type="password" 
            required 
            autocomplete="new-password"
            placeholder="••••••••"
            class="block w-full px-3.5 py-2.5 rounded-xl text-sm border @error('password') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @else border-slate-300 focus:border-brand-600 focus:ring-brand-600 @enderror shadow-xs transition-colors"
        />
        @error('password')
            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
            {{ __('Confirm Password') }}
        </label>
        <input 
            id="password_confirmation" 
            name="password_confirmation" 
            type="password" 
            required 
            autocomplete="new-password"
            placeholder="••••••••"
            class="block w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-300 focus:border-brand-600 focus:ring-brand-600 shadow-xs transition-colors"
        />
    </div>

    <!-- Notice of Approval -->
    <div class="p-3 bg-brand-50/50 rounded-xl border border-brand-100 text-xs text-slate-600 space-y-1">
        <span class="font-bold text-brand-900">{{ __('Account Verification Notice:') }}</span>
        <p>{{ __('New parent accounts are reviewed and approved by institution administrators before class access is granted.') }}</p>
    </div>

    <!-- Submit Button -->
    <button 
        type="submit" 
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer btn-interactive">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.765Z" />
        </svg>
        {{ __('Register Parent Account') }}
    </button>

    <div class="pt-3 text-center border-t border-slate-100 text-xs text-slate-500">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-700 hover:underline ml-1">
            {{ __('Sign in here') }}
        </a>
    </div>
</form>
@endsection
