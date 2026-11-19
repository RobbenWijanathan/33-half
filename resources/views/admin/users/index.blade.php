@extends('layouts.admin')
@section('title', 'Manage Profiles')
@section('admin-content')
    <h1>
        Profiles</h1>
    <x-admin-filters :sorts="['newest' => 'Newest', 'oldest' => 'Oldest', 'name' => 'Name', 'credit' => 'Highest credit']">
        <div class="col-sm-auto"><select class="form-select" name="role" aria-label="Role" data-auto-submit>
                <option value="">All roles</option>
                <option value="user" @selected(request('role') === 'user')>User</option>
                <option value="admin" @selected(request('role') === 'admin')>Admin</option>
            </select></div>
        <div class="col-sm-auto"><select class="form-select" name="active" aria-label="Account status" data-auto-submit>
                <option value="">All accounts</option>
                <option value="1" @selected(request('active') === '1')>Active</option>
                <option value="0" @selected(request('active') === '0')>Inactive</option>
            </select></div>
    </x-admin-filters>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Role / status</th>
                    <th>Orders / quotes</th>
                    <th>Store credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><a href="{{ route('admin.users.edit', $user) }}">{{ $user->name }}</a><small
                                class="d-block text-muted">{{ $user->email }}</small></td>
                        <td>{{ ucfirst($user->role) }} · {{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>{{ $user->orders_count }} / {{ $user->quotes_count }}</td>
                        <td>{{ \App\Support\Money::format($user->store_credit) }}</td>
                </tr>@empty<tr>
                        <td colspan="4">No accounts match your search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $users->links() }}
@endsection
