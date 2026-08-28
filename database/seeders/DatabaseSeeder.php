<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CitySeeder::class,
            EquipmentCategorySeeder::class,
            ProviderSeeder::class,
            LeadSeeder::class,
        ]);
    }
}
