<x-layouts.app
    title="Регистрация на доставчик"
    description="Създай профил на доставчик и получавай заявки за техника в твоя район."
    :noindex="true"
>
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <h1 class="text-3xl font-bold tracking-tight">Регистрирай се като доставчик</h1>
        <p class="mt-2 text-ink-500">
            Клиентите не се нуждаят от профил — за да заявиш техника,
            <a href="{{ route('leads.create') }}" class="font-medium text-brand-700 hover:underline">използвай формата за заявка</a>.
        </p>

        <x-ui.card class="mt-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                <x-ui.input name="name" label="Име и фамилия" required autocomplete="name" autofocus />
                <x-ui.input name="email" type="email" label="Имейл" required autocomplete="email" />
                <x-ui.input name="phone" type="tel" label="Телефон" required autocomplete="tel" placeholder="0888 123 456" />
                <x-ui.input name="password" type="password" label="Парола" required autocomplete="new-password"
                            hint="Поне 8 символа, с букви и цифри." />
                <x-ui.input name="password_confirmation" type="password" label="Потвърди паролата" required autocomplete="new-password" />

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="terms" value="1" required @checked(old('terms'))
                           class="mt-0.5 h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                    <span class="text-sm text-ink-700">
                        Съгласявам се с <a href="{{ route('terms') }}" target="_blank" class="font-medium text-brand-700 underline">Общите условия</a>
                        и <a href="{{ route('privacy') }}" target="_blank" class="font-medium text-brand-700 underline">Политиката за поверителност</a>.
                    </span>
                </label>
                <x-ui.error name="terms" />

                <x-ui.button size="lg" class="w-full">Създай профил</x-ui.button>
            </form>
        </x-ui.card>

        <p class="mt-6 text-center text-sm text-ink-500">
            Вече имаш профил? <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">Влез</a>
        </p>
    </div>
</x-layouts.app>
