<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate>
 */
class AdmForeignCurrencyRateFactory extends Factory
{
    protected $model = AdmForeignCurrencyRate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->uuid,
            'year' => 0,
            'month' => 0,
            'currency_code' => '',
            'currency' => '',
            'currency_name' => '',
            'tts' => '0.00',
            'ttb' => '0.00',
            'ttm' => '0.00',
        ];
    }
}
