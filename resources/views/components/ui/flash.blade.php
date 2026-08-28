@php $types = ['success', 'error', 'info', 'warning']; @endphp

@foreach ($types as $type)
    @if (session($type))
        <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
            <x-ui.alert :type="$type">{{ session($type) }}</x-ui.alert>
        </div>
    @endif
@endforeach
