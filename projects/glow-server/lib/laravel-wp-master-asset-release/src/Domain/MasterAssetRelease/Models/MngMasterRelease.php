<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Models;

use WonderPlanet\Domain\MasterAssetRelease\Entities\MngMasterReleaseEntity;

/**
 * マスターデータリリース管理テーブル
 * release_keyの登録とそのリリース状態を管理する
 *
 * @property string $id
 * @property int $release_key
 * @property int $enabled
 * @property string|null $target_release_version_id
 * @property string $client_compatibility_version
 * @property string|null $description
 * @property \Carbon\Carbon|null $start_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class MngMasterRelease extends BaseMngModel
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'enabled' => 'integer',
        'target_release_version_id' => 'string',
        'client_compatibility_version' => 'string',
        'description' => 'string',
        'start_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'release_key',
        'enabled',
        'target_release_version_id',
        'client_compatibility_version',
        'description',
        'start_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @return MngMasterReleaseEntity
     */
    public function toEntity(): MngMasterReleaseEntity
    {
        return new MngMasterReleaseEntity(
            $this->id,
            $this->release_key,
            $this->enabled,
            $this->target_release_version_id,
            $this->client_compatibility_version,
            $this->description,
            $this->start_at?->toImmutable(),
            $this->created_at?->toImmutable(),
            $this->updated_at?->toImmutable(),
        );
    }
}
