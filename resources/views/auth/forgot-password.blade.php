@extends('layouts.guest', [
    'title' => __('Reset Password'),
    'heading' => __('Forgot Password'),
    'subheading' => __('Enter your email address and we will send you a password reset link')
])

@section('content')
<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf

    @if (session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <x-input 
        name="email" 
        type="email" 
        :label="__('Email Address')" 
        :value="old('email')" 
        required 
        autofocus 
        placeholder="you@institution.edu"
    />

    <button 
        type="submit" 
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer btn-interactive">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
        </svg>
        {{ __('Send Reset Link') }}
    </button>

    <div class="pt-3 text-center border-t border-slate-100 text-xs text-slate-500">
        <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-700 hover:underline">
            {{ __('Back to Sign In') }}
        </a>
    </div>
</form>
@endsection
