{{-- Used as: {{ $products->links('partials.pagination') }}. Laravel passes in $paginator and $elements. --}}
@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        <span>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        <div>
            @if ($paginator->onFirstPage())
                <span class="disabled" aria-disabled="true">← Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Previous</a>
            @endif

            @foreach ($elements as $element)
                {{-- "..." between page groups --}}
                @if (is_string($element))
                    <span class="disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="current" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a>
            @else
                <span class="disabled" aria-disabled="true">Next →</span>
            @endif
        </div>
    </nav>
@endif
