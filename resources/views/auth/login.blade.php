@extends('layouts.guest', [
    'title' => __('Sign In'),
    'heading' => __('Sign in to your account'),
    'subheading' => __('Enter your email address and password to continue')
])

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    @if (session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <!-- Email Address -->
    <x-input 
        name="email" 
        type="email" 
        :label="__('Email Address')" 
        :value="old('email')" 
        required 
        autofocus 
        autocomplete="username" 
        placeholder="you@institution.edu"
    />

    <!-- Password -->
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-sm font-semibold text-slate-700">
                {{ __('Password') }}
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>
        <input 
            id="password" 
            name="password" 
            type="password" 
            required 
            autocomplete="current-password"
            placeholder="••••••••"
            class="block w-full px-3.5 py-2.5 rounded-xl text-sm border @error('password') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @else border-slate-300 focus:border-brand-600 focus:ring-brand-600 @enderror shadow-xs transition-colors"
        />
        @error('password')
            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Remember Me -->
    <div class="flex items-center">
        <input 
            id="remember_me" 
            name="remember" 
            type="checkbox" 
            class="h-4 w-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300 transition-colors"
        />
        <label for="remember_me" class="ml-2 block text-xs font-medium text-slate-600">
            {{ __('Remember me on this device') }}
        </label>
    </div>

    <!-- Submit Button -->
    <button 
        type="submit" 
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer btn-interactive">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
        </svg>
        {{ __('Sign In') }}
    </button>

    @if (Route::has('register'))
        <div class="pt-3 text-center border-t border-slate-100 text-xs text-slate-500">
            {{ __("Don't have an account yet?") }}
            <a href="{{ route('register') }}" class="font-bold text-brand-600 hover:text-brand-700 hover:underline ml-1">
                {{ __('Register here') }}
            </a>
        </div>
    @endif
</form>
@endsection
