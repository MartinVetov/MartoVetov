@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'rows' => 5, 'required' => false])

@php $id = $attributes->get('id') ?? $name; @endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-ink-800">
            {{ $label }} @if ($required)<span class="text-brand-600" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border-0 px-3.5 py-2.5 text-ink-900 shadow-sm ring-1 ring-inset placeholder:text-ink-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 text-base sm:text-sm '
                .($errors->has($name) ? 'ring-rose-400' : 'ring-ink-300'),
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($hint)
        <p class="mt-1.5 text-sm text-ink-500">{{ $hint }}</p>
    @endif

    <x-ui.error :name="$name" :id="$id" />
</div>
