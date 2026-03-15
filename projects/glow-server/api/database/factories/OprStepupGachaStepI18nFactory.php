<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprStepupGachaStepI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprStepupGachaStepI18n>
 */
class OprStepupGachaStepI18nFactory extends Factory
{
    protected $model = OprStepupGachaStepI18n::class;

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
            'opr_stepup_gacha_step_id' => fake()->uuid(),
            'language' => 'ja',
            'fixed_prize_description' => fake()->sentence(),
        ];
    }
}
