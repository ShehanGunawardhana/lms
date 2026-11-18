<div class="max-w-5xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Profile & Preferences') }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Manage your personal details, login credentials, language, and notification channels (§1.7)') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
                {{ $user?->roles->first()?->name ?? __('Member') }}
            </span>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $user?->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                {{ ucfirst($user?->status ?? 'active') }}
            </span>
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between animate-fade-in shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Profile Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col sm:flex-row items-center gap-6">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 via-brand-500 to-accent-500 text-white font-black text-2xl flex items-center justify-center shadow-md shadow-brand-500/20 shrink-0">
            {{ strtoupper(substr($name ?: 'U', 0, 1)) }}
        </div>
        <div class="flex-1 text-center sm:text-left space-y-1">
            <h2 class="text-lg font-bold text-slate-900">{{ $user?->name }}</h2>
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-slate-500">
                <span>📧 {{ $user?->email }}</span>
                <span>📱 {{ $user?->phone ?? __('No mobile number linked') }}</span>
                <span>🌐 {{ strtoupper($locale) }}</span>
            </div>
        </div>
        <div class="shrink-0">
            @if ($user?->two_factor_confirmed_at)
                <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ __('2FA Secured') }}
                </span>
            @else
                <a href="{{ route('two-factor.setup') }}" class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 text-xs font-bold flex items-center gap-1.5 transition-colors">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    {{ __('Enable 2FA') }}
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 1. PERSONAL DETAILS -->
        <x-card :title="__('Personal Information')">
            <form wire:submit.prevent="updateProfileInformation(app(\App\Actions\Fortify\UpdateUserProfileInformation::class))" class="space-y-4">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        {{ __('Full Name') }} <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        id="name" 
                        type="text" 
                        wire:model="name" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm border @error('name', 'updateProfileInformation') border-rose-300 @else border-slate-200 @enderror focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                    />
                    @error('name', 'updateProfileInformation')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        {{ __('Email Address') }} <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        id="email" 
                        type="email" 
                        wire:model="email" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm border @error('email', 'updateProfileInformation') border-rose-300 @else border-slate-200 @enderror focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                    />
                    @error('email', 'updateProfileInformation')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        {{ __('Mobile Phone Number') }}
                    </label>
                    <input 
                        id="phone" 
                        type="tel" 
                        wire:model="phone" 
                        placeholder="+94 77 123 4567"
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm border @error('phone', 'updateProfileInformation') border-rose-300 @else border-slate-200 @enderror focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                    />
                    @error('phone', 'updateProfileInformation')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 text-right">
                    <button 
                        type="submit" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 active:scale-[0.98] shadow-xs transition-all cursor-pointer btn-interactive">
                        {{ __('Save Profile') }}
                    </button>
                </div>
            </form>
        </x-card>

        <!-- 2. SECURITY & PASSWORD -->
        <x-card :title="__('Security & Password')">
            <form wire:submit.prevent="updatePassword(app(\App\Actions\Fortify\UpdateUserPassword::class))" class="space-y-4">
                <div>
                    <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        {{ __('Current Password') }} <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        id="current_password" 
                        type="password" 
                        wire:model="current_password" 
                        required 
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm border @error('current_password', 'updatePassword') border-rose-300 @else border-slate-200 @enderror focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                    />
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        {{ __('New Password') }} <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        id="password" 
                        type="password" 
                        wire:model="password" 
                        required 
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm border @error('password', 'updatePassword') border-rose-300 @else border-slate-200 @enderror focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                    />
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        {{ __('Confirm New Password') }} <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        wire:model="password_confirmation" 
                        required 
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm border border-slate-200 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                    />
                </div>

                <div class="pt-2 flex items-center justify-between">
                    <a href="{{ route('two-factor.setup') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">
                        {{ __('Manage 2FA & Recovery Codes →') }}
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 active:scale-[0.98] shadow-xs transition-all cursor-pointer btn-interactive">
                        {{ __('Update Password') }}
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    <!-- 3. LANGUAGE & REGIONAL PREFERENCES -->
    <x-card :title="__('Language & Localization (§15)')">
        <form wire:submit.prevent="updateLocale" class="space-y-4">
            <p class="text-xs text-slate-500">
                {{ __('Select your preferred language for the portal interface, system alerts, and notification templates.') }}
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- English -->
                <label class="flex items-center gap-3 p-3.5 rounded-xl border {{ $locale === 'en' ? 'border-brand-500 bg-brand-50/40 text-brand-950 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }} cursor-pointer transition-colors">
                    <input type="radio" wire:model="locale" value="en" class="text-brand-600 focus:ring-brand-500 w-4 h-4" />
                    <div>
                        <span class="text-xs block font-bold">English (US / UK)</span>
                        <span class="text-[10px] text-slate-500 font-normal">{{ __('Default Institution Language') }}</span>
                    </div>
                </label>

                <!-- Sinhala -->
                <label class="flex items-center gap-3 p-3.5 rounded-xl border {{ $locale === 'si' ? 'border-brand-500 bg-brand-50/40 text-brand-950 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }} cursor-pointer transition-colors">
                    <input type="radio" wire:model="locale" value="si" class="text-brand-600 focus:ring-brand-500 w-4 h-4" />
                    <div>
                        <span class="text-xs block font-bold">සිංහල (Sinhala)</span>
                        <span class="text-[10px] text-slate-500 font-normal">{{ __('Sri Lanka Localized') }}</span>
                    </div>
                </label>

                <!-- Tamil -->
                <label class="flex items-center gap-3 p-3.5 rounded-xl border {{ $locale === 'ta' ? 'border-brand-500 bg-brand-50/40 text-brand-950 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }} cursor-pointer transition-colors">
                    <input type="radio" wire:model="locale" value="ta" class="text-brand-600 focus:ring-brand-500 w-4 h-4" />
                    <div>
                        <span class="text-xs block font-bold">தமிழ் (Tamil)</span>
                        <span class="text-[10px] text-slate-500 font-normal">{{ __('Sri Lanka & Regional') }}</span>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-between pt-2">
                <span class="text-[11px] text-slate-500">
                    {{ __('Campus Timezone:') }} <span class="font-bold text-slate-700 font-mono">{{ current_timezone() }}</span>
                </span>
                <button 
                    type="submit" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 active:scale-[0.98] shadow-xs transition-all cursor-pointer btn-interactive">
                    {{ __('Save Language Preference') }}
                </button>
            </div>
        </form>
    </x-card>

    <!-- 4. NOTIFICATION CHANNEL PREFERENCES (§12) -->
    <x-card :title="__('Notification Preferences (§12)')">
        <div class="space-y-4">
            <p class="text-xs text-slate-500">
                {{ __('Customize which channels (Email, In-App Notification Bell, SMS, or WhatsApp) you wish to receive for each category of institution event.') }}
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                            <th class="py-2.5 px-3">{{ __('Notification Event') }}</th>
                            <th class="py-2.5 px-3 text-center w-24">📧 {{ __('Email') }}</th>
                            <th class="py-2.5 px-3 text-center w-24">🔔 {{ __('In-App') }}</th>
                            <th class="py-2.5 px-3 text-center w-24">📱 {{ __('SMS') }}</th>
                            <th class="py-2.5 px-3 text-center w-24">💬 {{ __('WhatsApp') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($eventTypes as $key => $description)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $description }}</div>
                                    <span class="font-mono text-[10px] text-slate-400">{{ $key }}</span>
                                </td>

                                <!-- Mail -->
                                <td class="py-3 px-3 text-center">
                                    <button 
                                        type="button" 
                                        wire:click="toggleNotification('{{ $key }}', 'mail')"
                                        class="p-1 rounded-lg transition-colors cursor-pointer {{ ($preferences[$key]['mail'] ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </button>
                                </td>

                                <!-- Database -->
                                <td class="py-3 px-3 text-center">
                                    <button 
                                        type="button" 
                                        wire:click="toggleNotification('{{ $key }}', 'database')"
                                        class="p-1 rounded-lg transition-colors cursor-pointer {{ ($preferences[$key]['database'] ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </button>
                                </td>

                                <!-- SMS -->
                                <td class="py-3 px-3 text-center">
                                    <button 
                                        type="button" 
                                        wire:click="toggleNotification('{{ $key }}', 'sms')"
                                        class="p-1 rounded-lg transition-colors cursor-pointer {{ ($preferences[$key]['sms'] ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </button>
                                </td>

                                <!-- WhatsApp -->
                                <td class="py-3 px-3 text-center">
                                    <button 
                                        type="button" 
                                        wire:click="toggleNotification('{{ $key }}', 'whatsapp')"
                                        class="p-1 rounded-lg transition-colors cursor-pointer {{ ($preferences[$key]['whatsapp'] ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-card>
</div>
