<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstAdventBattleEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $advent_battle_type
 * @property ?string $mst_stage_rule_group_id
 * @property string $event_bonus_group_id
 * @property int    $challengeable_count
 * @property int    $ad_challengeable_count
 * @property ?string $display_mst_unit_id1
 * @property ?string $display_mst_unit_id2
 * @property ?string $display_mst_unit_id3
 * @property int    $exp
 * @property int    $coin
 * @property string $start_at
 * @property string $end_at
 */
class MstAdventBattle extends MstModel
{
    use HasFactory;

    protected $table = 'mst_advent_battles';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_event_id' => 'string',
        'advent_battle_type' => 'string',
        'event_bonus_group_id' => 'string',
        'challengeable_count' => 'integer',
        'ad_challengeable_count' => 'integer',
        'display_mst_unit_id1' => 'string',
        'display_mst_unit_id2' => 'string',
        'display_mst_unit_id3' => 'string',
        'exp' => 'integer',
        'coin' => 'integer',
        'start_at' => 'string',
        'end_at' => 'string',
        'score_additional_coef' => 'float',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->advent_battle_type,
            $this->event_bonus_group_id,
            $this->challengeable_count,
            $this->ad_challengeable_count,
            $this->exp,
            $this->coin,
            $this->start_at,
            $this->end_at,
        );
    }
}
