@props(['sorts' => ['newest' => 'Newest first', 'oldest' => 'Oldest first']])
<form class="admin-filters row g-2 mb-4" method="GET">
    <div class="col-sm"><label class="visually-hidden" for="admin-search">Search</label><input class="form-control"
            id="admin-search" name="q" type="search" placeholder="Search…" value="{{ request('q') }}"
            maxlength="120"></div>
    {{ $slot }}
    <div class="col-sm-auto"><label class="visually-hidden" for="admin-sort">Sort</label><select class="form-select"
            id="admin-sort" name="sort" data-auto-submit>
            @foreach ($sorts as $value => $label)
                <option value="{{ $value }}" @selected(request('sort', 'newest') === $value)>{{ $label }}</option>
            @endforeach
        </select></div>
    <div class="col-auto"><button class="button button-border">Search</button></div>
    <div class="col-auto"><a class="btn py-2" href="{{ url()->current() }}">Reset</a></div>
</form>
