<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstMissionAchievementI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstMissionAchievementI18n>
 */
class MstMissionAchievementI18nFactory extends Factory
{

    protected $model = MstMissionAchievementI18n::class;

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
            'asset_key' => 'asset_key',
        ];
    }
}
