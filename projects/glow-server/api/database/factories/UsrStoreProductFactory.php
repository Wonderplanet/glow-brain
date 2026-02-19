<?php

namespace Database\Factories;

use App\Domain\Shop\Models\UsrStoreProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Shop\Eloquent\Models\UsrStoreProduct>
 */
class UsrStoreProductFactory extends Factory
{

    protected $model = UsrStoreProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
