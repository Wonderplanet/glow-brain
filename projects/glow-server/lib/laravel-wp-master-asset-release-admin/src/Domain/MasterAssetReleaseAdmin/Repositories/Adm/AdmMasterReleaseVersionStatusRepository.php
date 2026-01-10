<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Repositories\Adm;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterReleaseVersionStatus;

class AdmMasterReleaseVersionStatusRepository
{
    /**
     * @param string $mngMasterReleaseVersionId
     * @param string $ocarinaValidatedStatus
     * @param string|null $ocarinaValidationVersion
     * @param CarbonImmutable|null $clientFileDeletedAt
     * @param CarbonImmutable|null $serverDbDeletedAt
     * @return AdmMasterReleaseVersionStatus
     */
    public function create(
        string $mngMasterReleaseVersionId,
        string $ocarinaValidatedStatus,
        string|null $ocarinaValidationVersion = null,
        CarbonImmutable|null $clientFileDeletedAt = null,
        CarbonImmutable|null $serverDbDeletedAt = null,
    ): AdmMasterReleaseVersionStatus {
        $admMasterReleaseVersionStatus = new AdmMasterReleaseVersionStatus();
        $admMasterReleaseVersionStatus->mng_master_release_version_id = $mngMasterReleaseVersionId;
        $admMasterReleaseVersionStatus->ocarina_validated_status = $ocarinaValidatedStatus;
        $admMasterReleaseVersionStatus->ocarina_validation_version = $ocarinaValidationVersion;
        $admMasterReleaseVersionStatus->client_file_deleted_at = $clientFileDeletedAt;
        $admMasterReleaseVersionStatus->server_db_deleted_at = $serverDbDeletedAt;

        $admMasterReleaseVersionStatus->save();

        return $admMasterReleaseVersionStatus;
    }

    /**
     * @param string $id
     * @return AdmMasterReleaseVersionStatus|null
     */
    public function getById(string $id): ?AdmMasterReleaseVersionStatus
    {
        return AdmMasterReleaseVersionStatus::query()->find($id);
    }
}
