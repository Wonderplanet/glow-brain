<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionLimitedTermDependencyEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int    $release_key
 * @property string $group_id
 * @property string $mst_mission_limited_term_id
 * @property int    $unlock_order
 */
class MstMissionLimitedTermDependency extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'mst_mission_limited_term_dependencies';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'group_id' => 'string',
        'mst_mission_limited_term_id' => 'string',
        'unlock_order' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->group_id,
            $this->mst_mission_limited_term_id,
            $this->unlock_order,
        );
    }
}
