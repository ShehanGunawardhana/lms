@props([
    'targetDate',
    'label' => __('Deadline remaining'),
])

<div 
    x-data="{
        target: new Date('{{ $targetDate }}').getTime(),
        now: new Date().getTime(),
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        isExpired: false,
        updateCountdown() {
            this.now = new Date().getTime();
            const distance = this.target - this.now;

            if (distance < 0) {
                this.isExpired = true;
                this.days = 0;
                this.hours = 0;
                this.minutes = 0;
                this.seconds = 0;
                return;
            }

            this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
            this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            this.seconds = Math.floor((distance % (1000 * 60)) / 1000);
        }
    }"
    x-init="updateCountdown(); setInterval(() => updateCountdown(), 1000)"
    class="inline-flex flex-col items-center p-3 rounded-2xl bg-slate-900 text-white shadow-md border border-slate-800"
>
    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ $label }}</span>

    <template x-if="!isExpired">
        <div class="flex items-center space-x-1.5 font-mono text-sm sm:text-base font-bold text-brand-300">
            <span class="px-2 py-1 bg-slate-800 rounded-lg" x-text="String(days).padStart(2, '0') + 'd'"></span>
            <span>:</span>
            <span class="px-2 py-1 bg-slate-800 rounded-lg" x-text="String(hours).padStart(2, '0') + 'h'"></span>
            <span>:</span>
            <span class="px-2 py-1 bg-slate-800 rounded-lg" x-text="String(minutes).padStart(2, '0') + 'm'"></span>
            <span>:</span>
            <span class="px-2 py-1 bg-slate-800 rounded-lg text-accent-400 animate-pulse" x-text="String(seconds).padStart(2, '0') + 's'"></span>
        </div>
    </template>

    <template x-if="isExpired">
        <span class="px-3 py-1 bg-rose-500/20 text-rose-300 font-bold rounded-lg text-xs tracking-wider uppercase">
            {{ __('Expired / Closed') }}
        </span>
    </template>
</div>
