@extends('layouts.guest', [
    'title' => __('Two-Factor Authentication'),
    'heading' => __('Two-Factor Security Verification'),
    'subheading' => __('Confirm access to your account using an authentication code or emergency recovery code')
])

@section('content')
<div x-data="{ recovery: false }" class="space-y-4">
    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
        @csrf

        <div x-show="!recovery">
            <x-input 
                name="code" 
                type="text" 
                :label="__('6-Digit Authentication Code')" 
                placeholder="123456" 
                autofocus 
                autocomplete="one-time-code" 
                inputmode="numeric" 
            />
        </div>

        <div x-show="recovery" x-cloak>
            <x-input 
                name="recovery_code" 
                type="text" 
                :label="__('Emergency Recovery Code')" 
                placeholder="abcdef-98765" 
                autocomplete="one-time-code" 
            />
        </div>

        <button 
            type="submit" 
            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer btn-interactive">
            {{ __('Verify & Continue') }}
        </button>

        <div class="pt-3 text-center border-t border-slate-100 text-xs text-slate-500">
            <button 
                type="button" 
                x-show="!recovery" 
                @click="recovery = true; $nextTick(() => { $refs.recoveryCodeInput?.focus() })" 
                class="font-bold text-brand-600 hover:text-brand-700 hover:underline cursor-pointer">
                {{ __('Use an emergency recovery code instead') }}
            </button>
            <button 
                type="button" 
                x-show="recovery" 
                x-cloak 
                @click="recovery = false" 
                class="font-bold text-brand-600 hover:text-brand-700 hover:underline cursor-pointer">
                {{ __('Use an authentication code instead') }}
            </button>
        </div>
    </form>
</div>
@endsection
