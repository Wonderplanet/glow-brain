<?php

namespace Database\Factories;

use App\Domain\Resource\Eloquent\Models\OprTosVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Eloquent\Models\OprTosVersion>
 */
class OprTosVersionFactory extends Factory
{
    protected $model = OprTosVersion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'version' => fake()->randomDigit(),
            'url' => fake()->url(),
        ];
    }
}
