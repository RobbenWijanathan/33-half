@extends('layouts.app')
@section('title', 'Trade In')
@section('content')
<div class="container">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Trade In</div>
    <h1>Trade In</h1>
    <p class="page-lead">Give your records a second spin.</p>
    <div class="row">
        <aside class="col-lg-5 mb-4 pr-lg-5">
            <h2>Trade your records.<br>Find your next favourite.</h2>
            <p>Tell us what’s on your shelf. Choose cash or store credit, and start with a quote request.</p>
            <ol class="trade-steps">
                <li><strong>Tell us about your records</strong><p>Add the artist, album, condition, and a few photos.</p></li>
                <li><strong>Get a quote</strong><p>The store checks demand and condition before making an offer.</p></li>
                <li><strong>Bring your records in</strong><p>Final value depends on an in-person inspection. Accept an offer only when you’re happy with it.</p></li>
            </ol>
            <div class="cream-panel">
                <h3>More music, less shelf space.</h3>
                <p class="mb-0">Choose store credit to put your collection towards your next find, or request a cash offer.</p>
            </div>
        </aside>
        <div class="col-lg-7">
            <form class="trade-form" method="POST" action="{{ route('trade.store') }}" enctype="multipart/form-data">
                @csrf
                <h2>Request a Quote</h2>
                <p class="demo-note">Demo: requests stay in this browser session. Only photo filenames are saved; nothing is sent to the store.</p>
                <p class="small text-muted">All fields are required unless marked optional.</p>
                <div class="row">
                    @foreach(['artist' => 'Artist', 'album' => 'Album Title'] as $field => $label)
                        <div class="col-sm-6 form-group">
                            <label for="{{ $field }}">{{ $label }}</label>
                            <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" maxlength="{{ $field === 'artist' ? 120 : 160 }}" required>
                            @error($field)<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    @endforeach
                    @foreach(['record_grade' => 'Record Grade', 'sleeve_grade' => 'Sleeve Grade'] as $field => $label)
                        <div class="col-sm-6 form-group">
                            <label for="{{ $field }}">{{ $label }}</label>
                            <select class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" required>
                                <option value="">Choose condition</option>
                                @foreach($grades as $value => $grade)
                                    <option value="{{ $value }}" @selected(old($field) === $value)>{{ $grade }}</option>
                                @endforeach
                            </select>
                            @error($field)<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    @endforeach
                </div>
                <details class="grade-help">
                    <summary>Not sure about the condition?</summary>
                    <p>M: untouched. NM: almost no signs of use. VG+: light wear. VG: visible wear and some surface noise. G: noticeable wear. F/P: heavily worn or damaged. These are your estimates; the store confirms grades after inspection.</p>
                </details>
                <div class="form-group">
                    <label for="notes">Notes <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="2000" placeholder="Pressing, scratches, missing inserts, or anything else we should know">{{ old('notes') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="photos">Photos <span class="text-muted">(optional)</span></label>
                    <input class="form-control-file" type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple aria-describedby="photo-help">
                    <small id="photo-help" class="form-text text-muted">Up to 6 JPG, PNG, or WebP photos, 10 MB each. Photos are not retained in this demo.</small>
                </div>
                <fieldset class="form-group">
                    <legend class="field-label">I’d prefer</legend>
                    <label class="choice"><input type="radio" name="preference" value="cash" @checked(old('preference', 'cash') === 'cash')> Cash</label>
                    <label class="choice"><input type="radio" name="preference" value="store_credit" @checked(old('preference') === 'store_credit')> Store Credit</label>
                </fieldset>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label for="name">Your Name</label>
                        <input class="form-control" id="name" name="name" autocomplete="name" maxlength="120" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="email">Email</label>
                        <input class="form-control" type="email" id="email" name="email" autocomplete="email" maxlength="254" value="{{ old('email') }}" required>
                    </div>
                </div>
                <button class="button button-dark w-100" type="submit">Request a Quote</button>
            </form>
        </div>
    </div>
</div>
@endsection
