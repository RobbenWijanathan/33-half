@props(['product'])

<section class="record-condition" aria-labelledby="condition-heading">
    <h2 id="condition-heading">Record &amp; sleeve condition</h2>
    <p>The record grade describes the vinyl and its playback. The sleeve grade describes the outer cover.</p>
    <dl class="grade-boxes">
        @foreach(['Record' => $product->record_grade, 'Sleeve' => $product->sleeve_grade] as $part => $grade)
            @php([$label, $description] = \App\Support\RecordGrades::description($grade, $product->condition, strtolower($part)))
            <div>
                <dt>{{ $part }}</dt>
                <dd>
                    <strong>{{ $label }}</strong>
                    <span>{{ $description }}</span>
                </dd>
            </div>
        @endforeach
    </dl>
</section>
