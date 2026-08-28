@props(['title' => null, 'heading' => null, 'area' => 'provider'])

@php
    $navigation = $area === 'admin'
        ? [
            ['label' => 'Табло', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ['label' => 'Заявки', 'route' => 'admin.leads.index', 'active' => 'admin.leads.*'],
            ['label' => 'Доставчици', 'route' => 'admin.providers.index', 'active' => 'admin.providers.*'],
            ['label' => 'Категории', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
            ['label' => 'Градове', 'route' => 'admin.cities.index', 'active' => 'admin.cities.*'],
            ['label' => 'Отзиви', 'route' => 'admin.reviews.index', 'active' => 'admin.reviews.*'],
        ]
        : [
            ['label' => 'Табло', 'route' => 'provider.dashboard', 'active' => 'provider.dashboard'],
            ['label' => 'Моите заявки', 'route' => 'provider.leads.index', 'active' => 'provider.leads.*'],
            ['label' => 'Моята техника', 'route' => 'provider.equipment.index', 'active' => 'provider.equipment.*'],
            ['label' => 'Обслужвани райони', 'route' => 'provider.areas.edit', 'active' => 'provider.areas.*'],
            ['label' => 'Профил на фирмата', 'route' => 'provider.profile.edit', 'active' => 'provider.profile.*'],
        ];
@endphp

<!DOCTYPE html>
<html lang="bg" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ? $title.' | ' : '' }}{{ $area === 'admin' ? 'Администрация' : 'Профил на доставчик' }} — {{ config('nt.brand') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-ink-50" x-data="{ nav: false }">
    <header class="border-b border-ink-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <button type="button" class="-ml-2 p-2 text-ink-600 lg:hidden" @click="nav = !nav" aria-label="Меню">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">НТ</span>
                    <span class="hidden text-base font-semibold text-ink-900 sm:block">
                        {{ $area === 'admin' ? 'Администрация' : 'Намери Техник' }}
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-ink-500 sm:block">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button variant="ghost" size="sm">Изход</x-ui.button>
                </form>
            </div>
        </div>
    </header>

    <div class="mx-auto flex max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <aside class="hidden w-56 shrink-0 lg:block">
            <nav class="space-y-1" aria-label="Навигация в профила">
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'block rounded-lg px-3 py-2 text-sm font-medium transition',
                           'bg-brand-50 text-brand-800' => request()->routeIs($item['active']),
                           'text-ink-600 hover:bg-white hover:text-ink-900' => ! request()->routeIs($item['active']),
                       ])>{{ $item['label'] }}</a>
                @endforeach
            </nav>
        </aside>

        <div x-show="nav" x-cloak x-transition class="fixed inset-x-0 top-14 z-30 border-b border-ink-200 bg-white p-4 lg:hidden">
            <nav class="space-y-1" aria-label="Мобилна навигация в профила">
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'block rounded-lg px-3 py-2 text-sm font-medium',
                           'bg-brand-50 text-brand-800' => request()->routeIs($item['active']),
                           'text-ink-700' => ! request()->routeIs($item['active']),
                       ])>{{ $item['label'] }}</a>
                @endforeach
            </nav>
        </div>

        <main class="min-w-0 flex-1">
            @if ($heading)
                <h1 class="mb-6 text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">{{ $heading }}</h1>
            @endif

            <div class="space-y-4">
                @foreach (['success', 'error', 'info', 'warning'] as $type)
                    @if (session($type))
                        <x-ui.alert :type="$type">{{ session($type) }}</x-ui.alert>
                    @endif
                @endforeach

                @if ($errors->any() && $errors->count() > 1)
                    <x-ui.alert type="error">
                        <p class="font-medium">Провери въведените данни:</p>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @endif
            </div>

            <div class="mt-6">{{ $slot }}</div>
        </main>
    </div>
</body>
</html>
