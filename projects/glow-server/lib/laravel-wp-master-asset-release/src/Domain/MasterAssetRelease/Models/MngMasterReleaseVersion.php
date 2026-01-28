<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Models;

use WonderPlanet\Domain\MasterAssetRelease\Entities\MngMasterReleaseVersionEntity;

/**
 * マスターデータの配信単位となるテーブル
 *
 * @property string $id
 * @property int $release_key
 * @property string $git_revision
 * @property string $master_schema_version
 * @property string $data_hash
 * @property string $server_db_hash
 * @property string $client_mst_data_hash
 * @property string $client_mst_data_i18n_ja_hash
 * @property string $client_mst_data_i18n_en_hash
 * @property string $client_mst_data_i18n_zh_hash
 * @property string $client_opr_data_hash
 * @property string $client_opr_data_i18n_ja_hash
 * @property string $client_opr_data_i18n_en_hash
 * @property string $client_opr_data_i18n_zh_hash
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class MngMasterReleaseVersion extends BaseMngModel
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'git_revision' => 'string',
        'master_schema_version' => 'string',
        'data_hash' => 'string',
        'server_db_hash' => 'string',
        'client_mst_data_hash' => 'string',
        'client_mst_data_i18n_ja_hash' => 'string',
        'client_mst_data_i18n_en_hash' => 'string',
        'client_mst_data_i18n_zh_hash' => 'string',
        'client_opr_data_hash' => 'string',
        'client_opr_data_i18n_ja_hash' => 'string',
        'client_opr_data_i18n_en_hash' => 'string',
        'client_opr_data_i18n_zh_hash' => 'string',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'release_key',
        'git_revision',
        'master_schema_version',
        'data_hash',
        'server_db_hash',
        'client_mst_data_hash',
        'client_mst_data_i18n_ja_hash',
        'client_mst_data_i18n_en_hash',
        'client_mst_data_i18n_zh_hash',
        'client_opr_data_hash',
        'client_opr_data_i18n_ja_hash',
        'client_opr_data_i18n_en_hash',
        'client_opr_data_i18n_zh_hash',
        'created_at',
        'updated_at',
    ];

    /**
     * @return MngMasterReleaseVersionEntity
     */
    public function toEntity(): MngMasterReleaseVersionEntity
    {
        return new MngMasterReleaseVersionEntity(
            $this->id,
            $this->release_key,
            $this->git_revision,
            $this->master_schema_version,
            $this->data_hash,
            $this->server_db_hash,
            $this->client_mst_data_hash,
            $this->client_mst_data_i18n_ja_hash,
            $this->client_mst_data_i18n_en_hash,
            $this->client_mst_data_i18n_zh_hash,
            $this->client_opr_data_hash,
            $this->client_opr_data_i18n_ja_hash,
            $this->client_opr_data_i18n_en_hash,
            $this->client_opr_data_i18n_zh_hash,
            $this->created_at?->toImmutable(),
            $this->updated_at?->toImmutable(),
        );
    }
}
