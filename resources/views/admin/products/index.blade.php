@extends('layouts.admin')
@section('title', $type === 'vinyl' ? 'Manage Records' : 'Manage Merchandise')
@section('admin-content')
    <div class="section-heading">
        <h1>{{ $type === 'vinyl' ? 'Records' : 'Merchandise' }}</h1><a class="button button-dark"
            href="{{ route($base . '.create') }}">Add {{ $type === 'vinyl' ? 'record' : 'merchandise' }}</a>
    </div>
    <x-admin-filters :sorts="[
        'newest' => 'Newest',
        'oldest' => 'Oldest',
        'name' => 'Name',
        'price' => 'Price ↑',
        'stock' => 'Stock ↑',
    ]">
        <div class="col-sm-auto"><select class="form-select" name="stock" aria-label="Stock filter" data-auto-submit>
                <option value="">All stock</option>
                <option value="low" @selected(request('stock') === 'low')>Low stock</option>
                <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
            </select></div>
        <div class="col-sm-auto"><select class="form-select" name="condition" aria-label="Condition filter"
                data-auto-submit>
                <option value="">All conditions</option>
                <option value="new" @selected(request('condition') === 'new')>New</option>
                <option value="used" @selected(request('condition') === 'used')>Used</option>
            </select></div>
    </x-admin-filters>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3"><img class="table-art"
                                    src="{{ $product->cover_url }}" alt="">
                                <div><a href="{{ route($base . '.edit', $product) }}">{{ $product->name }}</a><small
                                        class="d-block text-muted">{{ $product->artists->pluck('name')->join(', ') }} ·
                                        {{ ucfirst($product->condition) }}</small></div>
                            </div>
                        </td>
                        <td class="text-nowrap">{{ $product->formatted_price }}</td>
                        <td>{{ $product->variants->isNotEmpty() ? $product->variants->sum('stock') : $product->stock }}</td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap"><a class="btn btn-sm btn-outline-secondary"
                                    href="{{ route($base . '.edit', $product) }}">Edit</a>
                                <form method="POST" action="{{ route($base . '.destroy', $product) }}"
                                    data-confirm="Archive this product? Existing orders will stay intact.">@csrf
                                    @method('DELETE')<button class="btn btn-sm btn-outline-danger">Archive</button></form>
                            </div>
                        </td>
                </tr>@empty<tr>
                        <td colspan="4">No products match your search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $products->links() }}
@endsection
