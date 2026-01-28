<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Database\Factories\Adm;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistoryVersion;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<AdmMasterImportHistoryVersion>
 */
class AdmMasterImportHistoryVersionFactory extends Factory
{
    protected $model = AdmMasterImportHistoryVersion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'adm_master_import_history_id' => fake()->uuid(),
            'mng_master_release_version_id' => fake()->uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
