@extends('layouts.admin')
@section('title', 'Admin Audit Log')
@section('admin-content')
    <h1>
        Audit log</h1>
    <p class="text-muted">Account, stock, quote, payment, and credit changes made behind the counter.</p>
    <x-admin-filters>
        <div class="col-sm-auto"><select class="form-select" name="action" aria-label="Action" data-auto-submit>
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>
    </x-admin-filters>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date / actor</th>
                    <th>Action</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M Y H:i') }}<small
                                class="d-block text-muted">{{ $log->actor?->name ?? 'Console / seed' }}</small></td>
                        <td>{{ $log->action }}<small class="d-block">{{ $log->subject_type }}
                                #{{ $log->subject_id }}</small></td>
                        <td class="audit-description">{{ $log->description }}</td>
                </tr>@empty<tr>
                        <td colspan="3">No activity matches your search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $logs->links() }}
@endsection
