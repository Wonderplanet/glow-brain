<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstAdventBattleRewardGroupEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_advent_battle_id
 * @property string $reward_category
 * @property string $condition_value
 * @property int    $release_key
 */
class MstAdventBattleRewardGroup extends MstModel
{
    use HasFactory;

    protected $table = 'mst_advent_battle_reward_groups';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_advent_battle_id' => 'string',
        'reward_category' => 'string',
        'condition_value' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_advent_battle_id,
            $this->reward_category,
            $this->condition_value,
        );
    }
}
