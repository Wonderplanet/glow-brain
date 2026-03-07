<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Database\Factories\Adm;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterReleaseVersionStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<AdmMasterReleaseVersionStatus>
 */
class AdmMasterReleaseVersionStatusFactory extends Factory
{
    protected $model = AdmMasterReleaseVersionStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'mng_master_release_version_id' => fake()->uuid(),
            'ocarina_validated_status' => fake()->uuid(),
            'ocarina_validation_version' => null,
            'client_file_deleted_at' => null,
            'server_db_deleted_at' => null,
        ];
    }
}
