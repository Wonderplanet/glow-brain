<?php

namespace Database\Factories;

use App\Domain\Shop\Models\UsrConditionPack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Shop\Eloquent\Models\UsrConditionPack>
 */
class UsrConditionPackFactory extends Factory
{
    protected $model = UsrConditionPack::class;

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
            'mst_pack_id' => fake()->uuid(),
            'start_date' => fake()->dateTime()->format('Y-m-d H:i:s'),
        ];
    }
}
