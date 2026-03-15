<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<MngMasterRelease>
 */
class MngMasterReleaseFactory extends Factory
{
    protected $model = MngMasterRelease::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'release_key' => 1,
            'enabled' => 0,
            'target_release_version_id' => null,
            'client_compatibility_version' => '0.0.0',
            'description' => null,
            'start_at' => null,
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
