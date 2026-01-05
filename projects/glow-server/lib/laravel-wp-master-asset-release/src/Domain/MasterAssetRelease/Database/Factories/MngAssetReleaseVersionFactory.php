<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<MngAssetReleaseVersion>
 */
class MngAssetReleaseVersionFactory extends Factory
{
    protected $model = MngAssetReleaseVersion::class;

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
            'git_revision' => 'revision',
            'git_branch' => 'testBranch',
            'catalog_hash' => 'test_hash',
            'platform' => '1',
            'build_client_version' => '1.0',
            'asset_total_byte_size' => 1000,
            'catalog_byte_size' => 1000,
            'catalog_file_name' => 'filaName',
            'catalog_hash_file_name' => 'test',
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
