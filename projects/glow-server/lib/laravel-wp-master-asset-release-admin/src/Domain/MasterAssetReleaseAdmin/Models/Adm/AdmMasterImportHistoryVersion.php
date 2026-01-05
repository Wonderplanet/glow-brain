<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm;

use WonderPlanet\Domain\Admin\Models\BaseAdmModel;

/**
 * adm_master_import_historiesとmng_master_release_versionsを1対多で紐づける中間テーブル
 *
 * @property string $id
 * @property string $adm_master_import_history_id
 * @property string $mng_master_release_version_id
 */
class AdmMasterImportHistoryVersion extends BaseAdmModel
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'adm_master_import_history_id' => 'string',
        'mng_master_release_version_id' => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'adm_master_import_history_id',
        'mng_master_release_version_id',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function getAdmMasterImportHistoryId(): string
    {
        return $this->adm_master_import_history_id;
    }

    public function getMngMasterReleaseVersionId(): string
    {
        return $this->mng_master_release_version_id;
    }
}
