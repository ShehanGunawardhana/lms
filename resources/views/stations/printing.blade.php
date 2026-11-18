<x-layouts.station :title="__('Printing Counter Station')" :stationName="__('Print Counter Station')">
    <div class="h-full flex flex-col justify-between space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 flex-1">
            <!-- Printing Scanner Viewport -->
            <div class="md:col-span-2 bg-slate-900 rounded-3xl border border-slate-800 p-6 flex flex-col items-center justify-center relative overflow-hidden shadow-2xl">
                <div class="w-64 h-64 sm:w-80 sm:h-80 border-2 border-accent-400 rounded-2xl relative flex items-center justify-center bg-black/40 shadow-inner">
                    <div class="text-center space-y-2 p-4">
                        <svg class="w-16 h-16 mx-auto text-accent-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <p class="text-sm font-bold text-slate-200">{{ __('Scan Student Pass to Verify Print Allowance') }}</p>
                        <p class="text-xs text-slate-400">{{ __('1 copy per material per month limit (§9)') }}</p>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-2 text-xs font-mono text-cyan-400 bg-cyan-950/60 px-3.5 py-1.5 rounded-full border border-cyan-800">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                    {{ __('PRINT QUEUE READY') }}
                </div>
            </div>

            <!-- Print Logs & Limits -->
            <div class="bg-slate-900/90 rounded-3xl border border-slate-800 p-6 flex flex-col justify-between shadow-xl">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center justify-between">
                        <span>{{ __('Print Counter Quotas') }}</span>
                        <span class="text-xs text-amber-400 font-mono">{{ __('Allowance Policy') }}</span>
                    </h3>

                    <div class="space-y-3">
                        <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/60 text-xs text-slate-400">
                            <span class="font-bold text-slate-200 block mb-1">{{ __('Policy Rules:') }}</span>
                            <ul class="list-disc list-inside space-y-1">
                                <li>{{ __('Maximum 1 print per material, student, and month.') }}</li>
                                <li>{{ __('Damaged prints require admin reprint approval.') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                    <div>
                        <span class="block text-slate-500">{{ __('Staff:') }}</span>
                        <span class="font-bold text-slate-200">{{ auth()->user()->name ?? 'Printing Staff' }}</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 font-mono text-[11px]">{{ __('COUNTER #1') }}</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.station>
