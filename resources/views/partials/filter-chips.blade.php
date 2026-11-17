{{-- Each active filter as a link that removes just that filter. Needs $activeFilters from the controller. --}}
@if (count($activeFilters) > 0)
    <ul class="filter-chips" aria-label="Active filters">
        @foreach ($activeFilters as $filter)
            <li>
                <a class="filter-chip" href="{{ $filter['url'] }}">
                    <span class="sr-only">Remove filter </span>{{ $filter['label'] }}
                    <svg aria-hidden="true" viewBox="0 0 12 12" width="10" height="10"><path d="M2 2l8 8M10 2l-8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </a>
            </li>
        @endforeach

        @if (count($activeFilters) > 1)
            <li><a class="filter-chips-clear" href="{{ url()->current() }}">Clear all</a></li>
        @endif
    </ul>
@endif
