<?php

namespace Database\Factories;

use App\Domain\Resource\Mng\Models\MngClientVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mng\Models\MngClientVersion>
 */
class MngClientVersionFactory extends Factory
{
    protected $model = MngClientVersion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'client_version' => '1.0.0',
            'platform' => 1,
            'is_force_update' => false,
        ];
    }
}
