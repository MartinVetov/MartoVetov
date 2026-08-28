@php use App\Enums\ReviewStatus; @endphp

<x-layouts.dashboard title="Отзиви" heading="Модерация на отзиви" area="admin">
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-64">
            <x-ui.select name="status" label="Статус"
                         :options="collect(ReviewStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])"
                         :value="request('status', ReviewStatus::Pending->value)" />
        </div>
        <x-ui.button variant="secondary">Филтрирай</x-ui.button>
    </form>

    @if ($reviews->isEmpty())
        <x-ui.empty title="Няма отзиви за преглед" />
    @else
        <div class="space-y-4">
            @foreach ($reviews as $review)
                <x-ui.card>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-ink-900">{{ $review->author_name }}</p>
                            <p class="mt-0.5 text-sm text-ink-500">
                                за <a href="{{ route('admin.providers.show', $review->providerProfile) }}"
                                      class="font-medium text-brand-700 hover:underline">{{ $review->providerProfile->company_name }}</a>
                                @if ($review->lead)
                                    · заявка {{ $review->lead->reference }}
                                @endif
                                · {{ $review->created_at->format('d.m.Y') }}
                            </p>
                        </div>
                        <x-ui.rating :rating="$review->rating" />
                    </div>

                    @if ($review->comment)
                        <p class="mt-4 whitespace-pre-line text-ink-700">{{ $review->comment }}</p>
                    @endif

                    <form method="POST" action="{{ route('admin.reviews.update', $review) }}"
                          class="mt-5 flex flex-wrap items-end gap-3 border-t border-ink-100 pt-5">
                        @csrf @method('PATCH')
                        <div class="w-full sm:w-48">
                            <x-ui.select name="status" label="Статус"
                                         :options="collect(ReviewStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])"
                                         :value="$review->status->value" required />
                        </div>
                        <div class="w-full sm:flex-1">
                            <x-ui.input name="moderation_note" label="Бележка (вътрешна)" :value="$review->moderation_note" />
                        </div>
                        <x-ui.button>Запази</x-ui.button>
                    </form>
                </x-ui.card>
            @endforeach
        </div>

        <div class="mt-6">{{ $reviews->links() }}</div>
    @endif
</x-layouts.dashboard>
