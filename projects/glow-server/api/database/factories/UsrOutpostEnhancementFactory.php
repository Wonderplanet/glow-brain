<?php

namespace Database\Factories;

use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Outpost\Eloquent\Models\UsrOutpostEnhancement>
 */
class UsrOutpostEnhancementFactory extends Factory
{
    protected $model = UsrOutpostEnhancement::class;

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
            'mst_outpost_id' => fake()->uuid(),
            'mst_outpost_enhancement_id' => fake()->uuid(),
            'level' => 1,
        ];
    }
}
