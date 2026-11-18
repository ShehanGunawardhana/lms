<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Teacher & Lecturer Invitations') }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Register new academic faculty and send secure password activation links via email or SMS (§5.1)') }}
            </p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Admin Dashboard') }}
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Last Invite URL Quick-Share Card -->
    @if ($lastInviteUrl)
        <div class="p-4 bg-brand-50 border border-brand-200 rounded-2xl space-y-2 animate-fade-in shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-brand-950 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                    {{ __('Direct Activation Link (Shareable)') }}
                </span>
                <span class="text-[11px] text-brand-700 font-medium">{{ __('Valid for 7 days') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <input 
                    type="text" 
                    readonly 
                    value="{{ $lastInviteUrl }}" 
                    class="w-full text-xs font-mono bg-white border border-brand-200 rounded-xl px-3 py-2 text-slate-700 select-all"
                />
            </div>
        </div>
    @endif

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Invite Form Card -->
        <div class="lg:col-span-1">
            <x-card :title="__('New Teacher Registration')">
                <form wire:submit="invite" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 mb-1">{{ __('Full Name') }}</label>
                        <input 
                            id="name" 
                            type="text" 
                            wire:model="name" 
                            placeholder="Dr. Sarah Perera" 
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-300 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                        />
                        @error('name') <span class="text-xs text-rose-600 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1">{{ __('Email Address') }}</label>
                        <input 
                            id="email" 
                            type="email" 
                            wire:model="email" 
                            placeholder="sarah.perera@campus.edu" 
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-300 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                        />
                        @error('email') <span class="text-xs text-rose-600 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">
                            {{ __('Phone Number') }}
                            <span class="font-normal text-slate-400">({{ __('optional unless SMS delivery selected') }})</span>
                        </label>
                        <input 
                            id="phone" 
                            type="tel" 
                            wire:model="phone" 
                            placeholder="+94 77 123 4567" 
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-300 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                        />
                        @error('phone') <span class="text-xs text-rose-600 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Delivery Channel -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Delivery Channel') }}</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex items-center justify-center p-2.5 border rounded-xl cursor-pointer text-xs font-semibold text-center {{ $channel === 'email' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                <input type="radio" wire:model.live="channel" value="email" class="sr-only">
                                {{ __('Email') }}
                            </label>
                            <label class="flex items-center justify-center p-2.5 border rounded-xl cursor-pointer text-xs font-semibold text-center {{ $channel === 'sms' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                <input type="radio" wire:model.live="channel" value="sms" class="sr-only">
                                {{ __('SMS') }}
                            </label>
                            <label class="flex items-center justify-center p-2.5 border rounded-xl cursor-pointer text-xs font-semibold text-center {{ $channel === 'both' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                <input type="radio" wire:model.live="channel" value="both" class="sr-only">
                                {{ __('Both') }}
                            </label>
                        </div>
                    </div>

                    <!-- Subjects / Specializations -->
                    <div>
                        <label for="subjects" class="block text-xs font-bold text-slate-700 mb-1">
                            {{ __('Teaching Subjects / Dept.') }}
                            <span class="font-normal text-slate-400">({{ __('comma-separated') }})</span>
                        </label>
                        <input 
                            id="subjects" 
                            type="text" 
                            wire:model="subjects" 
                            placeholder="Mathematics, Physics" 
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-300 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                        />
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 shadow-md shadow-brand-500/20 active:scale-[0.98] transition-all cursor-pointer btn-interactive">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                        <span>{{ __('Send Faculty Invitation') }}</span>
                    </button>
                </form>
            </x-card>
        </div>

        <!-- Invitations List Table -->
        <div class="lg:col-span-2">
            <x-card :title="__('Faculty Invitation History')">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 text-slate-500 uppercase tracking-wider font-semibold">
                                <th class="py-3 px-3.5">{{ __('Teacher') }}</th>
                                <th class="py-3 px-3.5">{{ __('Channel') }}</th>
                                <th class="py-3 px-3.5">{{ __('Status') }}</th>
                                <th class="py-3 px-3.5">{{ __('Expires') }}</th>
                                <th class="py-3 px-3.5 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse ($invitations as $inv)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-3.5">
                                        <div class="font-bold text-slate-900">{{ $inv->user?->name ?? '—' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $inv->sent_to }}</div>
                                    </td>
                                    <td class="py-3 px-3.5 uppercase font-mono text-[11px]">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                            {{ $inv->channel }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3.5">
                                        @if ($inv->isAccepted())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ __('Accepted') }}
                                            </span>
                                        @elseif ($inv->isExpired())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                {{ __('Expired') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                {{ __('Pending') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3.5 text-slate-500">
                                        {{ format_portal_datetime($inv->expires_at, 'M d, Y H:i') }}
                                    </td>
                                    <td class="py-3 px-3.5 text-right space-x-2">
                                        @if (! $inv->isAccepted())
                                            <button 
                                                type="button" 
                                                wire:click="resend({{ $inv->id }})" 
                                                wire:confirm="{{ __('Resend invitation with a new 7-day expiration link?') }}"
                                                class="font-bold text-brand-600 hover:text-brand-700 hover:underline">
                                                {{ __('Resend') }}
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="revoke({{ $inv->id }})" 
                                                wire:confirm="{{ __('Revoke this invitation link?') }}"
                                                class="font-bold text-rose-600 hover:text-rose-700 hover:underline">
                                                {{ __('Revoke') }}
                                            </button>
                                        @else
                                            <span class="text-slate-400 text-[11px]">{{ __('Active User') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-500">
                                        {{ __('No invitations sent yet. Use the form to invite your first faculty member.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $invitations->links() }}
                </div>
            </x-card>
        </div>
    </div>
</div>
