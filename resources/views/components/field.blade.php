@props(['name', 'label', 'value' => '', 'type' => 'text'])
<div class="mb-3">
    <label class="form-label" for="{{ $attributes->get('id', $name) }}">{{ $label }}</label>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        <input {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
            id="{{ $attributes->get('id', $name) }}" name="{{ $name }}" type="{{ $type }}"
            value="{{ $type === 'password' ? '' : old($name, $value) }}">
    @endif
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
