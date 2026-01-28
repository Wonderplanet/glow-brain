<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstPvpRewardGroupEntity as Entity;
use App\Domain\Resource\Mst\Models\MstModel;
use App\Domain\Resource\Traits\HasFactory;

class MstPvpRewardGroup extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'reward_category'  => 'string',
        'condition_value' => 'string',
        'mst_pvp_id' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->reward_category,
            $this->condition_value,
            $this->mst_pvp_id,
        );
    }
}
