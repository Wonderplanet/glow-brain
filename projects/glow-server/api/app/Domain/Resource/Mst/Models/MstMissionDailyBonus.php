<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionDailyBonusEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mission_daily_bonus_type
 * @property int $login_day_count
 * @property string $mst_mission_reward_group_id
 * @property int $sort_order
 * @property int $release_key
 */
class MstMissionDailyBonus extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_mission_daily_bonuses';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mission_daily_bonus_type' => 'string',
        'login_day_count' => 'integer',
        'mst_mission_reward_group_id' => 'string',
        'sort_order' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->mission_daily_bonus_type,
            $this->login_day_count,
            $this->mst_mission_reward_group_id,
            $this->sort_order,
        );
    }
}
