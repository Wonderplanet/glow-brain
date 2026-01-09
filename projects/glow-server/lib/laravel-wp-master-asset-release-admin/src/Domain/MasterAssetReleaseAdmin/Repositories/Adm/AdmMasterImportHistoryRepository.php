<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Repositories\Adm;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistory;

class AdmMasterImportHistoryRepository
{
    /**
     * @param string $gitRevision
     * @param string $importAdmUserId
     * @param string $importSource
     * @return AdmMasterImportHistory
     */
    public function create(
        string $gitRevision,
        string $importAdmUserId,
        string $importSource,
    ): AdmMasterImportHistory {
        $admMasterImportHistory = new AdmMasterImportHistory();
        $admMasterImportHistory->git_revision = $gitRevision;
        $admMasterImportHistory->import_adm_user_id = $importAdmUserId;
        $admMasterImportHistory->import_source = $importSource;

        $admMasterImportHistory->save();

        return $admMasterImportHistory;
    }

    /**
     * @param string $id
     * @return AdmMasterImportHistory|null
     */
    public function getById(string $id): ?AdmMasterImportHistory
    {
        return AdmMasterImportHistory::query()->find($id);
    }
}
