<x-layouts.dashboard title="Профил на фирмата" heading="Профил на фирмата">
    <div class="mb-6 flex flex-wrap items-center gap-2">
        @if ($profile->verified)
            <x-ui.badge color="bg-emerald-50 text-emerald-800 ring-emerald-200">✓ Проверен доставчик</x-ui.badge>
        @endif
        <x-ui.badge :color="$profile->isApproved() ? 'bg-emerald-50 text-emerald-800 ring-emerald-200' : 'bg-amber-50 text-amber-800 ring-amber-200'">
            {{ $profile->isApproved() ? 'Одобрен профил' : 'Чака одобрение' }}
        </x-ui.badge>
        @if ($profile->isApproved())
            <a href="{{ route('providers.show', $profile) }}" class="text-sm font-semibold text-brand-700 hover:underline">
                Виж публичния профил →
            </a>
        @endif
    </div>

    @include('provider.profile._form')
</x-layouts.dashboard>
