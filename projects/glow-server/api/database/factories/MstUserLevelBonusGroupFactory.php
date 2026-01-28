<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\MstUserLevelBonusGroup>
 */
class MstUserLevelBonusGroupFactory extends Factory
{

    protected $model = MstUserLevelBonusGroup::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'mst_user_level_bonus_group_id' => 'string',
            'resource_type' => 'string',
            'resource_id' => 'string',
            'resource_amount' => 'int',
        ];
    }
}
