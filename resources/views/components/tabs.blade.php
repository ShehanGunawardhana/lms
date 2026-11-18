@props([
    'defaultTab' => 0,
    'tabs' => [],
])

<div x-data="{ activeTab: {{ is_numeric($defaultTab) ? $defaultTab : "'$defaultTab'" }} }" class="w-full">
    <!-- Tabs Header -->
    <div class="border-b border-slate-200 overflow-x-auto scrollbar-none">
        <nav class="flex space-x-2 sm:space-x-4 min-w-max pb-px" aria-label="Tabs">
            @foreach ($tabs as $index => $label)
                <button
                    type="button"
                    @click="activeTab = {{ is_numeric($index) ? $index : "'$index'" }}"
                    :class="activeTab === {{ is_numeric($index) ? $index : "'$index'" }} 
                        ? 'border-brand-600 text-brand-600 bg-brand-50/50 font-bold' 
                        : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium'"
                    class="whitespace-nowrap py-3 px-4 border-b-2 text-xs sm:text-sm rounded-t-xl transition-all duration-150 focus:outline-none"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Tabs Content Container -->
    <div class="pt-4">
        {{ $slot }}
    </div>
</div>
