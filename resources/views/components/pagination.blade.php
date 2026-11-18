@props([
    'paginator',
])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between py-3">
        <!-- Mobile Previous / Next View -->
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-400 bg-slate-100 border border-slate-200 cursor-default rounded-xl">
                    {{ __('Previous') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                    {{ __('Previous') }}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                    {{ __('Next') }}
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-xs font-semibold text-slate-400 bg-slate-100 border border-slate-200 cursor-default rounded-xl">
                    {{ __('Next') }}
                </span>
            @endif
        </div>

        <!-- Desktop Pagination Info & Links -->
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-slate-600">
                    {{ __('Showing') }}
                    <span class="font-bold text-slate-900">{{ $paginator->firstItem() }}</span>
                    {{ __('to') }}
                    <span class="font-bold text-slate-900">{{ $paginator->lastItem() }}</span>
                    {{ __('of') }}
                    <span class="font-bold text-slate-900">{{ $paginator->total() }}</span>
                    {{ __('results') }}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-xl shadow-2xs space-x-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-xs font-medium text-slate-300 bg-white border border-slate-200 cursor-default rounded-lg" aria-hidden="true">
                                &lsaquo;
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50" aria-label="{{ __('pagination.previous') }}">
                            &lsaquo;
                        </a>
                    @endif

                    {{-- Current page indicator --}}
                    <span class="relative inline-flex items-center px-3 py-2 text-xs font-bold text-white bg-brand-600 border border-brand-600 rounded-lg">
                        {{ $paginator->currentPage() }}
                    </span>

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50" aria-label="{{ __('pagination.next') }}">
                            &rsaquo;
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-xs font-medium text-slate-300 bg-white border border-slate-200 cursor-default rounded-lg" aria-hidden="true">
                                &rsaquo;
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
