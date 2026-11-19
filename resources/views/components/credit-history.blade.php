@props(['transactions'])
<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Date / reason</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Balance after</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('d M Y H:i') }}<small
                            class="d-block text-muted">{{ $transaction->reason }}</small></td>
                    <td>{{ ucfirst($transaction->type) }}</td>
                    <td class="text-nowrap">
                        {{ $transaction->amount > 0 ? '+' : '−' }}{{ \App\Support\Money::format(abs($transaction->amount)) }}
                    </td>
                    <td class="text-nowrap">{{ \App\Support\Money::format($transaction->balance_after) }}</td>
            </tr>@empty<tr>
                    <td colspan="4">No credit activity yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
