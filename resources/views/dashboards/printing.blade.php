<x-layouts.app :title="__('Printing Station & Handouts Workspace')">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Printing Staff Counter') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Scan student QR passes, verify hardcopy material eligibility, and issue printed handouts (§4, §10)') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('station.printing') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-accent-600 hover:bg-accent-500 shadow-md shadow-accent-500/20 inline-flex items-center gap-1.5 transition-all btn-interactive">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/></svg>
                    {{ __('Open Full-Screen Print Kiosk (§10)') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Station Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card class="border-l-4 border-l-accent-600">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Prints Issued Today') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">142</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('All materials signed out') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Eligibility Rate') }}</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">98.5%</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Valid fee passes scanned') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-amber-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Reprint Requests') }}</p>
                <p class="text-2xl font-black text-amber-600 mt-1">3</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Awaiting admin approval') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Available Bundles') }}</p>
                <p class="text-2xl font-black text-brand-600 mt-1">18</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Ready for collection') }}</p>
            </x-card>
        </div>

        <!-- Recent Collection Activity Feed -->
        <x-card :title="__('Recent Handout Disbursements (§10)')">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                            <th class="py-3 px-3">{{ __('Student') }}</th>
                            <th class="py-3 px-3">{{ __('Material / Handout') }}</th>
                            <th class="py-3 px-3">{{ __('Class') }}</th>
                            <th class="py-3 px-3">{{ __('Timestamp') }}</th>
                            <th class="py-3 px-3 text-right">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">Alexander Cross</span>
                                <span class="font-mono text-[10px] text-slate-400">STU-2026-0042</span>
                            </td>
                            <td class="py-3 px-3 font-medium text-slate-800">
                                Calculus Tutorial Booklet #4 (November Edition)
                            </td>
                            <td class="py-3 px-3 text-slate-500">
                                Advanced Mathematics
                            </td>
                            <td class="py-3 px-3 text-slate-500">
                                Just now
                            </td>
                            <td class="py-3 px-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    ✓ Disbursed
                                </span>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">David Miller</span>
                                <span class="font-mono text-[10px] text-slate-400">STU-2026-0089</span>
                            </td>
                            <td class="py-3 px-3 font-medium text-slate-800">
                                Data Structures Lab Manual & Problem Set #2
                            </td>
                            <td class="py-3 px-3 text-slate-500">
                                Computer Science
                            </td>
                            <td class="py-3 px-3 text-slate-500">
                                12 mins ago
                            </td>
                            <td class="py-3 px-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    ✓ Disbursed
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
