<ul class="pagination" role="navigation" style="margin: 15px 0 0 0">

    <li class="disabled" aria-disabled="true">
        <span aria-hidden="true"><strong>{{ __('Kayıt') }}:</strong> {{ number_format($paginator->total(), 0, '', '.') }}</span>
    </li>

    @if ($paginator->hasPages())

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="disabled" aria-disabled="true"><span>@lang('pagination.previous')</span></li>
        @else
            <li><a class="ajaxPage" href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a></li>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li><a class="ajaxPage" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a></li>
        @else
            <li class="disabled" aria-disabled="true"><span>@lang('pagination.next')</span></li>
        @endif
    @endif
</ul>