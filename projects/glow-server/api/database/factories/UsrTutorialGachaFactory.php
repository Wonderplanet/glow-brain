<?php

namespace Database\Factories;

use App\Domain\Tutorial\Models\UsrTutorialGacha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Tutorial\Models\UsrTutorialGacha>
 */
class UsrTutorialGachaFactory extends Factory
{
    protected $model = UsrTutorialGacha::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'gacha_result_json' => [],
            'confirmed_at' => null,
        ];
    }
}
