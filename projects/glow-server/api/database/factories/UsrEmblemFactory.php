<?php

namespace Database\Factories;

use App\Domain\Emblem\Models\UsrEmblem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Emblem\Eloquent\Models\UsrEmblem>
 */
class UsrEmblemFactory extends Factory
{
    protected $model = UsrEmblem::class;

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
            'mst_emblem_id' => fake()->uuid(),
        ];
    }
}
