<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstUnitLevelUp>
 */
class MstUnitLevelUpFactory extends Factory
{

    protected $model = MstUnitLevelUp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'level' => 1,
            'unit_label' => 'DropR',
            'required_coin' => '1',
        ];
    }
}
