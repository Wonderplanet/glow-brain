<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Repositories\Adm;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistoryVersion;

class AdmMasterImportHistoryVersionRepository
{
    /**
     * @param string $admMasterImportHistoryId
     * @param string $mngMasterReleaseVersionId
     * @return AdmMasterImportHistoryVersion
     */
    public function create(
        string $admMasterImportHistoryId,
        string $mngMasterReleaseVersionId,
    ): AdmMasterImportHistoryVersion {
        $admMasterImportHistoryVersion = new AdmMasterImportHistoryVersion();
        $admMasterImportHistoryVersion->adm_master_import_history_id = $admMasterImportHistoryId;
        $admMasterImportHistoryVersion->mng_master_release_version_id = $mngMasterReleaseVersionId;

        $admMasterImportHistoryVersion->save();

        return $admMasterImportHistoryVersion;
    }

    /**
     * @param string $id
     * @return AdmMasterImportHistoryVersion|null
     */
    public function getById(string $id): ?AdmMasterImportHistoryVersion
    {
        return AdmMasterImportHistoryVersion::query()->find($id);
    }
}
