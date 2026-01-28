<?php

namespace Database\Factories;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstPvpRank>
 */
class MstPvpRankFactory extends Factory
{

    protected $model = MstPvpRank::class;

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
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 0,
            'required_lower_score' => 0,
            'win_add_point' => 10,
            'lose_sub_point' => 5,
            'asset_key' => 'default_pvp_rank'
        ];
    }
}
