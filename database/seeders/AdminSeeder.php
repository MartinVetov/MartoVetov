<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => config('nt.admin_email')],
            [
                'name' => 'Администратор',
                'phone' => '0888000000',
                'password' => env('NT_ADMIN_PASSWORD', 'parola123'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );
    }
}
