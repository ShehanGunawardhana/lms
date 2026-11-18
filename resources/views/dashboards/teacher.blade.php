<x-layouts.app :title="__('Faculty & Teacher Dashboard')">
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Teacher & Lecturer Workspace') }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ __('Manage your classes, student assignments, learning activities and chat') }}</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Assigned Classes') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">4</p>
            </x-card>
            <x-card class="border-l-4 border-l-amber-500">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Pending Reviews') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">12</p>
            </x-card>
            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Total Enrolled') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">186</p>
            </x-card>
        </div>

        <x-card :title="__('Upcoming Sessions & Activities')">
            <x-empty-state 
                :title="__('No sessions scheduled for today')" 
                :description="__('Your upcoming timetable and active assignment submissions will appear here.')"
            />
        </x-card>
    </div>
</x-layouts.app>
