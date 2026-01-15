<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\OprAssetReleaseVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\OprAssetReleaseVersion>
 */
class OprAssetReleaseVersionFactory extends Factory
{
    protected $model = OprAssetReleaseVersion::class;

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
            'git_branch' => 'testBranch',
            'git_revision' => 'revision',
            'catalog_file_name' => 'filaName',
            'asset_total_byte_size' => 1000,
            'catalog_hash' => 'test_hash',
            'catalog_byte_size' => 1000,
            'catalog_hash_file_name' => 'test',
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
