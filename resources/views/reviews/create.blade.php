<x-layouts.app title="Остави отзив" :noindex="true">
    <div class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight">Как мина работата?</h1>
        <p class="mt-3 text-ink-600">
            Заявка № {{ $assignment->lead->reference }} с
            <strong class="text-ink-900">{{ $assignment->providerProfile->company_name }}</strong>.
            Отзивът се публикува след преглед от нашия екип.
        </p>

        <x-ui.card class="mt-8">
            <form method="POST" action="{{ request()->fullUrl() }}" class="space-y-5">
                @csrf

                <fieldset x-data="{ rating: {{ (int) old('rating', 5) }} }">
                    <legend class="mb-2 text-sm font-medium text-ink-800">Оценка</legend>
                    <div class="flex gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $i }}" class="sr-only"
                                       x-model.number="rating" @checked((int) old('rating', 5) === $i)>
                                <svg class="h-9 w-9 transition" :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-ink-200'"
                                     fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.28 3.95a1 1 0 0 0 .95.69h4.15c.97 0 1.37 1.24.59 1.81l-3.36 2.44a1 1 0 0 0-.36 1.12l1.28 3.95c.3.92-.75 1.69-1.54 1.12l-3.36-2.44a1 1 0 0 0-1.17 0l-3.36 2.44c-.79.57-1.84-.2-1.54-1.12l1.28-3.95a1 1 0 0 0-.36-1.12L2.07 9.38c-.78-.57-.38-1.81.59-1.81h4.15a1 1 0 0 0 .95-.69l1.29-3.95Z" />
                                </svg>
                                <span class="sr-only">{{ $i }} от 5</span>
                            </label>
                        @endfor
                    </div>
                    <x-ui.error name="rating" />
                </fieldset>

                <x-ui.input name="author_name" label="Твоето име" required
                            :value="old('author_name', $assignment->lead->contact_name)" />

                <x-ui.textarea name="comment" label="Коментар" rows="5"
                               placeholder="Как мина работата? Дойдоха ли навреме? Как беше техниката?" />

                <x-ui.button size="lg" class="w-full">Изпрати отзива</x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
