<footer class="mt-20 border-t border-ink-200 bg-ink-50">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-4">
            <div class="md:col-span-1">
                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white font-bold">НТ</span>
                    <span class="text-lg font-semibold text-ink-900">Намери Техник</span>
                </div>
                <p class="mt-3 text-sm text-ink-500">
                    Не търси техника. Кажи какво трябва да свършиш — ние ще намерим подходящия доставчик.
                </p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-ink-900">Техника</h3>
                <ul class="mt-3 space-y-2 text-sm text-ink-600">
                    @foreach ($categories as $footerCategory)
                        <li><a class="hover:text-brand-700" href="{{ route('categories.show', $footerCategory) }}">{{ $footerCategory->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-ink-900">Платформа</h3>
                <ul class="mt-3 space-y-2 text-sm text-ink-600">
                    <li><a class="hover:text-brand-700" href="{{ route('how-it-works') }}">Как работи</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('for-providers') }}">Предлагам техника</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('providers.index') }}">Доставчици</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('contacts') }}">Контакти</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-ink-900">Документи</h3>
                <ul class="mt-3 space-y-2 text-sm text-ink-600">
                    <li><a class="hover:text-brand-700" href="{{ route('terms') }}">Общи условия</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('privacy') }}">Политика за поверителност</a></li>
                </ul>
                <p class="mt-4 text-sm text-ink-500">{{ config('nt.contact.email') }}</p>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-2 border-t border-ink-200 pt-6 text-sm text-ink-500 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ config('nt.brand') }}. Всички права запазени.</p>
            <p>Платформа за наем на специализирана техника в България.</p>
        </div>
    </div>
</footer>
