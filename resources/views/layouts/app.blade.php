<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#171717">
    <title>@yield('title', '33½ — An independent record store')</title>
    <meta name="description" content="Records worth spending time with. A simple storefront for vinyl, merchandise, and crate digging.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-announcement-bar />
    <x-navbar />
    @if(session('notice'))<div class="flash-notice" role="status">{{ session('notice') }}</div>@endif
    <main id="main-content">@yield('content')</main>
    <x-footer />
</body>
</html>
