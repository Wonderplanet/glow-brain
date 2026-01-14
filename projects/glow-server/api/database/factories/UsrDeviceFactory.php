<?php

namespace Database\Factories;

use App\Domain\Auth\Models\UsrDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Auth\Eloquent\Models\UsrDevice>
 */
class UsrDeviceFactory extends Factory
{
    protected $model = UsrDevice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->randomNumber(),
            'uuid' => fake()->uuid(),
            'bnid_linked_at' => null,
            'os_platform' => '',
        ];
    }
}
