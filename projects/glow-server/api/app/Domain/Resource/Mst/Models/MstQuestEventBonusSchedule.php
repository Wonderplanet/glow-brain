<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstQuestEventBonusScheduleEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int $release_key
 * @property string $mst_quest_id
 * @property string $event_bonus_group_id
 * @property string $start_at
 * @property string $end_at
 */
class MstQuestEventBonusSchedule extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_quest_id' => 'string',
        'event_bonus_group_id' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->mst_quest_id,
            $this->event_bonus_group_id,
            $this->start_at,
            $this->end_at,
        );
    }
}
