<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\MstUserLevelBonus>
 */
class MstUserLevelBonusFactory extends Factory
{

    protected $model = MstUserLevelBonus::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'level' => 'int',
            'mst_user_level_bonus_group_id' => 'string',
        ];
    }
}
