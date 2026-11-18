<x-layouts.station :title="__('Attendance Scanner Station')" :stationName="__('Attendance Scanner Kiosk')">
    <div class="h-full flex flex-col justify-between space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 flex-1">
            <!-- Camera / QR Scanner Viewport -->
            <div class="md:col-span-2 bg-slate-900 rounded-3xl border border-slate-800 p-6 flex flex-col items-center justify-center relative overflow-hidden shadow-2xl">
                <div class="absolute inset-0 bg-gradient-to-b from-brand-500/10 via-transparent to-black/60 pointer-events-none"></div>

                <!-- Laser scan animation overlay -->
                <div class="w-64 h-64 sm:w-80 sm:h-80 border-2 border-brand-400 rounded-2xl relative flex items-center justify-center bg-black/40 shadow-inner">
                    <div class="absolute inset-x-0 h-1 bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-lg shadow-cyan-400/50 animate-pulse"></div>
                    <div class="text-center space-y-2 p-4">
                        <svg class="w-16 h-16 mx-auto text-brand-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <p class="text-sm font-bold text-slate-200">{{ __('Align Student QR Pass Inside Frame') }}</p>
                        <p class="text-xs text-slate-400">{{ __('Fast 1-second auto check-in') }}</p>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-2 text-xs font-mono text-emerald-400 bg-emerald-950/60 px-3.5 py-1.5 rounded-full border border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    {{ __('CAMERA READY — LISTENING FOR CODES') }}
                </div>
            </div>

            <!-- Recent Scans / Live Feed -->
            <div class="bg-slate-900/90 rounded-3xl border border-slate-800 p-6 flex flex-col justify-between shadow-xl">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center justify-between">
                        <span>{{ __('Recent Scans') }}</span>
                        <span class="text-xs text-cyan-400 font-mono">0 {{ __('Processed') }}</span>
                    </h3>

                    <div class="space-y-3">
                        <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/60 text-xs text-slate-400 text-center">
                            {{ __('No scans recorded in this session yet.') }}
                        </div>
                    </div>
                </div>

                <!-- Station Operator Banner -->
                <div class="pt-4 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                    <div>
                        <span class="block text-slate-500">{{ __('Operator:') }}</span>
                        <span class="font-bold text-slate-200">{{ auth()->user()->name ?? 'Marshal Staff' }}</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 font-mono text-[11px]">{{ __('STATION #1') }}</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.station>
