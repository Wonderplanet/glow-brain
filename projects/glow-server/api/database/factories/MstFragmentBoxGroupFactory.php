<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstFragmentBoxGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstItem>
 */
class MstFragmentBoxGroupFactory extends Factory
{

    protected $model = MstFragmentBoxGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_fragment_box_group_id' => 'string',
            'mst_item_id' => fake()->uuid(),
            // 現在日時で有効な状態をデフォルトとする
            'start_at' => '2000-01-01 00:00:00',
            'end_at' => '2038-01-01 00:00:00',
            'release_key' => '1',
        ];
    }
}
