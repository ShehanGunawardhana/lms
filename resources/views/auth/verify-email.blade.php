@extends('layouts.guest', [
    'title' => __('Verify Email'),
    'heading' => __('Verify Your Email Address'),
    'subheading' => __('Please check your inbox and verify your email before proceeding')
])

@section('content')
<div class="space-y-4">
    @if (session('status') == 'verification-link-sent')
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
            {{ __('A new verification link has been sent to your registered email address.') }}
        </div>
    @endif

    <p class="text-xs text-slate-600 leading-relaxed">
        {{ __('Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent you. If you did not receive it, we can send another.') }}
    </p>

    <div class="flex flex-col gap-3 pt-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button 
                type="submit" 
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer btn-interactive">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button 
                type="submit" 
                class="w-full inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:underline cursor-pointer">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</div>
@endsection
