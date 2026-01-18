<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionEventDailyBonusEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_mission_event_daily_bonus_schedule_id
 * @property int $login_day_count
 * @property string $mst_mission_reward_group_id
 * @property int $sort_order
 * @property int $release_key
 */
class MstMissionEventDailyBonus extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_mission_event_daily_bonuses';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_mission_event_daily_bonus_schedule_id' => 'string',
        'login_day_count' => 'integer',
        'mst_mission_reward_group_id' => 'string',
        'sort_order' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_mission_event_daily_bonus_schedule_id,
            $this->login_day_count,
            $this->mst_mission_reward_group_id,
            $this->sort_order,
            $this->release_key,
        );
    }
}
