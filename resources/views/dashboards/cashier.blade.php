<x-layouts.app :title="__('Cashier & Finance Dashboard')">
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Cashier & Fee Verification') }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ __('Process payment slips, issue official receipts and verify student financial standing') }}</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card class="border-l-4 border-l-amber-500">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Pending Slips') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">14</p>
            </x-card>
            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Verified Today') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">28</p>
            </x-card>
            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-xs font-semibold text-slate-500 uppercase">{{ __('Active Free Cards') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">6</p>
            </x-card>
        </div>

        <x-card :title="__('Slip Verification Queue')">
            <x-empty-state 
                :title="__('All payment slips reviewed')" 
                :description="__('Newly uploaded bank transfer slips will appear here for one-click verification.')"
            />
        </x-card>
    </div>
</x-layouts.app>
