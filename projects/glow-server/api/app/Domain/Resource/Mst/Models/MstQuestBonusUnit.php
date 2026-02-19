<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstQuestBonusUnitEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_quest_id
 * @property string $mst_unit_id
 * @property float $coin_bonus_rate
 * @property string $start_at
 * @property string $end_at
 */
class MstQuestBonusUnit extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = "mst_quest_bonus_units";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_quest_id' => 'string',
        'mst_unit_id' => 'string',
        'coin_bonus_rate' => 'float',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_quest_id,
            $this->mst_unit_id,
            $this->coin_bonus_rate,
            $this->start_at,
            $this->end_at
        );
    }
}
