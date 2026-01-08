<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstBoxGachaEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_event_id
 * @property string $cost_id
 * @property int $cost_num
 * @property string $loop_type
 */
class MstBoxGacha extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_event_id' => 'string',
        'cost_id' => 'string',
        'cost_num' => 'integer',
        'loop_type' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_event_id,
            $this->cost_id,
            $this->cost_num,
            $this->loop_type,
        );
    }
}
