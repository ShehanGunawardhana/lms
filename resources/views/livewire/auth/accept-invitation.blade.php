<div class="space-y-6">
    <!-- Case 1: Already Accepted -->
    @if ($isAccepted)
        <div class="text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 shadow-inner">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900">{{ __('Invitation Already Accepted') }}</h3>
                <p class="text-xs text-slate-500 mt-1">
                    {{ __('This invitation link has already been used to activate the account. Please sign in with your credentials.') }}
                </p>
            </div>
            <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition-colors">
                {{ __('Proceed to Sign In') }} &rarr;
            </a>
        </div>
    @elseif ($isExpired)
        <!-- Case 2: Expired -->
        <div class="text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shadow-inner">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900">{{ __('Invitation Expired') }}</h3>
                <p class="text-xs text-slate-500 mt-1">
                    {{ __('This invitation link has passed its 7-day validity window. Please contact your institution administrator to request a new link.') }}
                </p>
            </div>
            <a href="{{ url('/') }}" class="inline-flex text-xs font-bold text-brand-600 hover:underline">
                {{ __('Return to Homepage') }}
            </a>
        </div>
    @else
        <!-- Case 3: Valid Invitation -->
        <div class="space-y-4">
            <!-- Faculty Welcome Header -->
            <div class="p-4 bg-brand-50/70 border border-brand-100 rounded-2xl space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-700">{{ __('Faculty Invitation') }}</span>
                <h3 class="text-sm font-black text-brand-950">{{ $teacherName }}</h3>
                <p class="text-xs text-slate-600">{{ $teacherEmail }} &bull; {{ $institutionName }}</p>
            </div>

            <form wire:submit="accept" class="space-y-4">
                <!-- New Password -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1">
                        {{ __('Choose Your Password') }}
                    </label>
                    <input 
                        id="password" 
                        type="password" 
                        wire:model="password" 
                        placeholder="••••••••" 
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border @error('password') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @else border-slate-300 focus:border-brand-600 focus:ring-brand-600 @enderror shadow-xs"
                    />
                    @error('password') <span class="text-xs text-rose-600 font-medium">{{ $message }}</span> @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">
                        {{ __('Confirm Password') }}
                    </label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        wire:model="password_confirmation" 
                        placeholder="••••••••" 
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-300 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                    />
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-500 space-y-1">
                    <span class="font-bold text-slate-700">{{ __('Security Standards:') }}</span>
                    <ul class="list-disc list-inside space-y-0.5">
                        <li>{{ __('Minimum 8 characters length.') }}</li>
                        <li>{{ __('Must include a combination of letters and numbers.') }}</li>
                    </ul>
                </div>

                <button 
                    type="submit" 
                    wire:loading.attr="disabled"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 shadow-md shadow-brand-500/20 active:scale-[0.98] transition-all cursor-pointer btn-interactive">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>{{ __('Activate Faculty Account & Continue') }}</span>
                </button>
            </form>
        </div>
    @endif
</div>
