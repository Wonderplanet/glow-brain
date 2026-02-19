<?php

namespace Database\Factories;

use App\Domain\BoxGacha\Enums\BoxGachaRewardType;
use App\Domain\Resource\Mst\Models\MstBoxGachaPrize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstBoxGachaPrize>
 */
class MstBoxGachaPrizeFactory extends Factory
{
    protected $model = MstBoxGachaPrize::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_box_gacha_group_id' => fake()->uuid(),
            'is_pickup' => false,
            'resource_type' => BoxGachaRewardType::ITEM->value,
            'resource_id' => 'item_1',
            'resource_amount' => 1,
            'stock' => 10,
        ];
    }
}
