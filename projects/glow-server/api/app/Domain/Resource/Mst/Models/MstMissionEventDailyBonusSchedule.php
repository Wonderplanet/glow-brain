<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionEventDailyBonusScheduleEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_event_id
 * @property string $start_at
 * @property string $end_at
 * @property int $release_key
 */
class MstMissionEventDailyBonusSchedule extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_mission_event_daily_bonus_schedules';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_event_id' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_event_id,
            $this->start_at,
            $this->end_at,
            $this->release_key,
        );
    }
}
