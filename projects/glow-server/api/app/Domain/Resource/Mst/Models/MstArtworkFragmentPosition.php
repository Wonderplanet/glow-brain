<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkFragmentPositionEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_artwork_fragment_id
 * @property int $position
 * @property int $release_key
 */
class MstArtworkFragmentPosition extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_artwork_fragment_positions';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_fragment_id' => 'string',
        'position' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): MstArtworkFragmentPositionEntity
    {
        return new MstArtworkFragmentPositionEntity(
            $this->id,
            $this->mst_artwork_fragment_id,
            $this->position,
            $this->release_key,
        );
    }
}
