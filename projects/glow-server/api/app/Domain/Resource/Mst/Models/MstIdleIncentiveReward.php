<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstIdleIncentiveRewardEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstIdleIncentiveReward extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_stage_id' => 'string',
        'base_coin_amount' => 'decimal:4',
        'base_exp_amount' => 'decimal:4',
        'base_rank_up_material_amount' => 'decimal:2',
        'mst_idle_incentive_item_group_id' => 'string',
    ];

    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_stage_id,
            $this->base_coin_amount,
            $this->base_exp_amount,
            $this->mst_idle_incentive_item_group_id,
        );
    }
}
