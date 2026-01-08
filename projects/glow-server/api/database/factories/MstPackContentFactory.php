<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstPackContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstPackContent>
 */
class MstPackContentFactory extends Factory
{

    protected $model = MstPackContent::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'mst_pack_id' => fake()->uuid(),
            'resource_type' => fake()->randomElement([RewardType::FREE_DIAMOND->value, RewardType::COIN->value, RewardType::ITEM->value]),
            'resource_id' => fake()->uuid(),
            'resource_amount' => fake()->numberBetween(1, 100),
            'is_bonus' => fake()->numberBetween(0, 1),
            'display_order' => fake()->numberBetween(1, 10),
        ];
    }
}
