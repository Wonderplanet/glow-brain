<?php

namespace Database\Factories;

use App\Domain\Resource\Mng\Models\MngMessageI18n;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngMessageI18N>
 */
class MngMessageI18nFactory extends Factory
{

    protected $model = MngMessageI18n::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'mng_message_id' => fake()->uuid(),
            'language' => 'ja',
            'title' => '',
            'body' => '',
        ];
    }
}
