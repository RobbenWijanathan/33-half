@extends('layouts.admin')
@section('title', 'Manage Crates')
@section('admin-content')
    <div class="section-heading">
        <h1>Crates</h1><a class="button button-dark" href="{{ route('admin.crates.create') }}">Add crate</a>
    </div>
    <x-admin-filters :sorts="['newest' => 'Newest', 'oldest' => 'Oldest', 'name' => 'Name', 'count' => 'Most records']">
        <div class="col-sm-auto"><select class="form-select" name="contents" aria-label="Crate contents" data-auto-submit>
                <option value="">All crates</option>
                <option value="filled" @selected(request('contents') === 'filled')>With records</option>
                <option value="empty" @selected(request('contents') === 'empty')>Empty</option>
            </select></div>
    </x-admin-filters>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Crate</th>
                    <th>Records</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($crates as $crate)
                    <tr>
                        <td><span class="crate-swatch" style="background-color:{{ $crate->color }}"></span> <a
                                href="{{ route('admin.crates.edit', $crate) }}">{{ $crate->name }}</a></td>
                        <td>{{ $crate->products_count }}</td>
                        <td>
                            <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary"
                                    href="{{ route('admin.crates.edit', $crate) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.crates.destroy', $crate) }}"
                                    data-confirm="Archive this crate? The records stay in the catalog.">@csrf
                                    @method('DELETE')<button class="btn btn-sm btn-outline-danger">Archive</button></form>
                            </div>
                        </td>
                </tr>@empty<tr>
                        <td colspan="3">No crates match your search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $crates->links() }}
@endsection
