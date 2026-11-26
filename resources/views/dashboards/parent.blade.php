<x-layouts.app :title="__('Parent Portal Dashboard')">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Parent & Family Portal') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Stay connected with daily learning, milestones, and classroom activities (§6.5, §6.6)') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Child Selector Switcher -->
                <div class="inline-flex rounded-xl p-1 bg-slate-100 border border-slate-200 text-xs font-bold">
                    <button type="button" class="px-3 py-1 rounded-lg bg-white text-slate-900 shadow-xs">
                        Oliver (Playgroup)
                    </button>
                    <button type="button" class="px-3 py-1 rounded-lg text-slate-500 hover:text-slate-800">
                        Maya (Toddler)
                    </button>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Daily Student Status Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Attendance Today') }}</p>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-lg font-black text-emerald-800">{{ __('Present') }}</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Checked in at 08:42 AM</p>
            </x-card>

            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Active Group') }}</p>
                <p class="text-lg font-black text-slate-900 mt-1">Playgroup</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Teacher Sarah Jenkins</p>
            </x-card>

            <a href="{{ route('parent.activities') }}" class="block group">
                <x-card class="border-l-4 border-l-amber-500 hover:border-amber-600 transition-colors">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Activities Due') }}</p>
                    <p class="text-2xl font-black text-amber-600 mt-1">2</p>
                    <p class="text-[10px] text-slate-400 mt-0.5 group-hover:text-brand-600 transition-colors">Handprint art & alphabet match &rarr;</p>
                </x-card>
            </a>

            <x-card class="border-l-4 border-l-purple-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Monthly Fee') }}</p>
                <p class="text-lg font-black text-emerald-700 mt-1">{{ __('Paid') }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Receipt #RP-2026-118</p>
            </x-card>
        </div>

        <!-- Weekly Activity Feed (§6.6) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <x-card>
                    <x-slot name="header">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-slate-900">{{ __('Weekly Nursery Learning Feed') }}</h3>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('parent.progress') }}" class="text-xs font-bold text-amber-600 hover:text-amber-700 transition-colors inline-flex items-center gap-1">
                                    <span>🏆</span>
                                    <span>{{ __('Progress & Awards') }}</span>
                                </a>
                                <a href="{{ route('parent.activities') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 transition-colors">
                                    {{ __('Explore Activities') }} &rarr;
                                </a>
                            </div>
                        </div>
                    </x-slot>
                    <div class="space-y-4">
                        <!-- Activity Drop 1 -->
                        <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-brand-200 transition-colors space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900">
                                    {{ __('Sensory & Craft') }}
                                </span>
                                <span class="text-xs text-slate-400">Due Tomorrow, 4:00 PM</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">{{ __('Autumn Leaf Printing & Nature Journal') }}</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                {{ __('Explore outdoor leaf textures with washable paint. Collect 3 leaves of different shapes and press into your sketchbook. Upload a photo of the completed page!') }}
                            </p>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    {{ __('Photo Consent Active') }}
                                </span>
                                <button type="button" class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-xs cursor-pointer">
                                    {{ __('Submit Child\'s Work') }}
                                </button>
                            </div>
                        </div>

                        <!-- Activity Drop 2 -->
                        <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-brand-200 transition-colors space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-900">
                                    {{ __('Storytime & Music') }}
                                </span>
                                <span class="text-xs text-emerald-600 font-bold">✓ {{ __('Completed & Reviewed') }}</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">{{ __('The Very Hungry Caterpillar & Counting Game') }}</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                {{ __('Listen to the audio story with child. Complete the interactive caterpillar fruit matching quiz.') }}
                            </p>
                            <!-- Teacher sticker feedback (§6.4) -->
                            <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-200 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">⭐</span>
                                    <span class="font-bold text-amber-950">{{ __('Teacher Sarah:') }}</span>
                                    <span class="text-amber-900">{{ __('"Brilliant fruit counting, Oliver! You earned the Golden Apple sticker!"') }}</span>
                                </div>
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-200/60 px-2 py-0.5 rounded-md">5/5 Stars</span>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Sidebar: Safety, Pickup & Teacher Chat -->
            <div class="space-y-6">
                <!-- Safety & Pickup Info (§6.5) -->
                <x-card :title="__('Safety & Authorized Pickups')">
                    <div class="space-y-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <span class="font-bold text-slate-900 block">{{ __('Pickup & Safety Center') }}</span>
                            <span class="text-slate-500 block">{{ __('Manage emergency contacts, authorized pickups, and encrypted medical notes (§6.5).') }}</span>
                        </div>
                        <a href="{{ route('parent.safety') }}" class="w-full text-center py-2 text-xs font-bold text-brand-600 hover:underline cursor-pointer block">
                            {{ __('Manage Pickups & Health Details') }} &rarr;
                        </a>
                    </div>
                </x-card>

                <!-- Direct Message Teacher -->
                <x-card :title="__('Teacher Communications')">
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-100 text-brand-700 font-bold flex items-center justify-center">
                                SJ
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">Sarah Jenkins</h4>
                                <span class="text-[11px] text-slate-500">Playgroup Lead Teacher</span>
                            </div>
                        </div>
                        <p class="text-slate-600 leading-relaxed text-[11px]">
                            {{ __('Have questions about milestones, dietary needs, or classroom activities? Send a direct message to the teacher.') }}
                        </p>
                        <button type="button" class="w-full py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer shadow-xs">
                            💬 {{ __('Open Private Chat') }}
                        </button>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
