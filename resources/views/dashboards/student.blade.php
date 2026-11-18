<x-layouts.app :title="__('Student Learning Dashboard')">
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Student Learning Portal') }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ __('Access your enrolled classes, materials, quizzes and QR pass') }}</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Enrolled Classes') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">3</p>
            </x-card>
            <x-card class="border-l-4 border-l-accent-500">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Pending Assignments') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">2</p>
            </x-card>
            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Print Allowance Left') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">1 / month</p>
            </x-card>
        </div>

        <x-card :title="__('My Course Materials & Upcoming Sessions')">
            <x-empty-state 
                :title="__('No new materials posted')" 
                :description="__('Downloadable slides, notes, assignments and lecture links will appear here.')"
            />
        </x-card>
    </div>
</x-layouts.app>
