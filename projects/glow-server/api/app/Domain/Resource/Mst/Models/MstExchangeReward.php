<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstExchangeRewardEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_exchange_lineup_id
 * @property string $resource_type
 * @property string|null $resource_id
 * @property int    $resource_amount
 * @property int    $release_key
 */
class MstExchangeReward extends MstModel
{
    use HasFactory;

    protected $casts = [
        'id' => 'string',
        'mst_exchange_lineup_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'release_key' => 'integer',
    ];

    protected $guarded = [];

    public function toEntity(): MstExchangeRewardEntity
    {
        return new MstExchangeRewardEntity(
            $this->id,
            $this->mst_exchange_lineup_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->release_key,
        );
    }
}
