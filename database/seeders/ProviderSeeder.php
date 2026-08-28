<?php

namespace Database\Seeders;

use App\Enums\PriceUnit;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\City;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\ProviderProfile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $cities = City::all()->keyBy('slug');
        $categories = EquipmentCategory::whereNull('parent_id')->get()->keyBy('slug');

        foreach ($this->providers() as $index => $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['contact_name'],
                    'phone' => $data['phone'],
                    'password' => 'parola123',
                    'role' => UserRole::Provider,
                    'email_verified_at' => now(),
                ]
            );

            $city = $cities[$data['city']];

            $profile = ProviderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $data['company'],
                    'slug' => Str::slug($data['company']),
                    'eik' => $data['eik'] ?? null,
                    'contact_name' => $data['contact_name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'website' => $data['website'] ?? null,
                    'description' => $data['description'],
                    'city_id' => $city->id,
                    'service_radius' => $data['radius'],
                    'working_hours' => 'Пон – Съб, 07:00 – 19:00',
                    'email_verified' => true,
                    'phone_verified' => $data['verified'],
                    'company_verified' => $data['verified'],
                    'verified' => $data['verified'],
                    'verified_at' => $data['verified'] ? now()->subDays(30) : null,
                    'active' => true,
                    'submitted_at' => now()->subDays(45 - $index),
                    'approved_at' => now()->subDays(40 - $index),
                    'profile_views' => random_int(15, 480),
                ]
            );

            // Обслужвани градове — базовият град плюс съседните.
            $areaIds = collect($data['areas'])
                ->map(fn (string $slug) => $cities[$slug]->id ?? null)
                ->filter()
                ->push($city->id)
                ->unique();

            $profile->serviceAreas()->whereNotIn('city_id', $areaIds)->delete();

            foreach ($areaIds as $cityId) {
                $profile->serviceAreas()->updateOrCreate(
                    ['city_id' => $cityId],
                    ['radius' => $data['radius']]
                );
            }

            Subscription::updateOrCreate(
                ['provider_profile_id' => $profile->id, 'status' => SubscriptionStatus::Active],
                [
                    'plan' => $data['plan'],
                    'starts_at' => now()->subMonths(2),
                    'ends_at' => now()->addMonths(10),
                    'lead_quota' => $data['plan']->monthlyLeadQuota(),
                    'leads_used' => 0,
                ]
            );

            $profile->equipment()->delete();

            foreach ($data['equipment'] as $item) {
                $category = $categories[$item['category']];

                Equipment::create([
                    'provider_profile_id' => $profile->id,
                    'equipment_category_id' => $category->id,
                    'type' => $item['type'] ?? null,
                    'brand' => $item['brand'] ?? null,
                    'model' => $item['model'] ?? null,
                    'year' => $item['year'] ?? null,
                    'weight' => $item['weight'] ?? null,
                    'description' => $item['description'] ?? null,
                    'operator_available' => $item['operator'] ?? true,
                    'operator_only' => $item['operator_only'] ?? false,
                    'price_from' => $item['price'] ?? null,
                    'price_unit' => $item['unit'] ?? PriceUnit::Hour,
                    'min_duration' => $item['min'] ?? '4 часа',
                    'active' => true,
                ]);
            }
        }
    }

    protected function providers(): array
    {
        return [
            [
                'company' => 'Строй Техник ЕООД', 'contact_name' => 'Иван Петров',
                'email' => 'ivan@stroytehnik.bg', 'phone' => '0888123456', 'eik' => '831234567',
                'city' => 'sofia', 'areas' => ['pernik', 'sofia'], 'radius' => 80,
                'verified' => true, 'plan' => SubscriptionPlan::Business,
                'website' => 'https://stroytehnik.bg',
                'description' => 'Работим в София и областта от 2009 г. Разполагаме с мини багери от 1,8 до 8 тона, самосвали и опитни оператори. Поемаме както еднодневни изкопи в двор, така и по-големи обекти.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '2 – 5 тона', 'brand' => 'Caterpillar', 'model' => '303.5', 'year' => 2019, 'weight' => 3.5, 'price' => 70, 'description' => 'Верижен мини багер с обръщаща се кофа и хидравличен чук.'],
                    ['category' => 'mini-bager', 'type' => 'До 2 тона', 'brand' => 'Kubota', 'model' => 'KX016-4', 'year' => 2021, 'weight' => 1.8, 'price' => 55, 'description' => 'Минава през вход от 1 метър — за работа в готови дворове.'],
                    ['category' => 'samosval', 'type' => 'Среден (до 12 т)', 'brand' => 'MAN', 'model' => 'TGM', 'year' => 2017, 'weight' => 12, 'price' => 180, 'unit' => PriceUnit::Shift],
                ],
            ],
            [
                'company' => 'Висота Инженеринг ООД', 'contact_name' => 'Мария Georgieva',
                'email' => 'office@visota-eng.bg', 'phone' => '0889223344', 'eik' => '175223344',
                'city' => 'sofia', 'areas' => ['pernik', 'blagoevgrad'], 'radius' => 120,
                'verified' => true, 'plan' => SubscriptionPlan::Pro,
                'description' => 'Специализирани сме в работа на височина. Автовишки от 16 до 32 метра, ножични и колянови платформи за вътрешни помещения.',
                'equipment' => [
                    ['category' => 'avtovishka', 'type' => '15 – 25 м', 'brand' => 'Iveco', 'model' => 'Daily 22m', 'year' => 2018, 'price' => 90, 'min' => '2 часа', 'operator_only' => true],
                    ['category' => 'avtovishka', 'type' => 'Над 25 м', 'brand' => 'Mercedes', 'model' => 'Atego 32m', 'year' => 2020, 'price' => 140, 'min' => '2 часа', 'operator_only' => true],
                    ['category' => 'stroitelna-platforma', 'type' => 'Ножична', 'brand' => 'Genie', 'model' => 'GS-2632', 'year' => 2019, 'price' => 120, 'unit' => PriceUnit::Day, 'operator' => false],
                ],
            ],
            [
                'company' => 'Багер Сервиз Пловдив', 'contact_name' => 'Георги Динев',
                'email' => 'g.dinev@bagerservice.bg', 'phone' => '0878445566',
                'city' => 'plovdiv', 'areas' => ['pazardzhik', 'haskovo', 'perushtitsa'], 'radius' => 70,
                'verified' => true, 'plan' => SubscriptionPlan::Pro,
                'description' => 'Изкопни работи в Пловдив и Пловдивска област. Три мини багера и челен товарач. Излизаме и в почивни дни.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '2 – 5 тона', 'brand' => 'JCB', 'model' => '8035 ZTS', 'year' => 2020, 'weight' => 3.8, 'price' => 65],
                    ['category' => 'mini-bager', 'type' => '5 – 10 тона', 'brand' => 'Volvo', 'model' => 'EC60E', 'year' => 2018, 'weight' => 6.0, 'price' => 85],
                    ['category' => 'tovarach', 'type' => 'Челен товарач', 'brand' => 'JCB', 'model' => '409', 'year' => 2016, 'price' => 75],
                ],
            ],
            [
                'company' => 'Морска Техника Варна', 'contact_name' => 'Николай Стоянов',
                'email' => 'nikolay@morskatehnika.bg', 'phone' => '0877556677',
                'city' => 'varna', 'areas' => ['dobrich', 'shumen'], 'radius' => 90,
                'verified' => true, 'plan' => SubscriptionPlan::Business,
                'description' => 'Техника под наем за Варна, Добрич и Черноморието. Багери, товарачи и самосвали с оператори с дългогодишен опит.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '2 – 5 тона', 'brand' => 'Takeuchi', 'model' => 'TB240', 'year' => 2021, 'weight' => 4.0, 'price' => 72],
                    ['category' => 'tovarach', 'type' => 'Мини товарач', 'brand' => 'Bobcat', 'model' => 'S570', 'year' => 2019, 'price' => 68],
                    ['category' => 'samosval', 'type' => 'Голям (над 12 т)', 'brand' => 'Scania', 'model' => 'P410 8x4', 'year' => 2018, 'price' => 260, 'unit' => PriceUnit::Shift],
                ],
            ],
            [
                'company' => 'Бургас Билд Машини', 'contact_name' => 'Петър Ангелов',
                'email' => 'p.angelov@burgasbuild.bg', 'phone' => '0899334455',
                'city' => 'burgas', 'areas' => ['yambol', 'sliven'], 'radius' => 100,
                'verified' => false, 'plan' => SubscriptionPlan::Free,
                'description' => 'Малка фирма с две машини. Работим предимно по крайбрежието — изкопи за басейни, комуникации и разчистване на парцели.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '2 – 5 тона', 'brand' => 'Hyundai', 'model' => 'R35Z-9', 'year' => 2017, 'weight' => 3.5, 'price' => 60],
                    ['category' => 'samosval', 'type' => 'Малък (до 3,5 т)', 'brand' => 'Iveco', 'model' => 'Daily 35C', 'year' => 2015, 'price' => 90, 'unit' => PriceUnit::Shift],
                ],
            ],
            [
                'company' => 'Дунав Техник Русе', 'contact_name' => 'Стефан Илиев',
                'email' => 'stefan@dunavtehnik.bg', 'phone' => '0886778899',
                'city' => 'ruse', 'areas' => ['razgrad', 'silistra', 'targovishte'], 'radius' => 85,
                'verified' => true, 'plan' => SubscriptionPlan::Pro,
                'description' => 'Обслужваме Русе и Северна България. Багери, автовишка и генератори за строителни обекти.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '5 – 10 тона', 'brand' => 'Komatsu', 'model' => 'PC55MR', 'year' => 2019, 'weight' => 5.5, 'price' => 80],
                    ['category' => 'avtovishka', 'type' => 'До 15 м', 'brand' => 'Ford', 'model' => 'Transit 14m', 'year' => 2016, 'price' => 70, 'min' => '2 часа', 'operator_only' => true],
                    ['category' => 'generator', 'type' => '10 – 60 kVA', 'brand' => 'SDMO', 'model' => 'J44K', 'year' => 2018, 'price' => 150, 'unit' => PriceUnit::Day, 'operator' => false],
                ],
            ],
            [
                'company' => 'Загора Строй ЕООД', 'contact_name' => 'Димитър Колев',
                'email' => 'd.kolev@zagorastroy.bg', 'phone' => '0885112233',
                'city' => 'stara-zagora', 'areas' => ['sliven', 'haskovo', 'yambol'], 'radius' => 95,
                'verified' => true, 'plan' => SubscriptionPlan::Pro,
                'description' => 'Земни работи и транспорт в Стара Загора и региона. Разполагаме и с телескопичен товарач за монтаж на покривни конструкции.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '2 – 5 тона', 'brand' => 'Yanmar', 'model' => 'ViO38', 'year' => 2020, 'weight' => 3.8, 'price' => 68],
                    ['category' => 'teleskopichen-tovarach', 'type' => '10 – 14 м', 'brand' => 'Manitou', 'model' => 'MT 1135', 'year' => 2018, 'price' => 110],
                    ['category' => 'samosval', 'type' => 'Среден (до 12 т)', 'brand' => 'DAF', 'model' => 'LF', 'year' => 2016, 'price' => 170, 'unit' => PriceUnit::Shift],
                ],
            ],
            [
                'company' => 'Плевен Земни Работи', 'contact_name' => 'Красимир Тодоров',
                'email' => 'k.todorov@plevenzemni.bg', 'phone' => '0894556677',
                'city' => 'pleven', 'areas' => ['lovech', 'vratsa'], 'radius' => 75,
                'verified' => false, 'plan' => SubscriptionPlan::Free,
                'description' => 'Изкопи, обратни насипи и подравняване на терени в Плевенска област.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '2 – 5 тона', 'brand' => 'Bobcat', 'model' => 'E35', 'year' => 2018, 'weight' => 3.5, 'price' => 62],
                    ['category' => 'tovarach', 'type' => 'Мини товарач', 'brand' => 'Case', 'model' => 'SR175', 'year' => 2017, 'price' => 60],
                ],
            ],
            [
                'company' => 'Търново Лифт', 'contact_name' => 'Валентин Маринов',
                'email' => 'v.marinov@tarnovolift.bg', 'phone' => '0876889900',
                'city' => 'veliko-tarnovo', 'areas' => ['gabrovo', 'ruse'], 'radius' => 80,
                'verified' => true, 'plan' => SubscriptionPlan::Pro,
                'description' => 'Автовишки и платформи за Велико Търново и Габрово. Работа по фасади, покриви и градско осветление.',
                'equipment' => [
                    ['category' => 'avtovishka', 'type' => '15 – 25 м', 'brand' => 'Renault', 'model' => 'Master 20m', 'year' => 2019, 'price' => 85, 'min' => '2 часа', 'operator_only' => true],
                    ['category' => 'stroitelna-platforma', 'type' => 'Колянова', 'brand' => 'Haulotte', 'model' => 'HA16', 'year' => 2017, 'price' => 160, 'unit' => PriceUnit::Day, 'operator' => false],
                ],
            ],
            [
                'company' => 'Родопи Техника', 'contact_name' => 'Асен Караджов',
                'email' => 'asen@rodopitehnika.bg', 'phone' => '0893667788',
                'city' => 'smolyan', 'areas' => ['kardzhali', 'plovdiv'], 'radius' => 110,
                'verified' => false, 'plan' => SubscriptionPlan::Free,
                'description' => 'Работим в планински условия — стръмни терени, тесни улици и обекти с труден достъп.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => 'До 2 тона', 'brand' => 'Kubota', 'model' => 'U17-3', 'year' => 2020, 'weight' => 1.7, 'price' => 58],
                    ['category' => 'samosval', 'type' => 'Малък (до 3,5 т)', 'brand' => 'Mitsubishi', 'model' => 'Canter', 'year' => 2014, 'price' => 85, 'unit' => PriceUnit::Shift],
                ],
            ],
            [
                'company' => 'Пирин Строй Машини', 'contact_name' => 'Емил Костов',
                'email' => 'emil@pirinmash.bg', 'phone' => '0897223311',
                'city' => 'blagoevgrad', 'areas' => ['kyustendil', 'sofia'], 'radius' => 100,
                'verified' => true, 'plan' => SubscriptionPlan::Pro,
                'description' => 'Строителна техника под наем за Югозападна България. Багери, товарачи и генератори.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '5 – 10 тона', 'brand' => 'Doosan', 'model' => 'DX62R', 'year' => 2019, 'weight' => 6.2, 'price' => 82],
                    ['category' => 'generator', 'type' => 'До 10 kVA', 'brand' => 'Honda', 'model' => 'EU70is', 'year' => 2021, 'price' => 60, 'unit' => PriceUnit::Day, 'operator' => false],
                ],
            ],
            [
                'company' => 'Шумен Билд ООД', 'contact_name' => 'Тодор Василев',
                'email' => 't.vasilev@shumenbuild.bg', 'phone' => '0882334455',
                'city' => 'shumen', 'areas' => ['varna', 'targovishte', 'razgrad'], 'radius' => 85,
                'verified' => false, 'plan' => SubscriptionPlan::Free,
                'description' => 'Земни работи и транспорт на инертни материали в Шуменска област.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '2 – 5 тона', 'brand' => 'JCB', 'model' => '48Z-1', 'year' => 2021, 'weight' => 4.8, 'price' => 70],
                    ['category' => 'samosval', 'type' => 'Среден (до 12 т)', 'brand' => 'Volvo', 'model' => 'FL', 'year' => 2015, 'price' => 165, 'unit' => PriceUnit::Shift],
                ],
            ],
            [
                'company' => 'Хасково Техно Рент', 'contact_name' => 'Румен Ганев',
                'email' => 'rumen@haskovorent.bg', 'phone' => '0891445566',
                'city' => 'haskovo', 'areas' => ['kardzhali', 'stara-zagora'], 'radius' => 90,
                'verified' => false, 'plan' => SubscriptionPlan::Free,
                'description' => 'Наем на строителна техника със и без оператор. Гъвкави условия за дългосрочен наем.',
                'equipment' => [
                    ['category' => 'tovarach', 'type' => 'Мини товарач', 'brand' => 'Bobcat', 'model' => 'S450', 'year' => 2018, 'price' => 62],
                    ['category' => 'stroitelna-platforma', 'type' => 'Ножична', 'brand' => 'JLG', 'model' => '2646ES', 'year' => 2019, 'price' => 110, 'unit' => PriceUnit::Day, 'operator' => false],
                ],
            ],
            [
                'company' => 'Перник Изкопи', 'contact_name' => 'Здравко Митов',
                'email' => 'zdravko@pernikizkopi.bg', 'phone' => '0884778899',
                'city' => 'pernik', 'areas' => ['sofia', 'kyustendil'], 'radius' => 60,
                'verified' => false, 'plan' => SubscriptionPlan::Free,
                'description' => 'Изкопни работи в Перник, Радомир и София. Бърза реакция при аварии по водопровод.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '2 – 5 тона', 'brand' => 'Case', 'model' => 'CX37C', 'year' => 2020, 'weight' => 3.7, 'price' => 64],
                ],
            ],
            [
                'company' => 'Габрово Механизация', 'contact_name' => 'Пламен Йорданов',
                'email' => 'plamen@gabrovomeh.bg', 'phone' => '0879556644',
                'city' => 'gabrovo', 'areas' => ['veliko-tarnovo', 'lovech'], 'radius' => 70,
                'verified' => true, 'plan' => SubscriptionPlan::Pro,
                'description' => 'Механизация под наем за Габрово и Централна Стара планина. Работим целогодишно.',
                'equipment' => [
                    ['category' => 'mini-bager', 'type' => '2 – 5 тона', 'brand' => 'Kubota', 'model' => 'KX060-5', 'year' => 2022, 'weight' => 5.0, 'price' => 78],
                    ['category' => 'teleskopichen-tovarach', 'type' => 'До 10 м', 'brand' => 'JCB', 'model' => '525-60', 'year' => 2017, 'price' => 95],
                ],
            ],
            [
                'company' => 'Добруджа Агро Техника', 'contact_name' => 'Христо Панайотов',
                'email' => 'hristo@dobrudzhaagro.bg', 'phone' => '0898112244',
                'city' => 'dobrich', 'areas' => ['varna', 'silistra'], 'radius' => 100,
                'verified' => false, 'plan' => SubscriptionPlan::Free,
                'description' => 'Телескопични товарачи и генератори за селскостопански и строителни обекти в Добруджа.',
                'equipment' => [
                    ['category' => 'teleskopichen-tovarach', 'type' => '10 – 14 м', 'brand' => 'Merlo', 'model' => 'TF 38.10', 'year' => 2019, 'price' => 105],
                    ['category' => 'generator', 'type' => '10 – 60 kVA', 'brand' => 'Caterpillar', 'model' => 'DE33', 'year' => 2016, 'price' => 140, 'unit' => PriceUnit::Day, 'operator' => false],
                ],
            ],
        ];
    }
}
