@props(['product'])
@if($product->audio_preview_url)
    <button class="preview-button" type="button" aria-label="Play preview of {{ $product->name }}" aria-pressed="false" data-preview-button data-audio-url="{{ asset($product->audio_preview_url) }}" data-start-time="{{ $product->preview_start_time }}"><span class="preview-icon" aria-hidden="true">▶</span><x-audio-playing-indicator /></button>
@endif
