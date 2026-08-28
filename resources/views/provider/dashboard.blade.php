<x-layouts.dashboard title="Табло" heading="Обобщение">
    @unless ($profile->isApproved())
        <x-ui.alert type="warning" class="mb-6">
            <p class="font-medium">Профилът ти още не е одобрен.</p>
            <p class="mt-1">
                @if ($profile->submitted_at)
                    Изпратен е за преглед на {{ $profile->submitted_at->format('d.m.Y') }}. Ще получиш имейл, когато бъде одобрен.
                @else
                    Добави техника и обслужвани райони, след което изпрати профила за одобрение.
                @endif
            </p>
            @unless ($profile->submitted_at)
                <form method="POST" action="{{ route('provider.profile.submit') }}" class="mt-3">
                    @csrf
                    <x-ui.button size="sm">Изпрати за одобрение</x-ui.button>
                </form>
            @endunless
        </x-ui.alert>
    @endunless

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat label="Получени заявки" :value="$stats['total']" />
        <x-ui.stat label="Нови заявки" :value="$stats['new']" accent hint="Чакат твоя отговор" />
        <x-ui.stat label="Приети заявки" :value="$stats['accepted']" />
        <x-ui.stat label="Завършени заявки" :value="$stats['completed']" />
        <x-ui.stat label="Заявки този месец" :value="$stats['this_month']" />
        <x-ui.stat label="Посещения на профила" :value="$stats['profile_views']" />
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <x-ui.card>
            <h2 class="text-sm font-medium text-ink-500">Моята техника</h2>
            <p class="mt-1 text-2xl font-semibold">{{ $profile->equipment_count }}</p>
            <a href="{{ route('provider.equipment.index') }}" class="mt-3 inline-block text-sm font-semibold text-brand-700">Управлявай →</a>
        </x-ui.card>
        <x-ui.card>
            <h2 class="text-sm font-medium text-ink-500">Обслужвани градове</h2>
            <p class="mt-1 text-2xl font-semibold">{{ $profile->service_areas_count }}</p>
            <a href="{{ route('provider.areas.edit') }}" class="mt-3 inline-block text-sm font-semibold text-brand-700">Редактирай →</a>
        </x-ui.card>
        <x-ui.card>
            <h2 class="text-sm font-medium text-ink-500">План</h2>
            <p class="mt-1 text-2xl font-semibold">{{ $profile->plan()->label() }}</p>
            <p class="mt-3 text-sm text-ink-500">
                Заявки на месец: {{ $profile->planSetting('monthly_lead_quota') ?? 'без ограничение' }}
            </p>
        </x-ui.card>
    </div>

    <section class="mt-10">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Последни заявки</h2>
            <a href="{{ route('provider.leads.index') }}" class="text-sm font-semibold text-brand-700">Виж всички →</a>
        </div>

        @if ($latest->isEmpty())
            <div class="mt-4">
                <x-ui.empty title="Още нямаш заявки"
                            description="Увери се, че профилът ти е одобрен, техниката е добавена и си посочил обслужваните градове." />
            </div>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($latest as $assignment)
                    <a href="{{ route('provider.leads.show', $assignment) }}"
                       class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-ink-200 bg-white p-4 transition hover:border-brand-300">
                        <div class="min-w-0">
                            <p class="font-medium text-ink-900">
                                {{ $assignment->lead->categoryLabel() }}
                                @if ($assignment->lead->equipment_type)
                                    <span class="text-ink-500">— {{ $assignment->lead->equipment_type }}</span>
                                @endif
                            </p>
                            <p class="mt-0.5 text-sm text-ink-500">
                                {{ $assignment->lead->locationLabel() }} · {{ $assignment->lead->dateLabel() }}
                            </p>
                        </div>
                        <x-ui.badge :color="$assignment->status->badgeClasses()">{{ $assignment->status->label() }}</x-ui.badge>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.dashboard>
