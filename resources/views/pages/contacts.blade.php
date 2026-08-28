<x-layouts.app
    title="Контакти"
    description="Свържи се с екипа на „Намери Техник“."
    :breadcrumbs="[['label' => 'Начало', 'url' => route('home')], ['label' => 'Контакти']]"
>
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold tracking-tight">Контакти</h1>
        <p class="mt-4 text-lg text-ink-600">
            Имаш въпрос за заявка или искаш да предлагаш техника? Пиши ни.
        </p>

        <dl class="mt-8 space-y-4">
            <div class="rounded-xl border border-ink-200 bg-white p-5">
                <dt class="text-sm text-ink-500">Имейл</dt>
                <dd class="mt-1 text-lg font-semibold text-ink-900">{{ config('nt.contact.email') }}</dd>
            </div>
            <div class="rounded-xl border border-ink-200 bg-white p-5">
                <dt class="text-sm text-ink-500">Телефон</dt>
                <dd class="mt-1 text-lg font-semibold text-ink-900">{{ config('nt.contact.phone') }}</dd>
            </div>
        </dl>

        <div class="mt-10 flex flex-col gap-3 sm:flex-row">
            <x-ui.button href="{{ route('leads.create') }}" size="lg">Намери техника</x-ui.button>
            <x-ui.button href="{{ route('for-providers') }}" size="lg" variant="outline">Предлагам техника</x-ui.button>
        </div>
    </div>
</x-layouts.app>
