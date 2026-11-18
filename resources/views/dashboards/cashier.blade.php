<x-layouts.app :title="__('Cashier & Finance Workspace')">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Cashier & Finance Desk') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Verify student payment slips, issue official receipts, and reconcile fee ledgers (§4, §11)') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-xs transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('Issue Manual Cash Receipt') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Finance KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card class="border-l-4 border-l-amber-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Slips Pending Review') }}</p>
                <p class="text-2xl font-black text-amber-600 mt-1">8</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Requires slip image check') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Today\'s Collections') }}</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">LKR 285,000</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('19 receipts confirmed') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Month to Date') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">LKR 4.2M</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('91.4% fee recovery rate') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-rose-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Rejected Slips') }}</p>
                <p class="text-2xl font-black text-rose-600 mt-1">2</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Unclear deposits') }}</p>
            </x-card>
        </div>

        <!-- Payment Slip Verification Queue (§11) -->
        <x-card :title="__('Submitted Payment Slips (Pending Verification)')">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                            <th class="py-3 px-3">{{ __('Student / Code') }}</th>
                            <th class="py-3 px-3">{{ __('Class & Month') }}</th>
                            <th class="py-3 px-3">{{ __('Amount') }}</th>
                            <th class="py-3 px-3">{{ __('Deposit Slip') }}</th>
                            <th class="py-3 px-3">{{ __('Submitted') }}</th>
                            <th class="py-3 px-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-3">
                                <span class="font-bold text-slate-900 block">Alexander Cross</span>
                                <span class="font-mono text-[10px] text-slate-400">STU-2026-0042</span>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="font-medium text-slate-800 block">Calculus & Linear Algebra</span>
                                <span class="text-[10px] text-slate-400">November 2026</span>
                            </td>
                            <td class="py-3.5 px-3 font-mono font-bold text-slate-900">
                                LKR 15,000.00
                            </td>
                            <td class="py-3.5 px-3">
                                <a href="#" class="text-brand-600 hover:underline font-bold inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                    {{ __('View Slip (BOC Ref #8812)') }}
                                </a>
                            </td>
                            <td class="py-3.5 px-3 text-slate-500">
                                20 mins ago
                            </td>
                            <td class="py-3.5 px-3 text-right space-x-1.5 whitespace-nowrap">
                                <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 cursor-pointer shadow-xs">
                                    {{ __('Verify & Issue Receipt') }}
                                </button>
                                <button type="button" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 cursor-pointer">
                                    {{ __('Reject') }}
                                </button>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-3">
                                <span class="font-bold text-slate-900 block">Samantha Smith (Oliver Smith)</span>
                                <span class="font-mono text-[10px] text-slate-400">NUR-2026-0014</span>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="font-medium text-slate-800 block">Playgroup Term Fee</span>
                                <span class="text-[10px] text-slate-400">November 2026</span>
                            </td>
                            <td class="py-3.5 px-3 font-mono font-bold text-slate-900">
                                LKR 12,500.00
                            </td>
                            <td class="py-3.5 px-3">
                                <a href="#" class="text-brand-600 hover:underline font-bold inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                    {{ __('View Slip (Commercial Ref #4401)') }}
                                </a>
                            </td>
                            <td class="py-3.5 px-3 text-slate-500">
                                1 hour ago
                            </td>
                            <td class="py-3.5 px-3 text-right space-x-1.5 whitespace-nowrap">
                                <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 cursor-pointer shadow-xs">
                                    {{ __('Verify & Issue Receipt') }}
                                </button>
                                <button type="button" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 cursor-pointer">
                                    {{ __('Reject') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
