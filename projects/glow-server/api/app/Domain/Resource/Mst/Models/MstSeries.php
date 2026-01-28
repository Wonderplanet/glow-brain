<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstSeriesEntity;
use App\Domain\Resource\Traits\HasFactory;

class MstSeries extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'jump_plus_url' => 'string',
        'asset_key' => 'string',
        'banner_asset_key' => 'string',
        'release_key' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'jump_plus_url',
        'asset_key',
        'banner_asset_key',
        'release_key',
    ];

    public function toEntity(): MstSeriesEntity
    {
        return new MstSeriesEntity(
            $this->id,
            $this->jump_plus_url,
            $this->asset_key,
            $this->banner_asset_key,
            $this->release_key,
        );
    }
}
