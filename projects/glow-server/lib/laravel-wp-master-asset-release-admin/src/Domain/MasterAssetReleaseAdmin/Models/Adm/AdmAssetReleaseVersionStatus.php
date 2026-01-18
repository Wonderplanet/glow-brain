<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Admin\Models\BaseAdmModel;

/**
 * mng_asset_release_versionに紐づくデータのパージ状態等を管理するテーブル
 *
 * @property string $id
 * @property string $mng_asset_release_version_id
 * @property string $ocarina_validated_status
 * @property string|null $ocarina_validation_version
 * @property CarbonImmutable|null $client_file_deleted_at
 * @property CarbonImmutable|null $server_db_deleted_at
 */
class AdmAssetReleaseVersionStatus extends BaseAdmModel
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mng_asset_release_version_id' => 'string',
        'ocarina_validated_status' => 'string',
        'ocarina_validation_version' => 'string',
        'client_file_deleted_at' => 'immutable_datetime',
        'server_db_deleted_at' => 'immutable_datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mng_asset_release_version_id',
        'ocarina_validated_status',
        'ocarina_validation_version',
        'client_file_deleted_at',
        'server_db_deleted_at',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function getMngAssetReleaseVersionId(): string
    {
        return $this->mng_asset_release_version_id;
    }

    public function getOcarinaValidatedStatus(): string
    {
        return $this->ocarina_validated_status;
    }

    public function getOcarinaValidationVersion(): string|null
    {
        return $this->ocarina_validation_version;
    }

    public function getClientFileDeletedAt(): CarbonImmutable|null
    {
        return $this->client_file_deleted_at;
    }

    public function getServerDbDeletedAt(): CarbonImmutable|null
    {
        return $this->server_db_deleted_at;
    }
}
