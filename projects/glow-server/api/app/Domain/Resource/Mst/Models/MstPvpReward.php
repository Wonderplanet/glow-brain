<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstPvpRewardEntity as Entity;
use App\Domain\Resource\Mst\Models\MstModel;
use App\Domain\Resource\Traits\HasFactory;

class MstPvpReward extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_pvp_reward_group_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstPvpRewardGroupId(): string
    {
        return $this->mst_pvp_reward_group_id;
    }

    public function getResourceType(): string
    {
        return $this->resource_type;
    }

    public function getResourceId(): ?string
    {
        return $this->resource_id;
    }

    public function getResourceAmount(): int
    {
        return $this->resource_amount;
    }

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_pvp_reward_group_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount
        );
    }
}
