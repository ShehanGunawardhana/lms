<x-layouts.app :title="__('Parent & Nursery Portal')">
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Parent Portal') }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ __('Monitor nursery activities, videos, submissions and announcements') }}</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card class="border-l-4 border-l-nursery-500">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Registered Children') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">1</p>
            </x-card>
            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('New Activities') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">3</p>
            </x-card>
            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Reviewed Submissions') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">8</p>
            </x-card>
        </div>

        <x-card :title="__('Active Activities & Video Lessons')">
            <x-empty-state 
                :title="__('Welcome to the Nursery Activity Feed')" 
                :description="__('Videos, step-by-step instructions and learning materials posted by teachers will appear here.')"
            />
        </x-card>
    </div>
</x-layouts.app>
