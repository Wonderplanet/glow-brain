<?php

namespace Database\Factories;

use App\Domain\User\Models\UsrOsPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Models\UsrOsPlatform>
 */
class UsrOsPlatformFactory extends Factory
{
    protected $model = UsrOsPlatform::class;

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
            'os_platform' => 'ios',
        ];
    }
}
