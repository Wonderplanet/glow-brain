<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstAttackEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_unit_id
 * @property int $unit_grade
 * @property string $attack_kind
 * @property string $asset_key
 * @property string $killer_colors
 * @property int $killer_percentage
 * @property int $action_frames
 * @property int $attack_delay
 * @property int $next_attack_interval
 */
class MstAttack extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_unit_id' => 'string',
        'unit_grade' => 'integer',
        'attack_kind' => 'string',
        'killer_colors' => 'string',
        'killer_percentage' => 'integer',
        'action_frames' => 'integer',
        'attack_delay' => 'integer',
        'next_attack_interval' => 'integer',
    ];

    /**
     * @return Entity
     */
    public function toEntity()
    {
        return new Entity(
            $this->id,
            $this->mst_unit_id,
            $this->unit_grade,
            $this->attack_kind,
            $this->killer_colors,
            $this->killer_percentage,
            $this->action_frames,
            $this->attack_delay,
            $this->next_attack_interval,
        );
    }
}
