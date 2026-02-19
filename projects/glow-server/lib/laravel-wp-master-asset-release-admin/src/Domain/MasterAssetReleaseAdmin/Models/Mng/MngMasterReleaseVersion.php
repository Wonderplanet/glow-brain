<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng;

use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion as BaseMngMasterReleaseVersion;

class MngMasterReleaseVersion extends BaseMngMasterReleaseVersion
{
    private const MASTER_DATA_DB_PREFIX = 'mst_';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->connection = DBUtility::getMngConnName();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mngMasterRelease(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MngMasterRelease::class);
    }

    /**
     * envやDBの情報からDB名を取得する
     *
     * @return string
     */
    public function getDbName(): string
    {
        return config('app.env') . '_'
            . self::MASTER_DATA_DB_PREFIX
            . $this->release_key
            . '_' . $this->server_db_hash;

    }
}
