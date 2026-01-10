<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstAdventBattleClearRewardEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_advent_battle_id
 * @property string $reward_category
 * @property string $resource_type
 * @property ?string $resource_id
 * @property ?int   $resource_amount
 * @property int    $percentage
 */
class MstAdventBattleClearReward extends MstModel
{
    use HasFactory;

    protected $table = 'mst_advent_battle_clear_rewards';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_advent_battle_id' => 'string',
        'reward_category' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'percentage' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_advent_battle_id,
            $this->reward_category,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->percentage,
        );
    }
}
