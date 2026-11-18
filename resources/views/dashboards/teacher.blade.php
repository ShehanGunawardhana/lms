<x-layouts.app :title="__('Faculty & Teacher Workspace')">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Teacher & Lecturer Workspace') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Manage your classes, student assignments, learning activities, and grading (§4, §5.5, §6.4)') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="#quick-drop" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-xs transition-colors inline-flex items-center gap-1.5 btn-interactive">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    {{ __('Create Lesson Drop') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Faculty Metrics Overview -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card class="border-l-4 border-l-brand-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Assigned Classes') }}</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">4</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                    </div>
                </div>
            </x-card>

            <x-card class="border-l-4 border-l-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Pending Reviews') }}</p>
                        <p class="text-2xl font-black text-amber-600 mt-1">12</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                </div>
            </x-card>

            <x-card class="border-l-4 border-l-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Active Students') }}</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1">186</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                    </div>
                </div>
            </x-card>

            <x-card class="border-l-4 border-l-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Portfolio Status') }}</p>
                        <p class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md mt-1 inline-block">{{ __('Published (§5.5)') }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Quick Access Workspaces -->
        <x-card :title="__('Faculty Workflows')">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="#activities" class="p-4 rounded-xl border border-slate-200 hover:border-brand-300 hover:bg-brand-50/50 flex flex-col items-center text-center group transition-colors">
                    <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    </span>
                    <span class="text-xs font-bold text-slate-800">{{ __('Lesson Drops & Materials') }}</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ __('PDFs, Videos, Worksheets') }}</span>
                </a>

                <a href="#grading" class="p-4 rounded-xl border border-slate-200 hover:border-brand-300 hover:bg-brand-50/50 flex flex-col items-center text-center group transition-colors">
                    <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.09 1.976 1.053 1.976 2.188V8.25M6.75 12h10.5M6.75 16.5h10.5M6.75 7.5h10.5"/></svg>
                    </span>
                    <span class="text-xs font-bold text-slate-800">{{ __('Submissions & Feedback') }}</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ __('Stars, Stickers, Grading (§6.4)') }}</span>
                </a>

                <a href="{{ route('teacher.portfolio') }}" class="p-4 rounded-xl border border-slate-200 hover:border-brand-300 hover:bg-brand-50/50 flex flex-col items-center text-center group transition-colors">
                    <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                    </span>
                    <span class="text-xs font-bold text-slate-800">{{ __('Lecturer Portfolio') }}</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ __('Bio, Qualifications (§5.5)') }}</span>
                </a>

                <a href="#chat" class="p-4 rounded-xl border border-slate-200 hover:border-brand-300 hover:bg-brand-50/50 flex flex-col items-center text-center group transition-colors">
                    <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                    </span>
                    <span class="text-xs font-bold text-slate-800">{{ __('Direct Inquiries') }}</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ __('Student & Parent Chat') }}</span>
                </a>
            </div>
        </x-card>

        <!-- Today's Teaching Schedule -->
        <x-card :title="__('Today\'s Classes & Sessions')">
            <div class="space-y-3">
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-xl bg-brand-50 border border-brand-200 text-brand-700 font-bold flex flex-col items-center justify-center shrink-0">
                            <span class="text-[10px] uppercase">09:00</span>
                            <span class="text-xs font-black">AM</span>
                        </span>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">Sunflowers Nursery &bull; Sensory Art & Storytime</div>
                            <span class="text-slate-500">Room 102 &bull; 18 Students Enrolled</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                            {{ __('Confirmed') }}
                        </span>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-200 text-purple-700 font-bold flex flex-col items-center justify-center shrink-0">
                            <span class="text-[10px] uppercase">02:30</span>
                            <span class="text-xs font-black">PM</span>
                        </span>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">Mathematics Class &bull; Calculus Session 4</div>
                            <span class="text-slate-500">Hall B &bull; 42 Students Enrolled</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-brand-100 text-brand-800">
                            {{ __('Scheduled') }}
                        </span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.app>
