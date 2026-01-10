<?php

namespace Database\Factories;

use App\Domain\User\Models\UsrUserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Eloquent\Models\UsrUserProfile>
 */
class UsrUserProfileFactory extends Factory
{
    protected $model = UsrUserProfile::class;

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
            'my_id' => fake()->uuid(),
            'birth_date' => '',
            'mst_unit_id' => '',
            'mst_emblem_id' => '',
        ];
    }
}
