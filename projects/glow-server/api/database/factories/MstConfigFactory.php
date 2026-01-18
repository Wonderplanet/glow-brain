<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstConfig>
 */
class MstConfigFactory extends Factory
{

    protected $model = MstConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
        ];
    }
}
