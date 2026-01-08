<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstShopItem;
use App\Domain\Shop\Enums\ShopItemCostType;
use App\Domain\Shop\Enums\ShopType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\MstShopItem>
 */
class MstShopItemFactory extends Factory
{

    protected $model = MstShopItem::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'shop_type' => ShopType::COIN->value,
            'cost_type' => ShopItemCostType::COIN->value,
            'cost_amount' => 1,
            'is_first_time_free' => 1,
            'tradable_count' => 1,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => 'string',
            'resource_amount' => 1,
            'start_date' => fake()->dateTime(),
            'end_date' => fake()->dateTime(),
        ];
    }
}
