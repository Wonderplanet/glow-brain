<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstPvpRewardGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstPvpRewardGroup>
 */
class MstPvpRewardGroupFactory extends Factory
{

    protected $model = MstPvpRewardGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'release_key' => 1,
            'reward_category' => 'RANK_CLASS',
            'condition_value' => '1',
            'mst_pvp_id' => 'default_pvp', // ここは適切なPVP IDに置き換える必要があります
        ];
    }
}
