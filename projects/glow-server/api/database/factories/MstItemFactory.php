<?php

namespace Database\Factories;

use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Models\MstItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstItem>
 */
class MstItemFactory extends Factory
{

    protected $model = MstItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'type' => ItemType::ETC->value,
            'group_type' => 'string',
            'rarity' => 'N',
            'asset_key' => 'string',
            'effect_value' => null,
            'mst_series_id' => 'string',
            'sort_order' => 1,
            // 現在日時で有効な状態をデフォルトとする
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2038-01-01 00:00:00',
            'destination_opr_product_id' => 'string',
        ];
    }
}
