@extends('layouts.guest', [
    'title' => __('Awaiting Administrator Approval'),
    'heading' => __('Registration Under Review'),
    'subheading' => __('Your account registration has been submitted and is awaiting administrator verification')
])

@section('content')
<div class="space-y-5 text-center">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 shadow-inner">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
    </div>

    <div class="space-y-2 text-left bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs text-slate-600 leading-relaxed">
        <p class="font-bold text-slate-800">{{ __('What happens next?') }}</p>
        <ul class="list-disc list-inside space-y-1">
            <li>{{ __('Our administrative staff verifies your child and guardian credentials.') }}</li>
            <li>{{ __('You will receive an instant notification (SMS / Email) as soon as your account is approved.') }}</li>
            <li>{{ __('Once approved, you will have immediate access to your parent dashboard, learning activities, and child tracking.') }}</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button 
            type="submit" 
            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 active:scale-[0.98] transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
            {{ __('Sign Out for Now') }}
        </button>
    </form>
</div>
@endsection
