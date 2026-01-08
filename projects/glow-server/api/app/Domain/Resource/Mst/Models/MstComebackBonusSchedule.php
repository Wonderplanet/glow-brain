<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstComebackBonusScheduleEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int $inactive_condition_days
 * @property int $duration_days
 * @property string $start_at
 * @property string $end_at
 * @property int $release_key
 */
class MstComebackBonusSchedule extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_comeback_bonus_schedules';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'inactive_condition_days' => 'integer',
        'duration_days' => 'integer',
        'start_at' => 'string',
        'end_at' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->inactive_condition_days,
            $this->duration_days,
            $this->start_at,
            $this->end_at,
        );
    }
}
