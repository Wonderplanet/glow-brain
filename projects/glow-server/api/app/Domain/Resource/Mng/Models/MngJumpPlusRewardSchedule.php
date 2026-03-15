<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngJumpPlusRewardScheduleEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MngJumpPlusRewardSchedule extends MngModel
{
    use HasFactory;

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'group_id' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->group_id,
            $this->start_at,
            $this->end_at,
        );
    }
}
