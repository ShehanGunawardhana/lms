@extends('layouts.guest', [
    'title' => __('Parent & Child Registration'),
    'heading' => __('Create Parent Account'),
    'subheading' => __('Register parent account and submit child details for nursery intake (§5.2)')
])

@section('content')
<form method="POST" action="{{ route('register') }}" class="space-y-6" x-data="{
    hasChild: true,
    photoConsent: true,
    termsConsent: false,
    safetyConsent: false,
}">
    @csrf

    <!-- SECTION 1: PARENT / GUARDIAN INFORMATION -->
    <div class="space-y-4">
        <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
            <span class="w-6 h-6 rounded-lg bg-brand-50 border border-brand-200 text-brand-700 flex items-center justify-center text-xs font-black">1</span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">{{ __('Parent / Guardian Details') }}</h3>
        </div>

        <!-- Full Name -->
        <x-input 
            name="name" 
            type="text" 
            :label="__('Parent / Guardian Full Name')" 
            :value="old('name')" 
            required 
            autofocus 
            autocomplete="name" 
            placeholder="Jane Doe"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                :label="__('Mobile Phone Number')" 
                :value="old('phone')" 
                required 
                autocomplete="tel" 
                placeholder="+94 77 123 4567"
                :helper="__('Used for SMS verification and nursery emergency calls')"
            />
        </div>

        <!-- Relationship to Child -->
        <div>
            <label for="relationship" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                {{ __('Relationship to Child') }} <span class="text-rose-500">*</span>
            </label>
            <select 
                id="relationship" 
                name="relationship" 
                required
                class="block w-full px-3.5 py-2.5 rounded-xl text-sm border @error('relationship') border-rose-300 focus:border-rose-500 @else border-slate-300 focus:border-brand-600 @enderror focus:ring-brand-600 shadow-xs transition-colors bg-white">
                <option value="mother" @selected(old('relationship') === 'mother')>{{ __('Mother') }}</option>
                <option value="father" @selected(old('relationship') === 'father')>{{ __('Father') }}</option>
                <option value="legal_guardian" @selected(old('relationship', 'legal_guardian') === 'legal_guardian')>{{ __('Legal Guardian') }}</option>
                <option value="grandparent" @selected(old('relationship') === 'grandparent')>{{ __('Grandparent') }}</option>
                <option value="other" @selected(old('relationship') === 'other')>{{ __('Other') }}</option>
            </select>
            @error('relationship')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- SECTION 2: CHILD DETAILS -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center text-xs font-black">2</span>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">{{ __('Child Details (Nursery)') }}</h3>
            </div>
            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-[10px] font-black text-amber-900 uppercase tracking-wider">{{ __('Nursery Wing') }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Child First Name -->
            <x-input 
                name="child_first_name" 
                type="text" 
                :label="__('Child First Name')" 
                :value="old('child_first_name')" 
                required 
                placeholder="Leo"
            />

            <!-- Child Last Name -->
            <x-input 
                name="child_last_name" 
                type="text" 
                :label="__('Child Last Name')" 
                :value="old('child_last_name')" 
                required 
                placeholder="Doe"
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Child Date of Birth -->
            <x-input 
                name="child_dob" 
                type="date" 
                :label="__('Date of Birth')" 
                :value="old('child_dob')" 
                required 
                :helper="__('Must be born before today')"
            />

            <!-- Child Gender -->
            <div>
                <label for="child_gender" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    {{ __('Child Gender') }}
                </label>
                <select 
                    id="child_gender" 
                    name="child_gender" 
                    class="block w-full px-3.5 py-2.5 rounded-xl text-sm border @error('child_gender') border-rose-300 focus:border-rose-500 @else border-slate-300 focus:border-brand-600 @enderror focus:ring-brand-600 shadow-xs transition-colors bg-white">
                    <option value="">{{ __('Select Gender (Optional)') }}</option>
                    <option value="male" @selected(old('child_gender') === 'male')>{{ __('Male') }}</option>
                    <option value="female" @selected(old('child_gender') === 'female')>{{ __('Female') }}</option>
                    <option value="other" @selected(old('child_gender') === 'other')>{{ __('Other / Prefer not to say') }}</option>
                </select>
                @error('child_gender')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Nursery Age Group -->
        <div>
            <label for="nursery_group" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                {{ __('Target Nursery Age Group') }} <span class="text-rose-500">*</span>
            </label>
            <select 
                id="nursery_group" 
                name="nursery_group" 
                required
                class="block w-full px-3.5 py-2.5 rounded-xl text-sm border @error('nursery_group') border-rose-300 focus:border-rose-500 @else border-slate-300 focus:border-brand-600 @enderror focus:ring-brand-600 shadow-xs transition-colors bg-white">
                <option value="toddler" @selected(old('nursery_group') === 'toddler')>{{ __('Toddler / Infant (1.0 - 2.0 yrs)') }}</option>
                <option value="playgroup" @selected(old('nursery_group') === 'playgroup')>{{ __('Playgroup (2.0 - 3.0 yrs)') }}</option>
                <option value="nursery" @selected(old('nursery_group', 'nursery') === 'nursery')>{{ __('Nursery (3.0 - 4.0 yrs)') }}</option>
                <option value="pre_kg" @selected(old('nursery_group') === 'pre_kg')>{{ __('Pre-Kindergarten / LKG (4.0 - 5.0 yrs)') }}</option>
                <option value="kg" @selected(old('nursery_group') === 'kg')>{{ __('Kindergarten / UKG (5.0+ yrs)') }}</option>
            </select>
            @error('nursery_group')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Allergies / Medical Notes (Encrypted Storage) -->
        <div>
            <label for="medical_notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                <span>{{ __('Medical & Allergy Notes') }}</span>
                <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                    {{ __('Encrypted at rest (§14)') }}
                </span>
            </label>
            <textarea 
                id="medical_notes" 
                name="medical_notes" 
                rows="2" 
                placeholder="{{ __('List any allergies, medications, or special medical instructions...') }}"
                class="block w-full px-3.5 py-2 rounded-xl text-sm border @error('medical_notes') border-rose-300 focus:border-rose-500 @else border-slate-300 focus:border-brand-600 @enderror focus:ring-brand-600 shadow-xs transition-colors">{{ old('medical_notes') }}</textarea>
            @error('medical_notes')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Dietary Requirements -->
        <div>
            <label for="dietary_needs" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                {{ __('Dietary Requirements (Optional)') }}
            </label>
            <input 
                id="dietary_needs" 
                name="dietary_needs" 
                type="text" 
                value="{{ old('dietary_needs') }}" 
                placeholder="{{ __('e.g., Vegetarian, Halal, Lactose-free, No nuts') }}"
                class="block w-full px-3.5 py-2 rounded-xl text-sm border @error('dietary_needs') border-rose-300 focus:border-rose-500 @else border-slate-300 focus:border-brand-600 @enderror focus:ring-brand-600 shadow-xs transition-colors"
            />
            @error('dietary_needs')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- SECTION 3: CONSENT & PRIVACY POLICY (§5.2, §14) -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
            <span class="w-6 h-6 rounded-lg bg-teal-50 border border-teal-200 text-teal-700 flex items-center justify-center text-xs font-black">3</span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">{{ __('Parental Consents & Authorizations') }}</h3>
        </div>

        <div class="space-y-3">
            <!-- Terms Consent -->
            <label class="flex items-start gap-3 p-3 rounded-xl border @error('consent_terms') border-rose-300 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 hover:bg-slate-50 @enderror cursor-pointer transition-colors">
                <input 
                    type="checkbox" 
                    name="consent_terms" 
                    value="1" 
                    required 
                    @checked(old('consent_terms'))
                    class="mt-0.5 rounded text-brand-600 focus:ring-brand-500 border-slate-300 w-4 h-4 cursor-pointer"
                />
                <span class="text-xs text-slate-700 leading-relaxed">
                    <span class="font-bold text-slate-900">{{ __('I agree to the Terms of Service & Nursery Policies') }}</span> <span class="text-rose-500">*</span>
                    <br>
                    <span class="text-slate-500">{{ __('I agree to abide by the institution admission, attendance, fee schedule, and operational guidelines.') }}</span>
                </span>
            </label>
            @error('consent_terms')
                <p class="text-xs text-rose-600 font-medium px-1">{{ $message }}</p>
            @enderror

            <!-- Child Safety & Code of Conduct Consent -->
            <label class="flex items-start gap-3 p-3 rounded-xl border @error('consent_child_safety') border-rose-300 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 hover:bg-slate-50 @enderror cursor-pointer transition-colors">
                <input 
                    type="checkbox" 
                    name="consent_child_safety" 
                    value="1" 
                    required 
                    @checked(old('consent_child_safety'))
                    class="mt-0.5 rounded text-brand-600 focus:ring-brand-500 border-slate-300 w-4 h-4 cursor-pointer"
                />
                <span class="text-xs text-slate-700 leading-relaxed">
                    <span class="font-bold text-slate-900">{{ __('Child Protection & Safety Policy Agreement') }}</span> <span class="text-rose-500">*</span>
                    <br>
                    <span class="text-slate-500">{{ __('I acknowledge the nursery child safeguarding policy, emergency care protocol, and authorized pickup rules.') }}</span>
                </span>
            </label>
            @error('consent_child_safety')
                <p class="text-xs text-rose-600 font-medium px-1">{{ $message }}</p>
            @enderror

            <!-- Photo & Media Release Consent (Explicit checkbox) -->
            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-brand-200 bg-brand-50/40 hover:bg-brand-50/70 cursor-pointer transition-colors">
                <input 
                    type="checkbox" 
                    name="consent_photo" 
                    value="1" 
                    @checked(old('consent_photo', true))
                    class="mt-0.5 rounded text-brand-600 focus:ring-brand-500 border-brand-300 w-4 h-4 cursor-pointer"
                />
                <span class="text-xs text-slate-700 leading-relaxed">
                    <span class="font-bold text-brand-950 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                        {{ __('Educational Activity Photo & Media Release Consent') }}
                    </span>
                    <span class="text-slate-600 block mt-0.5">
                        {{ __('I consent to photos and videos of my child participating in daily learning activities, art projects, and milestone moments being captured and shared securely within the private Parent Portal.') }}
                    </span>
                    <span class="text-[11px] text-brand-700 font-semibold block mt-1">
                        {{ __('Note: Photos are never published publicly without separate explicit written permission (§14).') }}
                    </span>
                </span>
            </label>
            @error('consent_photo')
                <p class="text-xs text-rose-600 font-medium px-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- SECTION 4: SECURITY & ACCOUNT PASSWORD -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
            <span class="w-6 h-6 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 flex items-center justify-center text-xs font-black">4</span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">{{ __('Portal Password') }}</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    {{ __('Password') }} <span class="text-rose-500">*</span>
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
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    {{ __('Confirm Password') }} <span class="text-rose-500">*</span>
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
        </div>
    </div>

    <!-- Approval Process Notice -->
    <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-2xl flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <div class="text-xs text-amber-900 space-y-1">
            <p class="font-bold">{{ __('Administrator Approval Process (§5.2)') }}</p>
            <p class="text-amber-800 leading-relaxed">
                {{ __('Upon submitting this form, your parent account is set to "Pending". Nursery administrators will verify child details and assign classroom placement before full portal access is activated.') }}
            </p>
        </div>
    </div>

    <!-- Submit Button -->
    <button 
        type="submit" 
        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer btn-interactive">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.765Z" />
        </svg>
        {{ __('Register Parent Account & Submit for Review') }}
    </button>

    <div class="pt-3 text-center border-t border-slate-100 text-xs text-slate-500">
        {{ __('Already registered?') }}
        <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-700 hover:underline ml-1">
            {{ __('Sign in to your account') }}
        </a>
    </div>
</form>
@endsection
