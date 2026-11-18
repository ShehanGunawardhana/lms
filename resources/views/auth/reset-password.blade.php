@extends('layouts.guest', [
    'title' => __('Set New Password'),
    'heading' => __('Reset Your Password'),
    'subheading' => __('Choose a strong new password for your account')
])

@section('content')
<form method="POST" action="{{ route('password.update') }}" class="space-y-4">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <x-input 
        name="email" 
        type="email" 
        :label="__('Email Address')" 
        :value="old('email', $request->email)" 
        required 
        autofocus 
    />

    <div>
        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
            {{ __('New Password') }}
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

    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
            {{ __('Confirm New Password') }}
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

    <button 
        type="submit" 
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer btn-interactive">
        {{ __('Reset Password') }}
    </button>
</form>
@endsection
