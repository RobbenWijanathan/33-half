@props(['title', 'href' => null, 'linkText' => 'View All', 'eyebrow' => null])
<div class="section-header">
    <h2>{{ $title }}</h2>
    @if($href)
        <a class="text-link" href="{{ $href }}">{{ $linkText }}</a>
    @endif
</div>
