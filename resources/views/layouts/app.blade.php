<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#350021">
    <title>@yield('title', '33½ Records — Buy, sell, and trade vinyl')</title>
    <meta name="description" content="New releases, graded used vinyl, and the joy of finding your next record.">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <x-announcement-bar />
    <x-navbar />
    @if(session('notice'))
        <div class="container alert alert-success mt-3" role="status">{{ session('notice') }}</div>
    @endif
    @if($errors->any())
        <div class="container alert alert-danger mt-3" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <main id="main-content">@yield('content')</main>
    <x-footer />
</body>
</html>
