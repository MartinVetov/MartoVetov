<x-layouts.app title="Заявката е получена" :noindex="true">
    <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-ink-200 bg-white p-8 text-center shadow-sm sm:p-12">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
                <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </div>

            <h1 class="mt-6 text-3xl font-bold tracking-tight">Готово!</h1>
            <p class="mt-3 text-lg text-ink-600">Получихме твоята заявка.</p>
            <p class="mt-1 text-ink-500">
                Ще я насочим към подходящи доставчици на техника в твоя район.
            </p>

            <div class="mt-8 rounded-xl border border-ink-200 bg-ink-50 p-5">
                <p class="text-sm text-ink-500">Номер на заявката</p>
                <p class="mt-1 text-2xl font-semibold tracking-tight text-ink-900">Заявка № {{ $lead->reference }}</p>
                <p class="mt-2 text-sm text-ink-500">Запази този номер — той ще ти трябва при въпроси.</p>
            </div>

            <dl class="mt-8 space-y-3 text-left">
                <div class="flex justify-between gap-4 border-b border-ink-100 pb-3">
                    <dt class="text-sm text-ink-500">Техника</dt>
                    <dd class="text-sm font-medium text-ink-900">{{ $lead->categoryLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-ink-100 pb-3">
                    <dt class="text-sm text-ink-500">Локация</dt>
                    <dd class="text-sm font-medium text-ink-900">{{ $lead->locationLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-ink-100 pb-3">
                    <dt class="text-sm text-ink-500">Дата</dt>
                    <dd class="text-sm font-medium text-ink-900">{{ $lead->dateLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-sm text-ink-500">Оператор</dt>
                    <dd class="text-sm font-medium text-ink-900">{{ $lead->operator_required->shortLabel() }}</dd>
                </div>
            </dl>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <x-ui.button href="{{ route('home') }}" variant="outline">Към началната страница</x-ui.button>
                <x-ui.button href="{{ route('categories.index') }}">Разгледай категориите техника</x-ui.button>
            </div>
        </div>
    </div>

    @push('head')
        <script>window.addEventListener('load', () => window.ntTrack?.('lead_form_completed'));</script>
    @endpush
</x-layouts.app>
