<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkGradeUpEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string      $id
 * @property string|null $mst_artwork_id
 * @property string      $mst_series_id
 * @property string      $rarity
 * @property int         $grade_level
 * @property int         $release_key
 */
class MstArtworkGradeUp extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_id' => 'string',
        'mst_series_id' => 'string',
        'rarity' => 'string',
        'grade_level' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): MstArtworkGradeUpEntity
    {
        return new MstArtworkGradeUpEntity(
            $this->id,
            $this->mst_artwork_id,
            $this->mst_series_id,
            $this->rarity,
            $this->grade_level,
        );
    }
}
