<x-layouts.app :title="__('Student Portal Dashboard')">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Student Portal') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Access your lecture notes, attendance QR pass, assignments, and payments (§4, §7.2)') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-xl bg-brand-50 border border-brand-200 text-brand-700 text-xs font-mono font-bold">
                    ID: STU-2026-0042
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Student Identity & Digital Pass Card (§5.3, §7.2) -->
        <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-indigo-950 text-white rounded-3xl p-6 shadow-xl relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-brand-500/10 blur-2xl"></div>
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                    <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center font-bold text-2xl text-accent-400">
                        👨‍🎓
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center justify-center sm:justify-start gap-2">
                            <h2 class="text-xl font-black tracking-tight text-white">Alexander Cross</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                {{ __('Active Pass') }}
                            </span>
                        </div>
                        <p class="text-xs text-brand-200 font-mono">STU-2026-0042 &bull; Computer Science & Advanced Math</p>
                        <p class="text-[11px] text-brand-300">{{ __('Enrolled in 2 Active Institute Classes') }}</p>
                    </div>
                </div>

                <!-- Digital QR Pass Quick Trigger -->
                <div class="bg-white p-3 rounded-2xl shadow-lg shrink-0 text-center">
                    <div class="w-24 h-24 bg-slate-900 rounded-xl flex items-center justify-center text-white font-mono text-[10px] p-2 leading-tight">
                        [QR PASS CODE: STU-2026-0042]
                    </div>
                    <span class="text-[10px] font-bold text-slate-700 mt-1 block">{{ __('Scan at Kiosk') }}</span>
                </div>
            </div>
        </div>

        <!-- Academic Overview Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Enrolled Classes') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">2</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('All fees cleared') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Attendance Rate') }}</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">96.8%</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('31 of 32 sessions attended') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-amber-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Pending Assignments') }}</p>
                <p class="text-2xl font-black text-amber-600 mt-1">1</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Calculus problem set #3') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-purple-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Print Quota (§10)') }}</p>
                <p class="text-2xl font-black text-purple-600 mt-1">3 <span class="text-xs font-normal text-slate-400">/ 5</span></p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Materials collected this month') }}</p>
            </x-card>
        </div>

        <!-- Active Classes & Materials -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <x-card :title="__('My Institute Classes')">
                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-brand-200 transition-colors space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-brand-50 text-brand-700">
                                    {{ __('Advanced Mathematics') }}
                                </span>
                                <span class="text-xs text-slate-500">Prof. Ada Lovelace</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Calculus & Linear Algebra &bull; Hall B</h3>
                            <p class="text-xs text-slate-600">Saturdays 08:30 AM - 11:30 AM &bull; Next session in 3 days</p>
                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">
                                <span class="text-emerald-700 font-semibold text-[11px]">✓ November Fee Paid</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 cursor-pointer">
                                        {{ __('Lecture Notes') }}
                                    </button>
                                    <button type="button" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 cursor-pointer">
                                        {{ __('Submit Assignment') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-brand-200 transition-colors space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700">
                                    {{ __('Computer Science') }}
                                </span>
                                <span class="text-xs text-slate-500">Dr. Alan Turing</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Algorithms & Data Structures &bull; Lab 2</h3>
                            <p class="text-xs text-slate-600">Wednesdays 03:00 PM - 05:00 PM</p>
                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">
                                <span class="text-emerald-700 font-semibold text-[11px]">✓ November Fee Paid</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 cursor-pointer">
                                        {{ __('Lecture Notes') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Sidebar: Payments & Printing Eligibility -->
            <div class="space-y-6">
                <!-- Fee Payment Slip Upload (§11) -->
                <x-card :title="__('Monthly Fees & Receipts')">
                    <div class="space-y-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-slate-900 block">November 2026</span>
                                <span class="text-slate-500 text-[11px]">LKR 15,000 &bull; Approved</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">Paid</span>
                        </div>
                        <button type="button" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 cursor-pointer shadow-xs">
                            + {{ __('Upload Bank Payment Slip') }}
                        </button>
                    </div>
                </x-card>

                <!-- Printing Eligibility Counter Pass (§10) -->
                <x-card :title="__('Printing Counter Eligibility (§10)')">
                    <div class="space-y-2 text-xs text-slate-600">
                        <p>{{ __('Show your digital QR pass at the physical printing counter to collect your hardcopy handouts and past papers.') }}</p>
                        <div class="p-3 rounded-xl bg-purple-50/70 border border-purple-200 text-purple-900 font-medium">
                            <span class="font-bold">Next available pickup:</span> Calculus Tutorial Booklet #4
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
