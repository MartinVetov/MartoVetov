<?php

namespace Tests\Unit;

use App\Enums\LeadDuration;
use App\Enums\LeadStatus;
use App\Enums\OperatorRequirement;
use App\Rules\BulgarianPhone;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MatchingConfigTest extends TestCase
{
    public function test_the_scoring_weights_are_configurable(): void
    {
        $weights = config('matching.weights');

        foreach (['category', 'same_city', 'within_radius', 'operator_match', 'verified'] as $key) {
            $this->assertArrayHasKey($key, $weights);
            $this->assertIsInt($weights[$key]);
        }

        $this->assertIsInt(config('matching.minimum_score'));
        $this->assertIsInt(config('matching.suggested_providers'));
    }

    public function test_all_statuses_have_bulgarian_labels(): void
    {
        foreach (LeadStatus::cases() as $status) {
            $this->assertMatchesRegularExpression('/\p{Cyrillic}/u', $status->label());
        }

        foreach (OperatorRequirement::cases() as $requirement) {
            $this->assertMatchesRegularExpression('/\p{Cyrillic}/u', $requirement->label());
        }

        foreach (LeadDuration::cases() as $duration) {
            $this->assertMatchesRegularExpression('/\p{Cyrillic}|\d/u', $duration->label());
        }
    }

    #[DataProvider('phoneNumbers')]
    public function test_bulgarian_phone_validation(string $phone, bool $expected): void
    {
        $validator = Validator::make(['phone' => $phone], ['phone' => [new BulgarianPhone]]);

        $this->assertSame($expected, $validator->passes(), "Номерът {$phone} беше преценен грешно.");
    }

    public static function phoneNumbers(): array
    {
        return [
            ['0888123456', true],
            ['+359888123456', true],
            ['00359888123456', true],
            ['0888 123 456', true],
            ['02/9876543', true],
            ['123', false],
            ['0088812345', false],
            ['не е телефон', false],
        ];
    }
}
