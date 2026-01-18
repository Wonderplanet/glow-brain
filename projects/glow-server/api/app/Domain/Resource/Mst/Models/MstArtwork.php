<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_series_id
 * @property int $outpost_additional_hp
 * @property string $asset_key
 * @property int $sort_order
 * @property int $release_key
 */
class MstArtwork extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_artworks';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_series_id' => 'string',
        'outpost_additional_hp' => 'integer',
        'asset_key' => 'string',
        'sort_order' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): MstArtworkEntity
    {
        return new MstArtworkEntity(
            $this->id,
            $this->mst_series_id,
            $this->outpost_additional_hp,
            $this->asset_key,
            $this->sort_order,
            $this->release_key,
        );
    }
}
