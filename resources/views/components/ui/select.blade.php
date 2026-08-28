@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null, 'required' => false])

@php $id = $attributes->get('id') ?? $name; @endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-ink-800">
            {{ $label }} @if ($required)<span class="text-brand-600" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $id }}"
        @if ($required) required @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border-0 px-3.5 py-2.5 text-ink-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset focus:ring-brand-600 text-base sm:text-sm '
                .($errors->has($name) ? 'ring-rose-400' : 'ring-ink-300'),
        ]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($hint)
        <p class="mt-1.5 text-sm text-ink-500">{{ $hint }}</p>
    @endif

    <x-ui.error :name="$name" :id="$id" />
</div>
