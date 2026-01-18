<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstAdventBattleRewardEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_advent_battle_reward_group_id
 * @property string $resource_type
 * @property ?string $resource_id
 * @property ?int   $resource_amount
 * @property int    $release_key
 */
class MstAdventBattleReward extends MstModel
{
    use HasFactory;

    protected $table = 'mst_advent_battle_rewards';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_advent_battle_reward_group_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_advent_battle_reward_group_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
