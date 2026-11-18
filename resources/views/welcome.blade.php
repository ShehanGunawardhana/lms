<x-layouts.public :title="$institution ? $institution->name . ' • ' . __('Educational Institution & Nursery Portal') : config('app.name', 'Educational Institution & Nursery Portal')">
    <div class="relative overflow-hidden">
        <!-- Background Gradient Glows -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-b from-brand-100/50 via-accent-50/20 to-transparent pointer-events-none -z-10 blur-3xl"></div>

        <!-- Hero Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-16 lg:pt-20 lg:pb-24">
            <div class="text-center max-w-3xl mx-auto space-y-6">
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-50 border border-brand-200/80 text-brand-700 text-xs font-semibold shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ __('Admissions & Learning Portal Open') }} &bull; {{ now()->year }}</span>
                </div>

                <!-- Main Branding Heading -->
                <h1 class="text-4xl sm:text-6xl font-black text-slate-900 tracking-tight leading-[1.1]">
                    @if ($institution)
                        <span class="text-slate-900 block">{{ $institution->name }}</span>
                    @else
                        <span class="text-slate-900 block">{{ config('app.name', 'EduPortal Academy') }}</span>
                    @endif
                    <span class="bg-gradient-to-r from-brand-600 via-indigo-600 to-accent-600 bg-clip-text text-transparent block mt-2 text-3xl sm:text-5xl">
                        {{ __('Unified Education & Nursery Excellence') }}
                    </span>
                </h1>

                <!-- Hero Subtitle -->
                <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                    {{ __('An all-in-one institutional campus platform and early childhood development portal. Connect educators, students, and families through real-time attendance, digital student passes, secure fee reconciliation, and interactive lesson drops.') }}
                </p>

                <!-- Call to Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-500/30 transition-all btn-interactive inline-flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                            {{ __('Open My Workspace') }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-500/30 transition-all btn-interactive inline-flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM3 20a6 6 0 0 1 12 0v1H3v-1Z"/></svg>
                            {{ __('Register Parent Account') }}
                        </a>
                        <a href="#programs" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs transition-all inline-flex items-center justify-center gap-2">
                            {{ __('Explore Programs & Faculty') }}
                        </a>
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl text-sm font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors inline-flex items-center justify-center">
                            {{ __('Staff & Student Login') }} &rarr;
                        </a>
                    @endauth
                </div>

                <!-- Trust Metrics Bar -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-8 border-t border-slate-200/80 mt-8">
                    <div class="p-3 text-center">
                        <p class="text-2xl font-black text-slate-900">1,200+</p>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">{{ __('Active Learners') }}</p>
                    </div>
                    <div class="p-3 text-center">
                        <p class="text-2xl font-black text-emerald-600">98.5%</p>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">{{ __('Attendance Precision') }}</p>
                    </div>
                    <div class="p-3 text-center">
                        <p class="text-2xl font-black text-accent-600">100%</p>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">{{ __('Private Storage') }}</p>
                    </div>
                    <div class="p-3 text-center">
                        <p class="text-2xl font-black text-amber-600">24 / 7</p>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">{{ __('Offline-Ready PWA') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dynamic Module Pillars (§6, §7) -->
        <section id="programs" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ __('Designed for Modern Campus & Nursery Life') }}</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">{{ __('Dual-module infrastructure adapting seamlessly to early childhood daycares, college campuses, and tuition institutes.') }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Nursery Module Card -->
                @if (config('portal.modules.nursery', true))
                    <div class="p-8 rounded-3xl bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-white border border-amber-200 shadow-sm relative overflow-hidden group hover:border-amber-300 transition-all card-interactive">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-amber-500/30 mb-5">
                            🧸
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 inline-block mb-3">
                            {{ __('Nursery & Kindergarten Module') }}
                        </span>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ __('Early Years Milestones & Parent Portal') }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed mt-2">
                            {{ __('Keep parents intimately connected with their child\'s daily journey. Video activities, photo consent protections, sensory assignments, milestone journals, and direct teacher sticker reviews.') }}
                        </p>

                        <ul class="space-y-2 mt-5 text-xs text-slate-700 font-medium">
                            <li class="flex items-center gap-2">
                                <span class="text-amber-600 font-bold">✓</span>
                                <span>{{ __('YouTube video activities streamed with privacy enhancements') }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-600 font-bold">✓</span>
                                <span>{{ __('Encrypted medical allergies & authorized pickup verification') }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-600 font-bold">✓</span>
                                <span>{{ __('Parent submission upload before deadlines with instant teacher review') }}</span>
                            </li>
                        </ul>

                        <div class="mt-6 pt-5 border-t border-amber-100">
                            <a href="{{ route('register') }}" class="text-xs font-bold text-amber-800 hover:text-amber-900 inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                {{ __('Enroll your child in Nursery') }} &rarr;
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Institute Module Card -->
                @if (config('portal.modules.institute', true))
                    <div class="p-8 rounded-3xl bg-gradient-to-br from-brand-600/10 via-accent-500/5 to-white border border-brand-200 shadow-sm relative overflow-hidden group hover:border-brand-300 transition-all card-interactive">
                        <div class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-brand-600/30 mb-5">
                            🎓
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-brand-100 text-brand-900 inline-block mb-3">
                            {{ __('Tuition & Academic Institute Module') }}
                        </span>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ __('Academic Classes, Passes & Billing Desk') }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed mt-2">
                            {{ __('Full-fledged operations for secondary schools, tuition colleges, and universities. Student QR passes, gate attendance kiosks, bank slip verification, fee-gated lecture drops, and print quota tracking.') }}
                        </p>

                        <ul class="space-y-2 mt-5 text-xs text-slate-700 font-medium">
                            <li class="flex items-center gap-2">
                                <span class="text-brand-600 font-bold">✓</span>
                                <span>{{ __('Fast camera & barcode attendance scan at campus stations (§8)') }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-brand-600 font-bold">✓</span>
                                <span>{{ __('Upload bank slip for one-click manual cashier reconciliation (§11)') }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-brand-600 font-bold">✓</span>
                                <span>{{ __('Print counter kiosk with strict quota and duplicate deterrent (§10)') }}</span>
                            </li>
                        </ul>

                        <div class="mt-6 pt-5 border-t border-brand-100">
                            <a href="{{ route('login') }}" class="text-xs font-bold text-brand-800 hover:text-brand-900 inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                {{ __('Browse academic classes & faculty') }} &rarr;
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <!-- Platform Capabilities Grid -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ __('Built for Speed, Security & Simplicity') }}</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">{{ __('Every capability adheres to strict institutional privacy, multi-role security, and mobile-first design.') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-brand-200 transition-all card-interactive">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold mb-4">
                        📷
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">{{ __('Digital Pass & QR Attendance') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">
                        {{ __('Unique per-institution student codes with dynamic QR cards. Marshals scan via camera or wedge reader in real time.') }}
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-brand-200 transition-all card-interactive">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold mb-4">
                        💳
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">{{ __('Bank Slip Reconciliations') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">
                        {{ __('Parents and students upload bank receipts. Cashiers review image attachments and issue official PDF payment receipts.') }}
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-brand-200 transition-all card-interactive">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold mb-4">
                        🖨️
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">{{ __('Print Counter Quotas') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">
                        {{ __('Dedicated kiosk interface for printing staff. Verifies student fee clearance and prevents unauthorized handout duplicates.') }}
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-brand-200 transition-all card-interactive">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold mb-4">
                        🛡️
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">{{ __('Child Safety & Privacy Vault') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">
                        {{ __('Encrypted medical allergies, photo consent flags, and quarantine pipelines inspecting every uploaded document.') }}
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-brand-200 transition-all card-interactive">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold mb-4">
                        📱
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">{{ __('Offline PWA Architecture') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">
                        {{ __('Phone-first responsive layouts that install directly to mobile home screens with service-worker offline caching.') }}
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-brand-200 transition-all card-interactive">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold mb-4">
                        🔐
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">{{ __('Hardware Two-Factor & Audits') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">
                        {{ __('Mandatory TOTP 2FA for administrative accounts, complete audit logging with causer context, and role-based policies.') }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Final Call to Action -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-indigo-950 rounded-3xl p-8 sm:p-12 text-white text-center shadow-xl relative overflow-hidden">
                <div class="max-w-2xl mx-auto space-y-4 relative z-10">
                    <h3 class="text-2xl sm:text-3xl font-black tracking-tight text-white">{{ __('Start Your Educational Journey Today') }}</h3>
                    <p class="text-xs sm:text-sm text-brand-200 leading-relaxed">
                        {{ __('Whether registering for kindergarten or enrolling in advanced secondary classes, our portal provides the transparent tools your family needs.') }}
                    </p>
                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-bold text-brand-950 bg-accent-400 hover:bg-accent-300 transition-all shadow-md">
                            {{ __('Register New Parent Account') }}
                        </a>
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-white/20 transition-all border border-white/20">
                            {{ __('Access Campus Portal') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
