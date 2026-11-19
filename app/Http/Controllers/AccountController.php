<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('account.index', [
            'user' => $user, 'orders' => $user->orders()->latest()->take(4)->get(),
            'quotes' => $user->quotes()->latest()->take(4)->get(), 'addresses' => $user->addresses()->get(),
        ]);
    }

    public function edit(Request $request)
    {
        return view('account.settings', ['user' => $request->user(), 'addresses' => $request->user()->addresses()->get()]);
    }

    public function update(Request $request)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower($request->input('email'))]);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254', Rule::unique('users')->ignore($request->user()->id)],
            'phone' => ['nullable', 'string', 'max:30'], 'current_password' => ['required', 'current_password'],
        ]);
        unset($data['current_password']);
        if ($data['email'] !== $request->user()->email) {
            $request->user()->email_verified_at = null;
        }
        $request->user()->fill($data)->save();

        return back()->with('notice', 'Profile updated.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        $user = $request->user();
        $user->password = $data['password'];
        $user->setRememberToken(Str::random(60));
        $user->save();
        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();
        $request->session()->regenerate();

        return back()->with('notice', 'Password updated. Other sessions have been signed out.');
    }

    public function credits(Request $request)
    {
        return view('account.credits', ['user' => $request->user(), 'transactions' => $request->user()->creditTransactions()->latest('id')->paginate(15)]);
    }
}
