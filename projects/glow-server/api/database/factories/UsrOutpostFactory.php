<?php

namespace Database\Factories;

use App\Domain\Outpost\Models\UsrOutpost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Outpost\Eloquent\Models\UsrOutpost>
 */
class UsrOutpostFactory extends Factory
{
    protected $model = UsrOutpost::class;

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
        ];
    }
}
