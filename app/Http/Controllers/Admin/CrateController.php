<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Crate;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CrateController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'sort' => ['nullable', 'in:newest,oldest,name,count'], 'contents' => ['nullable', 'in:empty,filled']]);
        $crates = Crate::withCount('products');
        if (! empty($data['q'])) {
            $crates->where('name', 'like', '%'.$data['q'].'%');
        }
        if (($data['contents'] ?? '') === 'empty') {
            $crates->doesntHave('products');
        }
        if (($data['contents'] ?? '') === 'filled') {
            $crates->has('products');
        }
        $sort = $data['sort'] ?? 'newest';
        $crates->orderBy(match ($sort) {
            'name' => 'name', 'count' => 'products_count', default => 'id'
        }, in_array($sort, ['newest', 'count']) ? 'desc' : 'asc');

        return view('admin.crates.index', ['crates' => $crates->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return $this->form(new Crate(['color' => '#701f38']));
    }

    public function edit(Crate $crate)
    {
        return $this->form($crate->load('products'));
    }

    public function store(Request $request)
    {
        $crate = new Crate;
        $this->save($request, $crate);

        return redirect()->route('admin.crates.edit', $crate)->with('notice', 'Crate created.');
    }

    public function update(Request $request, Crate $crate)
    {
        $this->save($request, $crate);

        return back()->with('notice', 'Crate updated.');
    }

    public function destroy(Crate $crate)
    {
        DB::transaction(function () use ($crate) {
            $crate->delete();
            AdminAuditLog::record('crate.archived', $crate, 'Archived '.$crate->name);
        });

        return redirect()->route('admin.crates.index')->with('notice', 'Crate archived.');
    }

    private function form(Crate $crate)
    {
        return view('admin.crates.form', ['crate' => $crate, 'products' => Product::vinyl()->with('artists')->orderBy('name')->get()]);
    }

    private function save(Request $request, Crate $crate): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'alpha_dash', 'max:160', Rule::unique('crates')->ignore($crate->id)],
            'description' => ['nullable', 'string', 'max:5000'], 'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'remove_cover' => ['boolean'],
            'product_ids' => ['nullable', 'array', 'max:500'], 'product_ids.*' => ['integer', 'distinct', Rule::exists('products', 'id')->where('product_type', 'vinyl')->whereNull('deleted_at')],
        ]);
        $values = collect($data)->only(['name', 'slug', 'description', 'color'])->all();
        $old = $crate->cover_image;
        $new = $request->hasFile('cover') ? $request->file('cover')->store('crates', 'public') : null;
        if ($new || $request->boolean('remove_cover')) {
            $values['cover_image'] = $new ? 'storage/'.$new : null;
        }
        try {
            DB::transaction(function () use ($crate, $values, $data) {
                $crate->fill($values)->save();
                $crate->products()->sync($data['product_ids'] ?? []);
                AdminAuditLog::record('crate.saved', $crate, 'Saved '.$crate->name);
            });
        } catch (\Throwable $exception) {
            if ($new) {
                Storage::disk('public')->delete($new);
            }
            throw $exception;
        }
        if (($new || $request->boolean('remove_cover')) && str_starts_with($old ?? '', 'storage/crates/')) {
            Storage::disk('public')->delete(substr($old, 8));
        }
    }
}
