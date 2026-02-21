<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionEventDependencyEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int    $release_key
 * @property string $group_id
 * @property string $mst_mission_event_id
 * @property int    $unlock_order
 */
class MstMissionEventDependency extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_mission_event_dependencies';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'group_id' => 'string',
        'mst_mission_event_id' => 'string',
        'unlock_order' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->group_id,
            $this->mst_mission_event_id,
            $this->unlock_order,
        );
    }
}
