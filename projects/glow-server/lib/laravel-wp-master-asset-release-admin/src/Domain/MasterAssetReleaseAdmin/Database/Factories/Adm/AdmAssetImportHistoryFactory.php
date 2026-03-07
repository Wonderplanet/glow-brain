<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Database\Factories\Adm;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmAssetImportHistory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<AdmAssetImportHistory>
 */
class AdmAssetImportHistoryFactory extends Factory
{
    protected $model = AdmAssetImportHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'mng_asset_release_version_id' => fake()->uuid(),
            'import_adm_user_id' => 'adm_user_test',
            'import_source' => 'import_source_test',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
