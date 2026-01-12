<?php

namespace Database\Factories;

use App\Domain\Shop\Models\UsrStoreProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Shop\Eloquent\Models\UsrStoreProduct>
 */
class UsrSoreProductFactory extends Factory
{
    protected $model = UsrStoreProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $currentDateString = now()->format('Y-m-d H:i:s');
        return [
            'id' => fake()->uuid(),
            'product_sub_id' => fake()->uuid(),
            'purchase_count' => fake()->numberBetween(1, 10),
            'purchase_total_count' => fake()->numberBetween(1, 10),
            'last_reset_at' => $currentDateString,
            'created_at' => $currentDateString,
            'updated_at' => $currentDateString
        ];
    }
}
