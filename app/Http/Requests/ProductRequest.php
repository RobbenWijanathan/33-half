<?php

namespace App\Http\Requests;

use App\Support\RecordGrades;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $rules = [
            'name' => ['required', 'string', 'max:160'], 'slug' => ['required', 'alpha_dash', 'max:180', Rule::unique('products')->ignore($product?->id)],
            'description' => ['required', 'string', 'max:10000'], 'price' => ['required', 'integer', 'min:1', 'max:99999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'], 'condition' => ['required', Rule::in(['new', 'used'])],
            'record_grade' => ['nullable', Rule::in(array_keys(RecordGrades::labels()))], 'sleeve_grade' => ['nullable', Rule::in(array_keys(RecordGrades::labels()))],
            'artist_names' => ['nullable', 'string', 'max:255'], 'genre_ids' => ['nullable', 'array', 'max:3'], 'genre_ids.*' => ['integer', 'distinct', 'exists:genres,id'],
            'label_id' => ['nullable', 'exists:labels,id'], 'release_date' => ['nullable', 'date'], 'format' => ['nullable', 'string', 'max:100'],
            'merch_category' => ['nullable', 'string', 'max:100'], 'audio_preview_url' => ['nullable', 'url:http,https', 'max:255'],
            'preview_start_time' => ['nullable', 'integer', 'min:0', 'max:3600'], 'is_featured' => ['boolean'], 'is_preorder' => ['boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'remove_cover' => ['boolean'],
            'gallery' => ['nullable', 'array', 'max:2'], 'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'remove_gallery' => ['boolean'],
            'variants' => ['nullable', 'array', 'max:30'], 'variants.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('product_variants', 'id')->where('product_id', $product?->id ?? 0)],
            'variants.*' => ['array'], 'variants.*.sku' => ['nullable', 'string', 'distinct'],
            'variants.*.name' => ['nullable', 'string', 'max:100'], 'variants.*.delete' => ['nullable', 'boolean'],
        ];
        if ($this->routeIs('admin.records.*')) {
            $rules['artist_names'] = ['required', 'string', 'max:255'];
            $rules['genre_ids'] = ['required', 'array', 'min:1', 'max:3'];
            $rules['variants'] = ['prohibited'];
            if ($this->input('condition') === 'used') {
                $rules['record_grade'][] = 'required';
                $rules['sleeve_grade'][] = 'required';
            }
        }
        $variants = $this->input('variants', []);
        foreach (is_array($variants) ? $variants : [] as $index => $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $required = ! empty($variant['name']) && empty($variant['delete']);
            $rules["variants.$index.sku"] = [$required ? 'required' : 'nullable', 'string', 'max:100', 'distinct', Rule::unique('product_variants', 'sku')->ignore($variant['id'] ?? null)];
            $rules["variants.$index.price"] = [$required ? 'required' : 'nullable', 'integer', 'min:1', 'max:1000000000'];
            $rules["variants.$index.stock"] = [$required ? 'required' : 'nullable', 'integer', 'min:0', 'max:1000000'];
        }

        return $rules;
    }
}
