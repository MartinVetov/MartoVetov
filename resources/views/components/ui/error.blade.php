@props(['name', 'id' => null])

@error($name)
    <p id="{{ $id ?? $name }}-error" class="mt-1.5 text-sm font-medium text-rose-600">{{ $message }}</p>
@enderror
