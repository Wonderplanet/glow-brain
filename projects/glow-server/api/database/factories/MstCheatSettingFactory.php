<?php

namespace Database\Factories;

use App\Domain\Cheat\Enums\CheatContentType;
use App\Domain\Cheat\Enums\CheatType;
use App\Domain\Resource\Mst\Models\MstCheatSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstConfig>
 */
class MstCheatSettingFactory extends Factory
{

    protected $model = MstCheatSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'content_type' => CheatContentType::ADVENT_BATTLE->value,
            'cheat_type' => CheatType::BATTLE_TIME->value,
            'cheat_value' => 0,
            'is_excluded_ranking' => false,
            'start_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
            'end_at' => fake()->dateTime()->format('Y-m-d H:i:s'),
        ];
    }
}
