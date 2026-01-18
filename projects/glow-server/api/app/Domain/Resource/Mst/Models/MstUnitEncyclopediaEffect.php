<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaEffectEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_unit_encyclopedia_reward_id
 * @property string $effect_type
 * @property float $value
 * @property int $release_key
 */
class MstUnitEncyclopediaEffect extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_unit_encyclopedia_reward_id' => 'string',
        'effect_type' => 'string',
        'value' => 'float',
        'release_key' => 'integer',
    ];

    protected $guarded = [];


    public function toEntity(): MstUnitEncyclopediaEffectEntity
    {
        return new MstUnitEncyclopediaEffectEntity(
            $this->id,
            $this->mst_unit_encyclopedia_reward_id,
            $this->effect_type,
            $this->value,
            $this->release_key,
        );
    }
}
