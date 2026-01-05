<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprGachaI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprGachaI18n>
 */
class OprGachaI18nFactory extends Factory
{

    protected $model = OprGachaI18n::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'opr_gacha_id' => 1,
            'language' => 'ja',
            'name' => fake()->uuid(),
            'description' => fake()->uuid(),
            'max_rarity_upper_description' => fake()->uuid(),
            'pickup_upper_description' => fake()->uuid(),
            'banner_url' => fake()->uuid(),
            'logo_banner_url' => fake()->uuid(),
            'gacha_background_color' => fake()->uuid(),
        ];
    }
}
