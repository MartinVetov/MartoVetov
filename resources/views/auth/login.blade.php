<x-layouts.app title="Вход" :noindex="true">
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <h1 class="text-3xl font-bold tracking-tight">Вход в профила</h1>
        <p class="mt-2 text-ink-500">За доставчици и администратори на платформата.</p>

        <x-ui.card class="mt-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <x-ui.input name="email" type="email" label="Имейл" required autocomplete="email" autofocus />
                <x-ui.input name="password" type="password" label="Парола" required autocomplete="current-password" />

                <label class="flex items-center gap-3">
                    <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                    <span class="text-sm text-ink-700">Запомни ме</span>
                </label>

                <x-ui.button size="lg" class="w-full">Влез</x-ui.button>
            </form>
        </x-ui.card>

        <p class="mt-6 text-center text-sm text-ink-500">
            Нямаш профил?
            <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:underline">Регистрирай се като доставчик</a>
        </p>
    </div>
</x-layouts.app>
