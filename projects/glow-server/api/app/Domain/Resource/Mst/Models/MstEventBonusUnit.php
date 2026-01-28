<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstEventBonusUnitEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int $release_key
 * @property string $mst_unit_id
 * @property int $bonus_percentage
 * @property string $event_bonus_group_id
 * @property int $is_pick_up
 */
class MstEventBonusUnit extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_unit_id' => 'string',
        'bonus_percentage' => 'integer',
        'event_bonus_group_id' => 'string',
        'is_pick_up' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->mst_unit_id,
            $this->bonus_percentage,
            $this->event_bonus_group_id,
            $this->is_pick_up,
        );
    }
}
