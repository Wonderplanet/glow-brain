<?php

namespace Database\Factories;

use App\Domain\Shop\Models\UsrShopItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Shop\Eloquent\Models\UsrShopItem>
 */
class UsrShopItemFactory extends Factory
{

    protected $model = UsrShopItem::class;

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
