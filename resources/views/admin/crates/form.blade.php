@extends('layouts.admin')
@section('title', $crate->exists ? 'Edit Crate' : 'Add Crate')
@section('admin-content')
    <a href="{{ route('admin.crates.index') }}">← All crates</a>
    <h1 class="mt-3">{{ $crate->exists ? 'Edit' : 'Add' }} crate</h1>
    <form method="POST" action="{{ $crate->exists ? route('admin.crates.update', $crate) : route('admin.crates.store') }}"
        enctype="multipart/form-data">@csrf @if ($crate->exists)
            @method('PUT')
        @endif
        <div class="account-panel mb-4"><x-field name="name" label="Name" :value="$crate->name" maxlength="120"
                required /><x-field name="slug" label="URL slug (unique)" :value="$crate->slug" pattern="[A-Za-z0-9_-]+"
                maxlength="160" required /><x-field name="description" label="Description">
                <textarea class="form-control" name="description" id="description" rows="3">{{ old('description', $crate->description) }}</textarea>
            </x-field><x-field name="color" label="Crate color" type="color" :value="$crate->color" required />
            @if ($crate->cover_image)
                <img class="upload-preview mb-3" src="{{ asset($crate->cover_image) }}" alt="Current crate cover">
            @endif
            <label for="cover" class="form-label d-block">Cover image (JPG, PNG or WebP, max 5 MB)</label><input
                class="form-control mb-3" type="file" name="cover" id="cover"
                accept="image/jpeg,image/png,image/webp" data-image-preview><label class="form-check"><input
                    class="form-check-input" name="remove_cover" type="checkbox" value="1"> <span
                    class="form-check-label">Remove cover</span></label>
        </div>
        <div class="account-panel mb-4">
            <h2 class="h4">Records in this crate</h2><label for="crate-record-search" class="form-label">Find a
                record</label><input class="form-control mb-3" type="search" id="crate-record-search" data-checklist-search
                placeholder="Filter by album or artist…">
            <div class="record-checklist" data-checklist>
                @foreach ($products as $product)
                    <label class="form-check py-2 border-bottom"><input class="form-check-input" type="checkbox"
                            name="product_ids[]" value="{{ $product->id }}" @checked(in_array($product->id, old('product_ids', $crate->products->pluck('id')->all())))> <span
                            class="form-check-label">{{ $product->name }} <small class="text-muted">·
                                {{ $product->artists->pluck('name')->join(', ') }}</small></span></label>
                @endforeach
            </div>
        </div><button class="button button-dark">Save crate</button>
    </form>
@endsection
