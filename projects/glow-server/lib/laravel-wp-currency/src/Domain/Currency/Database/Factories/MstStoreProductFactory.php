<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\Currency\Models\MstStoreProduct;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Domain\Currency\Models\MstStoreProduct>
 */
class MstStoreProductFactory extends Factory
{
    protected $model = MstStoreProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => '',
            'release_key' => 0,
            'product_id_ios' => '',
            'product_id_android' => '',
        ];
    }
}
