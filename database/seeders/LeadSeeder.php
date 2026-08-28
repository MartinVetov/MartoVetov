<?php

namespace Database\Seeders;

use App\Enums\LeadDuration;
use App\Enums\LeadProviderStatus;
use App\Enums\LeadStatus;
use App\Enums\OperatorRequirement;
use App\Enums\PaymentStatus;
use App\Enums\ReviewStatus;
use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\Lead;
use App\Models\LeadProvider;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Review;
use App\Services\LeadMatchingService;
use Illuminate\Database\Seeder;

/**
 * Примерни заявки в различни състояния, за да е видима цялата фуния
 * още при първо стартиране.
 */
class LeadSeeder extends Seeder
{
    public function run(LeadMatchingService $matching): void
    {
        $cities = City::all()->keyBy('slug');
        $categories = EquipmentCategory::whereNull('parent_id')->get()->keyBy('slug');

        foreach ($this->leads() as $index => $data) {
            $category = $data['category'] ? $categories[$data['category']] : null;

            $lead = Lead::create([
                'equipment_category_id' => $category?->id,
                'category_unknown' => $data['category'] === null,
                'equipment_type' => $data['type'] ?? null,
                'description' => $data['description'],
                'city_id' => $cities[$data['city']]->id,
                'district' => $data['district'] ?? null,
                'requested_date' => now()->addDays($data['in_days']),
                'requested_time' => $data['time'] ?? null,
                'duration' => $data['duration'],
                'operator_required' => $data['operator'],
                'details' => $data['details'] ?? null,
                'contact_name' => $data['name'],
                'contact_phone' => $data['phone'],
                'contact_email' => $data['email'],
                'status' => $data['status'],
                'price' => $category?->leadPrice(),
                'consent_at' => now()->subDays(20 - $index),
                'ip_address' => '212.5.'.random_int(1, 254).'.'.random_int(1, 254),
                'source' => 'seed',
                'created_at' => now()->subDays(20 - $index),
                'updated_at' => now()->subDays(20 - $index),
            ]);

            if ($data['status'] === LeadStatus::New) {
                continue;
            }

            // Изпращаме заявката към най-подходящите доставчици.
            $matches = $matching->candidates($lead, 3);

            foreach ($matches as $position => $match) {
                $status = match (true) {
                    $data['status'] === LeadStatus::Completed && $position === 0 => LeadProviderStatus::Completed,
                    $data['status'] === LeadStatus::Accepted && $position === 0 => LeadProviderStatus::Accepted,
                    $data['status'] === LeadStatus::Contacted && $position === 0 => LeadProviderStatus::Contacted,
                    $position === 0 => LeadProviderStatus::Viewed,
                    default => LeadProviderStatus::Sent,
                };

                $assignment = LeadProvider::create([
                    'lead_id' => $lead->id,
                    'provider_profile_id' => $match->provider->id,
                    'status' => $status,
                    'match_score' => $match->score,
                    'price' => $lead->leadPrice(),
                    'sent_at' => $lead->created_at->addHours(2),
                    'viewed_at' => $position === 0 ? $lead->created_at->addHours(5) : null,
                    'accepted_at' => in_array($status, [LeadProviderStatus::Accepted, LeadProviderStatus::Contacted, LeadProviderStatus::Completed], true)
                        ? $lead->created_at->addHours(6) : null,
                    'contacted_at' => in_array($status, [LeadProviderStatus::Contacted, LeadProviderStatus::Completed], true)
                        ? $lead->created_at->addHours(8) : null,
                    'completed_at' => $status === LeadProviderStatus::Completed ? $lead->created_at->addDays(3) : null,
                ]);

                if (in_array($status, [LeadProviderStatus::Accepted, LeadProviderStatus::Contacted, LeadProviderStatus::Completed], true)) {
                    Payment::create([
                        'provider_profile_id' => $match->provider->id,
                        'lead_id' => $lead->id,
                        'amount' => $lead->leadPrice(),
                        'currency' => config('nt.lead_price.currency'),
                        'status' => $status === LeadProviderStatus::Completed ? PaymentStatus::Paid : PaymentStatus::Pending,
                        'provider' => 'manual',
                        'paid_at' => $status === LeadProviderStatus::Completed ? $lead->created_at->addDays(4) : null,
                    ]);
                }

                if ($status === LeadProviderStatus::Completed && ! empty($data['review'])) {
                    Review::create([
                        'provider_profile_id' => $match->provider->id,
                        'lead_id' => $lead->id,
                        'author_name' => $data['name'],
                        'rating' => $data['review']['rating'],
                        'comment' => $data['review']['comment'],
                        'status' => $data['review']['status'],
                        'created_at' => $lead->created_at->addDays(5),
                    ]);
                }

                unset($assignment);
            }

            $lead->forceFill(['sent_count' => $matches->count()])->save();
        }

        $this->refreshRatings();
    }

    protected function refreshRatings(): void
    {
        foreach (ProviderProfile::with('reviews')->get() as $provider) {
            $approved = $provider->reviews->where('status', ReviewStatus::Approved);

            $provider->forceFill([
                'rating_count' => $approved->count(),
                'rating_avg' => $approved->isEmpty() ? 0 : round($approved->avg('rating'), 2),
            ])->save();
        }
    }

    protected function leads(): array
    {
        return [
            [
                'category' => 'mini-bager', 'type' => '2 – 5 тона', 'city' => 'sofia', 'district' => 'Драгалевци',
                'description' => 'Трябва ми изкоп за основи на къща — приблизително 30 линейни метра, дълбочина около 1,2 м. Дворът е с тесен вход, около 2,5 м.',
                'in_days' => 6, 'time' => 'сутринта', 'duration' => LeadDuration::TwoThreeDays,
                'operator' => OperatorRequirement::Required, 'status' => LeadStatus::Completed,
                'details' => ['depth' => '1-2m', 'length' => '30 линейни метра', 'access' => 'ogranichen', 'entrance_width' => '2,50 м', 'terrain' => 'zemya'],
                'name' => 'Милен Христов', 'phone' => '0888456789', 'email' => 'milen.hristov@example.bg',
                'review' => ['rating' => 5, 'comment' => 'Дойдоха точно на уговорения час, работиха бързо и оставиха двора чист. Операторът беше много прецизен около оградата.', 'status' => ReviewStatus::Approved],
            ],
            [
                'category' => 'avtovishka', 'type' => '15 – 25 м', 'city' => 'sofia', 'district' => 'Лозенец',
                'description' => 'Трябва да се подменят улуци и да се почисти покрив на четириетажна сграда. Има място за паркиране пред входа.',
                'in_days' => 3, 'time' => '10:00', 'duration' => LeadDuration::Hours,
                'operator' => OperatorRequirement::Required, 'status' => LeadStatus::Contacted,
                'details' => ['height' => '15-25m', 'access' => 'lesen', 'load_weight' => '2 души с инструменти', 'usage' => 'vanshno'],
                'name' => 'Албена Стоева', 'phone' => '0887334455', 'email' => 'a.stoeva@example.bg',
            ],
            [
                'category' => null, 'city' => 'plovdiv', 'district' => 'Кючук Париж',
                'description' => 'Имам стар навес и много строителни отпадъци в двора. Трябва да се събори и да се извози всичко. Не знам каква техника е нужна.',
                'in_days' => 10, 'duration' => LeadDuration::OneDay,
                'operator' => OperatorRequirement::Unknown, 'status' => LeadStatus::Sent,
                'name' => 'Спас Тодоров', 'phone' => '0899887766', 'email' => 'spas.todorov@example.bg',
            ],
            [
                'category' => 'samosval', 'type' => 'Среден (до 12 т)', 'city' => 'varna',
                'description' => 'Трябва да се извозят около 40 куб. м изкопни земни маси от строителен обект. Достъпът е добър, има място за маневриране.',
                'in_days' => 4, 'duration' => LeadDuration::TwoThreeDays,
                'operator' => OperatorRequirement::Required, 'status' => LeadStatus::Accepted,
                'details' => ['material' => 'изкопни земни маси', 'volume' => '40 куб. м', 'trips' => '5-10', 'access' => 'lesen'],
                'name' => 'Веселин Иванов', 'phone' => '0876112233', 'email' => 'v.ivanov@example.bg',
            ],
            [
                'category' => 'mini-bager', 'type' => 'До 2 тона', 'city' => 'burgas',
                'description' => 'Трябва изкоп за басейн в готов двор — около 8 на 4 метра, дълбочина 1,5 м. Входът към двора е 1,10 м.',
                'in_days' => 14, 'duration' => LeadDuration::TwoThreeDays,
                'operator' => OperatorRequirement::Required, 'status' => LeadStatus::Sent,
                'details' => ['depth' => '1-2m', 'length' => '8 x 4 м', 'access' => 'truden', 'entrance_width' => '1,10 м', 'terrain' => 'pyasak'],
                'name' => 'Диана Радева', 'phone' => '0894556677', 'email' => 'diana.radeva@example.bg',
            ],
            [
                'category' => 'tovarach', 'type' => 'Мини товарач', 'city' => 'plovdiv',
                'description' => 'Разчистване на двор от чакъл и пръст, около 200 кв. м. Трябва и подравняване преди да се сложи настилка.',
                'in_days' => 8, 'duration' => LeadDuration::OneDay,
                'operator' => OperatorRequirement::Required, 'status' => LeadStatus::Completed,
                'details' => ['work_type' => 'разчистване и подравняване', 'access' => 'lesen', 'terrain' => 'raven'],
                'name' => 'Огнян Петков', 'phone' => '0885998877', 'email' => 'ognyan.p@example.bg',
                'review' => ['rating' => 4, 'comment' => 'Добра работа, малко закъсняха сутринта, но свършиха всичко за деня.', 'status' => ReviewStatus::Approved],
            ],
            [
                'category' => 'generator', 'type' => '10 – 60 kVA', 'city' => 'ruse',
                'description' => 'Трябва генератор за строителен обект без захранване. Ще работят бетонобъркачка, две ъглошлайфа и осветление.',
                'in_days' => 5, 'duration' => LeadDuration::MoreThanThreeDays,
                'operator' => OperatorRequirement::NotRequired, 'status' => LeadStatus::Opened,
                'details' => ['power' => 'около 20 kVA', 'phase' => 'trifazno', 'fuel_included' => 'ne-znam'],
                'name' => 'Мартин Симеонов', 'phone' => '0878445511', 'email' => 'm.simeonov@example.bg',
            ],
            [
                'category' => 'mini-bager', 'type' => '2 – 5 тона', 'city' => 'stara-zagora',
                'description' => 'Авария на водопровод в двора. Трябва спешно изкоп около 6 метра, дълбочина около метър.',
                'in_days' => 1, 'time' => 'възможно най-скоро', 'duration' => LeadDuration::Hours,
                'operator' => OperatorRequirement::Required, 'status' => LeadStatus::New,
                'details' => ['depth' => 'do-1m', 'length' => '6 метра', 'access' => 'lesen', 'terrain' => 'zemya'],
                'name' => 'Ивелина Костова', 'phone' => '0899223344', 'email' => 'ivelina.k@example.bg',
            ],
            [
                'category' => 'teleskopichen-tovarach', 'city' => 'veliko-tarnovo',
                'description' => 'Трябва да се качат покривни ферми на височина около 9 метра. Общо 14 броя, всяка около 300 кг.',
                'in_days' => 12, 'duration' => LeadDuration::OneDay,
                'operator' => OperatorRequirement::Required, 'status' => LeadStatus::New,
                'details' => ['height' => '9 м', 'load_weight' => '300 кг на ферма', 'access' => 'lesen'],
                'name' => 'Борислав Ников', 'phone' => '0882776655', 'email' => 'b.nikov@example.bg',
            ],
            [
                'category' => 'avtovishka', 'type' => 'До 15 м', 'city' => 'pleven',
                'description' => 'Трябва да се окастрят два големи ореха в двора. Клоните са над съседния покрив, така че трябва внимание.',
                'in_days' => 7, 'duration' => LeadDuration::Hours,
                'operator' => OperatorRequirement::Required, 'status' => LeadStatus::New,
                'details' => ['height' => 'do-15m', 'access' => 'ogranichen', 'usage' => 'vanshno'],
                'name' => 'Галя Маринова', 'phone' => '0897334422', 'email' => 'galya.m@example.bg',
            ],
            [
                'category' => null, 'city' => 'sofia', 'district' => 'Обеля',
                'description' => 'Трябва да се изкопае и подготви основа за монтаж на метален гараж 6 на 3 метра. Не знам дали ми трябва багер или само товарач.',
                'in_days' => 9, 'duration' => LeadDuration::OneDay,
                'operator' => OperatorRequirement::Any, 'status' => LeadStatus::Verified,
                'name' => 'Николета Драганова', 'phone' => '0886554433', 'email' => 'nikoleta.d@example.bg',
            ],
            [
                'category' => 'stroitelna-platforma', 'type' => 'Ножична', 'city' => 'sofia',
                'description' => 'Монтаж на вентилация в хале с височина 7 метра. Подът е бетонов и равен. Работата е вътре.',
                'in_days' => 5, 'duration' => LeadDuration::TwoThreeDays,
                'operator' => OperatorRequirement::NotRequired, 'status' => LeadStatus::Sent,
                'details' => ['height' => '7 м', 'usage' => 'vatreshno', 'surface' => 'beton'],
                'name' => 'Кирил Ставрев', 'phone' => '0888990011', 'email' => 'k.stavrev@example.bg',
            ],
        ];
    }
}
