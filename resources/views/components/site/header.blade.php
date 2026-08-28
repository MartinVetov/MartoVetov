<header class="sticky top-0 z-40 border-b border-ink-200 bg-white/95 backdrop-blur" x-data="{ open: false }">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ config('nt.brand') }} — начало">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white font-bold">НТ</span>
            <span class="text-lg font-semibold tracking-tight text-ink-900">Намери Техник</span>
        </a>

        <nav class="hidden items-center gap-7 text-sm font-medium text-ink-600 lg:flex" aria-label="Основна навигация">
            <a href="{{ route('categories.index') }}" class="hover:text-ink-900">Категории техника</a>
            <a href="{{ route('providers.index') }}" class="hover:text-ink-900">Доставчици</a>
            <a href="{{ route('how-it-works') }}" class="hover:text-ink-900">Как работи</a>
            <a href="{{ route('for-providers') }}" class="hover:text-ink-900">Предлагам техника</a>
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('provider.dashboard') }}"
                   class="text-sm font-medium text-ink-600 hover:text-ink-900">Моят профил</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-ink-600 hover:text-ink-900">Вход</a>
            @endauth
            <x-ui.button href="{{ route('leads.create') }}" size="sm">Намери техника</x-ui.button>
        </div>

        <button type="button" class="lg:hidden -mr-2 p-2 text-ink-700" @click="open = !open"
                :aria-expanded="open" aria-controls="mobile-nav" aria-label="Меню">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div id="mobile-nav" x-show="open" x-cloak x-transition class="border-t border-ink-200 bg-white lg:hidden">
        <nav class="space-y-1 px-4 py-4 text-base font-medium text-ink-700" aria-label="Мобилна навигация">
            <a href="{{ route('categories.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ink-50">Категории техника</a>
            <a href="{{ route('providers.index') }}" class="block rounded-lg px-3 py-2 hover:bg-ink-50">Доставчици</a>
            <a href="{{ route('how-it-works') }}" class="block rounded-lg px-3 py-2 hover:bg-ink-50">Как работи</a>
            <a href="{{ route('for-providers') }}" class="block rounded-lg px-3 py-2 hover:bg-ink-50">Предлагам техника</a>
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('provider.dashboard') }}"
                   class="block rounded-lg px-3 py-2 hover:bg-ink-50">Моят профил</a>
            @else
                <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 hover:bg-ink-50">Вход</a>
            @endauth
            <div class="pt-2">
                <x-ui.button href="{{ route('leads.create') }}" class="w-full justify-center">Намери техника</x-ui.button>
            </div>
        </nav>
    </div>
</header>
