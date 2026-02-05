<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkEffectTargetRuleEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_artwork_effect_id
 * @property string $condition_type
 * @property string $condition_value
 * @property int    $release_key
 */
class MstArtworkEffectTargetRule extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_effect_id' => 'string',
        'condition_type' => 'string',
        'condition_value' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): MstArtworkEffectTargetRuleEntity
    {
        return new MstArtworkEffectTargetRuleEntity(
            $this->id,
            $this->mst_artwork_effect_id,
            $this->condition_type,
            $this->condition_value,
        );
    }
}
