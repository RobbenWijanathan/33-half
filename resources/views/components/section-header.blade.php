@props(['eyebrow' => null, 'title', 'href' => null, 'linkText' => 'View all'])
<div class="section-header"><div>@if($eyebrow)<span class="eyebrow">{{ $eyebrow }}</span>@endif<h2>{{ $title }}</h2></div>@if($href)<a class="text-link" href="{{ $href }}">{{ $linkText }} <span aria-hidden="true">↗</span></a>@endif</div>
