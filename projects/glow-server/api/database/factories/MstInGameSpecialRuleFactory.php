<?php

namespace Database\Factories;

use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstInGameSpecialRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstInGameSpecialRule>
 */
class MstInGameSpecialRuleFactory extends Factory
{
    protected $model = MstInGameSpecialRule::class;

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
            'content_type' => InGameContentType::STAGE,
            'target_id' => fake()->uuid(),
            'rule_type' => fake()->word(),
            'rule_value' => fake()->word(),
            'start_at' => '2020-01-01 00:00:00',
            'end_at' => '2038-01-01 23:59:59',
        ];
    }
}
