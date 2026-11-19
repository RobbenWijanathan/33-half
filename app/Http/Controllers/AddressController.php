<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    public function store(AddressRequest $request)
    {
        $request->user()->addresses()->create($request->validated());

        return back()->with('notice', 'Address saved.');
    }

    public function update(AddressRequest $request, Address $address)
    {
        Gate::authorize('update', $address);
        $address->update($request->validated());

        return back()->with('notice', 'Address updated.');
    }

    public function destroy(Address $address)
    {
        Gate::authorize('delete', $address);
        $address->delete();

        return back()->with('notice', 'Address removed. Previous orders keep their original address.');
    }
}
