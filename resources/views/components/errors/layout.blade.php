@props(['code', 'title', 'message'])

<x-layouts.app :title="$title" :noindex="true">
    <div class="mx-auto max-w-xl px-4 py-24 text-center sm:px-6">
        <p class="text-6xl font-bold tracking-tight text-brand-600">{{ $code }}</p>
        <h1 class="mt-4 text-3xl font-bold tracking-tight">{{ $title }}</h1>
        <p class="mt-3 text-ink-600">{{ $message }}</p>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <x-ui.button href="{{ route('home') }}">Към началната страница</x-ui.button>
            <x-ui.button href="{{ route('leads.create') }}" variant="outline">Намери техника</x-ui.button>
        </div>
    </div>
</x-layouts.app>
