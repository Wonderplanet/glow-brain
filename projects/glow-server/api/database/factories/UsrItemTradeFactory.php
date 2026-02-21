<?php

namespace Database\Factories;

use App\Domain\Item\Models\UsrItemTrade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Item\Eloquent\Models\UsrItemTrade>
 */
class UsrItemTradeFactory extends Factory
{
    protected $model = UsrItemTrade::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
        ];
    }
}
