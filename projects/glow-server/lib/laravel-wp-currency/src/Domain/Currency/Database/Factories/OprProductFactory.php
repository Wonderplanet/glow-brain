<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Models\OprProduct;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\OprProduct>
 */
class OprProductFactory extends Factory
{
    protected $model = OprProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => '',
            'mst_store_product_id' => '',
            'paid_amount' => 0,
            'release_key' => 0,
            'display_priority' => 0,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ];
    }
}
