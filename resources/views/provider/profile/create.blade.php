<x-layouts.dashboard title="Профил на фирмата" heading="Създай профил на фирмата">
    <p class="mb-6 max-w-2xl text-ink-600">
        Тези данни се показват на клиентите и определят кои заявки ще получаваш.
        След това добави техниката си и изпрати профила за одобрение.
    </p>

    @include('provider.profile._form', ['profile' => new \App\Models\ProviderProfile])
</x-layouts.dashboard>
