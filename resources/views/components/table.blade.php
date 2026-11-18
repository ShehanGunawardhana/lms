@props([
    'headers' => [],
    'striped' => false,
])

<div class="overflow-x-auto rounded-2xl border border-slate-200/80 shadow-xs bg-white">
    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        @if (count($headers) > 0 || isset($head))
            <thead class="bg-slate-50/80 text-xs font-bold uppercase tracking-wider text-slate-500 select-none">
                @if (isset($head))
                    {{ $head }}
                @else
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col" class="px-5 py-3.5">{{ $header }}</th>
                        @endforeach
                    </tr>
                @endif
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
            {{ $slot }}
        </tbody>
    </table>
</div>
