@extends('layouts.admin')
@section('title', $product->exists ? 'Edit ' . $product->name : 'Add Product')
@section('admin-content')
    <a href="{{ route($base . '.index') }}">
        ← All {{ $type === 'vinyl' ? 'records' : 'merchandise' }}</a>
    <h1 class="mt-3">{{ $product->exists ? 'Edit' : 'Add' }} {{ $type === 'vinyl' ? 'record' : 'merchandise' }}</h1>
    <form method="POST" action="{{ $product->exists ? route($base . '.update', $product) : route($base . '.store') }}"
        enctype="multipart/form-data">@csrf @if ($product->exists)
            @method('PUT')
        @endif
        <div class="account-panel mb-4">
            <div class="row">
                <div class="col-md-7"><x-field name="name" label="Name" :value="$product->name" maxlength="160" required />
                </div>
                <div class="col-md-5"><x-field name="slug" label="URL slug (unique)" :value="$product->slug"
                        pattern="[A-Za-z0-9_-]+" maxlength="180" required /></div>
            </div>
            <x-field name="description" label="Description">
                <textarea class="form-control" name="description" id="description" rows="5" maxlength="10000" required>{{ old('description', $product->description) }}</textarea>
            </x-field>
            <div class="row">
                <div class="col-md-4"><x-field name="price" label="Price (whole IDR)" type="number" :value="(int) $product->price"
                        min="1" max="99999999" step="1" required /></div>
                <div class="col-md-4"><x-field name="stock" label="Stock (without variants)" type="number"
                        :value="$product->stock" min="0" max="1000000" step="1" required /></div>
                <div class="col-md-4"><x-field name="condition" label="Condition"><select class="form-select"
                            name="condition" id="condition">
                            <option value="new" @selected(old('condition', $product->condition) === 'new')>New</option>
                            <option value="used" @selected(old('condition', $product->condition) === 'used')>Used</option>
                        </select></x-field></div>
            </div>
            @if ($type === 'vinyl')
                <x-field name="artist_names" label="Artists (one name per line)">
                    <textarea class="form-control" name="artist_names" id="artist_names" rows="3" maxlength="255" required>{{ old('artist_names', $product->artists->pluck('name')->join("\n")) }}</textarea>
                </x-field>
                <x-field name="genre_ids" label="Genres (choose up to 3)">
                    <div class="genre-checks border rounded p-3">
                        @foreach ($genres as $genre)
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="genre_ids[]"
                                    value="{{ $genre->id }}" @checked(in_array($genre->id, old('genre_ids', $product->genres->pluck('id')->all())))> <span
                                    class="form-check-label">{{ $genre->name }}</span></label>
                        @endforeach
                    </div>
                </x-field>
                <div class="row">
                    @foreach (['record_grade' => 'Record grade', 'sleeve_grade' => 'Sleeve grade'] as $field => $label)
                        <div class="col-md-6"><x-field :name="$field" :label="$label"><select class="form-select"
                                    name="{{ $field }}" id="{{ $field }}">
                                    <option value="">Not graded</option>
                                    @foreach (\App\Support\RecordGrades::labels() as $grade => $label)
                                        <option value="{{ $grade }}" @selected(old($field, $product->$field) === $grade)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </x-field></div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-md-4"><x-field name="label_id" label="Record label"><select class="form-select"
                                name="label_id" id="label_id">
                                <option value="">None</option>
                                @foreach ($labels as $label)
                                    <option value="{{ $label->id }}" @selected(old('label_id', $product->label_id) == $label->id)>{{ $label->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-field></div>
                    <div class="col-md-4"><x-field name="release_date" label="Release date" type="date"
                            :value="$product->release_date?->format('Y-m-d')" /></div>
                    <div class="col-md-4"><x-field name="format" label="Format" :value="$product->format" maxlength="100" /></div>
                </div>
                <x-field name="audio_preview_url" label="Audio preview URL (optional)" type="url" :value="$product->audio_preview_url" />
                <x-field name="preview_start_time" label="Preview start (seconds)" type="number" :value="$product->preview_start_time ?? 0"
                    min="0" max="3600" />
            @else<x-field name="merch_category" label="Category" :value="$product->merch_category" maxlength="100" />
            @endif
            <label class="form-check mb-2"><input class="form-check-input" name="is_featured" value="1" type="checkbox"
                    @checked(old('is_featured', $product->is_featured))> <span class="form-check-label">Featured</span></label>
            <label class="form-check"><input class="form-check-input" name="is_preorder" value="1" type="checkbox"
                    @checked(old('is_preorder', $product->is_preorder))> <span class="form-check-label">Pre-order (stock still limits
                    checkout)</span></label>
        </div>
        <div class="account-panel mb-4">
            <h2 class="h4">Artwork</h2>
            <p class="small text-muted">JPG, PNG or WebP, up to 5 MB per image. Uploaded images replace the current
                selection.</p>
            @if ($product->exists)
                <img class="upload-preview mb-3" src="{{ $product->cover_url }}" alt="Current cover">
            @endif
            <label class="form-label" for="cover">Cover image</label><input class="form-control mb-3" type="file"
                name="cover" id="cover" accept="image/jpeg,image/png,image/webp" data-image-preview>
            <label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remove_cover"
                    value="1"> <span class="form-check-label">Remove uploaded cover</span></label>
            <label class="form-label" for="gallery">Gallery (up to 2 images)</label><input class="form-control mb-3"
                type="file" name="gallery[]" id="gallery" accept="image/jpeg,image/png,image/webp" multiple
                data-image-preview>
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach ($product->gallery_images ?? [] as $image)
                    <img class="upload-preview" src="{{ asset($image['path']) }}" alt="{{ $image['label'] }}">
                @endforeach
            </div>
            <label class="form-check"><input class="form-check-input" type="checkbox" name="remove_gallery"
                    value="1"> <span class="form-check-label">Remove gallery</span></label>
        </div>
        @if ($type === 'merchandise')
            <div class="account-panel mb-4">
                <h2 class="h4">Variants</h2>
                <p class="small text-muted">Add sizes or colors. Each variant has its own price and stock. Leave unused
                    rows empty. Retiring a variant preserves previous orders.</p>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>IDR price</th>
                                <th>Stock</th>
                                <th>Retire</th>
                            </tr>
                        </thead>
                        <tbody data-variant-rows>
                            @foreach (old('variants', [...$product->variants->toArray(), ['name' => ''], ['name' => ''], ['name' => '']]) as $index => $variant)
                                <tr>
                                    <td><input type="hidden" name="variants[{{ $index }}][id]"
                                            value="{{ $variant['id'] ?? '' }}"><input class="form-control variant-input"
                                            aria-label="Variant name {{ $index + 1 }}"
                                            name="variants[{{ $index }}][name]"
                                            value="{{ $variant['name'] ?? '' }}" maxlength="100"></td>
                                    <td><input class="form-control variant-input" aria-label="SKU {{ $index + 1 }}"
                                            name="variants[{{ $index }}][sku]"
                                            value="{{ $variant['sku'] ?? '' }}" maxlength="100"></td>
                                    <td><input class="form-control variant-input" aria-label="Price {{ $index + 1 }}"
                                            name="variants[{{ $index }}][price]" type="number" min="1"
                                            step="1" value="{{ $variant['price'] ?? '' }}"></td>
                                    <td><input class="form-control" style="min-width:80px"
                                            aria-label="Stock {{ $index + 1 }}"
                                            name="variants[{{ $index }}][stock]" type="number" min="0"
                                            step="1" value="{{ $variant['stock'] ?? '' }}"></td>
                                    <td><input class="form-check-input" aria-label="Retire variant {{ $index + 1 }}"
                                            name="variants[{{ $index }}][delete]" type="checkbox" value="1"
                                            @checked(!empty($variant['delete']))></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div><button class="button button-border" type="button" data-add-variant>Add another row</button>
            </div>
        @endif
        <div class="d-flex flex-wrap gap-3"><button class="button button-dark" data-loading-text="Saving…">Save
                {{ $type === 'vinyl' ? 'record' : 'merchandise' }}</button><a class="button button-border"
                href="{{ route($base . '.index') }}">Back to list</a></div>
    </form>
@endsection
