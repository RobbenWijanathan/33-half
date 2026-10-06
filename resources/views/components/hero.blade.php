@props(['eyebrow' => 'Independent sounds', 'title' => 'Records worth spending time with.', 'description' => 'A place for the albums you return to, the ones you find by accident, and everything in between.'])
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-copy"><span class="eyebrow light">{{ $eyebrow }}</span><h1>{{ $title }}</h1><p>{{ $description }}</p><div class="hero-actions"><a class="button button-light" href="{{ route('vinyl') }}">Shop vinyl <span aria-hidden="true">↗</span></a><a class="text-link light" href="{{ route('crate') }}">Try crate digging <span aria-hidden="true">→</span></a></div></div>
        <div class="hero-art" aria-hidden="true"><div class="hero-disc"><div class="hero-disc-label">33½<br><small>STEREO / SIDE A</small></div></div><span class="hero-art-note">A different way to find your next favorite.</span></div>
    </div>
    <div class="container hero-footer"><span>01 / DISCOVER THE SOUND</span><span>SCROLL TO EXPLORE ↓</span></div>
</section>
