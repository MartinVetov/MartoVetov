<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_runs_in_bulgarian(): void
    {
        $this->assertSame('bg', app()->getLocale());
    }

    public function test_validation_messages_are_in_bulgarian(): void
    {
        City::factory()->create();
        EquipmentCategory::factory()->create();

        $errors = $this->post('/zaiavka', [])->assertSessionHasErrors()->getSession()->get('errors');

        foreach (['description', 'city_id', 'contact_name'] as $field) {
            $message = $errors->first($field);

            $this->assertMatchesRegularExpression(
                '/\p{Cyrillic}/u',
                $message,
                "Съобщението за {$field} не е на български: {$message}"
            );
        }
    }

    public function test_login_errors_are_in_bulgarian(): void
    {
        $user = User::factory()->create(['password' => 'parola1234']);

        $errors = $this->post('/vhod', ['email' => $user->email, 'password' => 'greshka'])
            ->getSession()->get('errors');

        $this->assertMatchesRegularExpression('/\p{Cyrillic}/u', $errors->first('email'));
    }

    public function test_pagination_labels_are_translated(): void
    {
        $this->assertSame('&laquo; Назад', trans('pagination.previous'));
        $this->assertSame('Напред &raquo;', trans('pagination.next'));
    }
}
