<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstIdleIncentiveItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstIdleIncentiveItem>
 */
class MstIdleIncentiveItemFactory extends Factory
{

    protected $model = MstIdleIncentiveItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'mst_idle_incentive_item_group_id' => fake()->uuid(),
            'mst_item_id' => fake()->uuid(),
            'base_amount' => '1.23',
        ];
    }
}
