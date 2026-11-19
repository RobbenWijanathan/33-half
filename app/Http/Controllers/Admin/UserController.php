<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'role' => ['nullable', 'in:user,admin'], 'active' => ['nullable', 'in:0,1'], 'sort' => ['nullable', 'in:newest,oldest,name,credit']]);
        $users = User::withCount(['orders', 'quotes']);
        if (! empty($data['q'])) {
            $users->where(function ($query) use ($data) {
                $query->where('name', 'like', '%'.$data['q'].'%')->orWhere('email', 'like', '%'.$data['q'].'%');
            });
        }
        if (! empty($data['role'])) {
            $users->where('role', $data['role']);
        }
        if (isset($data['active'])) {
            $users->where('is_active', $data['active']);
        }
        $sort = $data['sort'] ?? 'newest';
        $users->orderBy(match ($sort) {
            'name' => 'name', 'credit' => 'store_credit', default => 'id'
        }, in_array($sort, ['newest', 'credit']) ? 'desc' : 'asc');

        return view('admin.users.index', ['users' => $users->paginate(15)->withQueryString()]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user, 'transactions' => $user->creditTransactions()->latest('id')->paginate(10),
            'orders' => $user->orders()->latest()->take(10)->get(), 'quotes' => $user->quotes()->latest()->take(10)->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower($request->input('email'))]);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:254', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'], 'role' => ['required', 'in:user,admin'], 'is_active' => ['required', 'boolean'],
        ]);
        DB::transaction(function () use ($user, $data) {
            $admins = User::where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $locked = User::lockForUpdate()->findOrFail($user->id);
            if ($locked->isAdmin() && ($data['role'] !== 'admin' || ! $data['is_active']) && $admins->where('is_active', true)->count() <= 1) {
                throw ValidationException::withMessages(['role' => 'Keep at least one active administrator.']);
            }
            $locked->fill(collect($data)->only(['name', 'email', 'phone'])->all());
            $locked->role = $data['role'];
            $locked->is_active = $data['is_active'];
            if ($locked->isDirty('email')) {
                $locked->email_verified_at = null;
            }
            $changes = $locked->getDirty();
            $locked->save();
            if (! $locked->is_active) {
                DB::table('sessions')->where('user_id', $locked->id)->delete();
            }
            AdminAuditLog::record('user.updated', $locked, 'Updated account: '.json_encode($changes));
        });

        return back()->with('notice', 'Account updated.');
    }

    public function credit(Request $request, User $user)
    {
        $data = $request->validate(['amount' => ['required', 'integer', 'not_in:0', 'between:-1000000000,1000000000'], 'reason' => ['required', 'string', 'max:500']]);
        DB::transaction(function () use ($user, $request, $data) {
            $user->changeCredit((int) $data['amount'], 'adjustment', $data['reason'], 'adjustment-'.Str::uuid(), $request->user()->id);
            AdminAuditLog::record('credit.adjusted', $user, 'IDR '.$data['amount'].': '.$data['reason']);
        });

        return back()->with('notice', 'Credit adjustment recorded.');
    }
}
