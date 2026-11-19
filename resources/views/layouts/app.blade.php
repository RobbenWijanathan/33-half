<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#350021">
    <meta name="color-scheme" content="light dark">
    <x-theme-script />
    <title>33½ Records - @yield('title', 'Home')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/favicon.svg') }}">
    <meta name="description" content="New releases, graded used vinyl, and the joy of finding your next record.">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}?v=5.3.9">
    <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}?v=5.3.9" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <x-announcement-bar />
    <x-navbar />
    <div class="notice-toast" role="status" aria-atomic="true" data-notice
        @if (session('notice')) data-visible data-kind="{{ session('notice_kind', 'success') }}" @endif>
        {{ session('notice') }}</div>
    @if ($errors->any())
        <div class="container alert alert-danger mt-3" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <main id="main-content">@yield('content')</main>
    <x-footer />
    <div class="modal fade" id="confirm-action" tabindex="-1" aria-labelledby="confirm-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="confirm-title">Confirm action</h2><button type="button"
                        class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" data-confirm-message></p>
                </div>
                <div class="modal-footer"><button class="button button-border" type="button" data-bs-dismiss="modal">Go
                        back</button><button class="button button-dark" type="button"
                        data-confirm-continue>Confirm</button></div>
            </div>
        </div>
    </div>
</body>

</html>
