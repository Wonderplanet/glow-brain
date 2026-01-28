<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstShopPassRewardEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_shop_pass_id
 * @property string $pass_reward_type
 * @property string $resource_type
 * @property string|null $resource_id
 * @property int $resource_amount
 */
class MstShopPassReward extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_shop_pass_id' => 'string',
        'pass_reward_type' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
    ];

    protected $guarded = [
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_shop_pass_id,
            $this->pass_reward_type,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
