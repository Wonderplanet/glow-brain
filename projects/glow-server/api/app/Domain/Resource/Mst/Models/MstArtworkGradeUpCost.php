<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkGradeUpCostEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_artwork_grade_up_id
 * @property string $resource_type
 * @property string $resource_id
 * @property int    $resource_amount
 * @property int    $release_key
 */
class MstArtworkGradeUpCost extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_grade_up_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): MstArtworkGradeUpCostEntity
    {
        return new MstArtworkGradeUpCostEntity(
            $this->id,
            $this->mst_artwork_grade_up_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
