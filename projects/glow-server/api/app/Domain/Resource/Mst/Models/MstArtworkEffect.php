<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkEffectEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_artwork_id
 * @property string $effect_type
 * @property float  $grade_level1_value
 * @property float  $grade_level2_value
 * @property float  $grade_level3_value
 * @property float  $grade_level4_value
 * @property float  $grade_level5_value
 * @property int    $release_key
 */
class MstArtworkEffect extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_id' => 'string',
        'effect_type' => 'string',
        'grade_level1_value' => 'float',
        'grade_level2_value' => 'float',
        'grade_level3_value' => 'float',
        'grade_level4_value' => 'float',
        'grade_level5_value' => 'float',
        'release_key' => 'integer',
    ];

    public function toEntity(): MstArtworkEffectEntity
    {
        return new MstArtworkEffectEntity(
            $this->id,
            $this->mst_artwork_id,
            $this->effect_type,
            $this->grade_level1_value,
            $this->grade_level2_value,
            $this->grade_level3_value,
            $this->grade_level4_value,
            $this->grade_level5_value,
        );
    }
}
