<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionAchievementDependencyEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstMissionAchievementDependency extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'group_id' => 'string',
        'mst_mission_achievement_id' => 'string',
        'unlock_order' => 'integer',
    ];

    protected $fillable = [
        'id',
        'release_key',
        'group_id',
        'mst_mission_achievement_id',
        'unlock_order',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->group_id,
            $this->mst_mission_achievement_id,
            $this->unlock_order,
        );
    }
}
