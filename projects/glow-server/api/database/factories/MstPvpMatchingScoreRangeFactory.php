<?php

namespace Database\Factories;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Resource\Mst\Models\MstPvpMatchingScoreRange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstPvpMatchingScoreRangey>
 */
class MstPvpMatchingScoreRangeFactory extends Factory
{
    protected $model = MstPvpMatchingScoreRange::class;

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
            'rank_class_type' => PvpRankClassType::BRONZE,
            'rank_class_level' => 0,
            'upper_rank_max_score' => 0,
            'upper_rank_min_score' => 0,
            'same_rank_max_score' => 0,
            'same_rank_min_score' => 0,
            'lower_rank_max_score' => 0,
            'lower_rank_min_score' => 0,
        ];
    }
}
