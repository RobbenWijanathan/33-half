@props(['histories'])
<ol class="status-history list-unstyled">
    @foreach ($histories as $history)
        <li><x-status-badge :status="$history->status" /><time class="small text-muted ms-2"
                datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('d M Y, H:i') }}</time>
            @if ($history->note)
                <p class="mb-0 mt-2">{{ $history->note }}</p>
            @endif
        </li>
    @endforeach
</ol>
