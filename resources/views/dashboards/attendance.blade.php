<x-layouts.app :title="__('Attendance Marshal Workspace')">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Attendance Marshal Workspace') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Operate the physical scanning post, track campus arrivals, and log attendance (§4, §8)') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('station.attendance') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-500/20 inline-flex items-center gap-1.5 transition-all btn-interactive">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/></svg>
                    {{ __('Launch Live Scanner Kiosk (§8)') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Attendance Marshal Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Present Today') }}</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">428</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('On-time check-ins') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-amber-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Late Arrivals') }}</p>
                <p class="text-2xl font-black text-amber-600 mt-1">14</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Arrived after grace period') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-purple-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Active Sessions') }}</p>
                <p class="text-2xl font-black text-purple-600 mt-1">6</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Currently checking in') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-rose-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Blocked / Unpaid') }}</p>
                <p class="text-2xl font-black text-rose-600 mt-1">3</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Referred to Cashier Desk') }}</p>
            </x-card>
        </div>

        <!-- Real-Time Gate Scan Stream (§8) -->
        <x-card :title="__('Gate & Turnstile Live Scan Feed')">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                            <th class="py-3 px-3">{{ __('Student') }}</th>
                            <th class="py-3 px-3">{{ __('Session / Class') }}</th>
                            <th class="py-3 px-3">{{ __('Scan Method') }}</th>
                            <th class="py-3 px-3">{{ __('Timestamp') }}</th>
                            <th class="py-3 px-3 text-right">{{ __('Result') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">Alexander Cross</span>
                                <span class="font-mono text-[10px] text-slate-400">STU-2026-0042</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-medium text-slate-800">Calculus & Linear Algebra</span>
                                <span class="text-[10px] text-slate-400 block">Hall B</span>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                <span class="font-mono text-[11px]">📷 QR Scanner</span>
                            </td>
                            <td class="py-3 px-3 text-slate-500">
                                08:24:12 AM
                            </td>
                            <td class="py-3 px-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    ✓ Present (On Time)
                                </span>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">Daisy Miller</span>
                                <span class="font-mono text-[10px] text-slate-400">NUR-2026-0031</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-medium text-slate-800">Little Blossoms Playgroup</span>
                                <span class="text-[10px] text-slate-400 block">Room 102</span>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                <span class="font-mono text-[11px]">✍ Manual (Parent accompanied)</span>
                            </td>
                            <td class="py-3 px-3 text-slate-500">
                                08:35:45 AM
                            </td>
                            <td class="py-3 px-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    Late (+10m)
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
