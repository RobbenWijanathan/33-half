@props(['genres'])
<div class="genre-nav">@foreach($genres as $genre)<a href="{{ route('genres.show', $genre) }}">{{ $genre->name }} <span aria-hidden="true">↗</span></a>@endforeach</div>
