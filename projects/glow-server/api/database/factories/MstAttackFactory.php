<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstAttack;
use App\Domain\Unit\Enums\AttackKind;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstAttack>
 */
class MstAttackFactory extends Factory
{

    protected $model = MstAttack::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mst_unit_id' => fake()->uuid(),
            'unit_grade' => 1,
            'attack_kind' => AttackKind::NORMAL->value,
            'killer_colors' => 'Red',
            'killer_percentage' => 1,
            'action_frames' => 1,
            'attack_delay' => 1,
            'next_attack_interval' => 1,
            'release_key' => 1,
        ];
    }
}
