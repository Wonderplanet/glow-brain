<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetRelease;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<MngAssetRelease>
 */
class MngAssetReleaseFactory extends Factory
{
    protected $model = MngAssetRelease::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 111,
            'target_release_version_id' => null,
            'client_compatibility_version' => null,
            'start_at' => null,
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
