<?php

namespace Database\Factories;

use App\Domain\Resource\Mng\Models\MngInGameNoticeI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngInGameNoticeI18n>
 */
class MngInGameNoticeI18nFactory extends Factory
{

    protected $model = MngInGameNoticeI18n::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mng_in_game_notice_id' => fake()->uuid(),
            'language' => fake()->randomElement(['ja']),
            'title' => fake()->text(),
            'description' => fake()->text(),
            'banner_url' => fake()->imageUrl(),
            'button_title' => '',
        ];
    }
}
