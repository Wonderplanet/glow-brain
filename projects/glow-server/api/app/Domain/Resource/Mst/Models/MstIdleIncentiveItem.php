<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstIdleIncentiveItemEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstIdleIncentiveItem extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_idle_incentive_item_group_id' => 'string',
        'mst_item_id' => 'string',
        'base_amount' => 'decimal:4',
    ];

    protected $fillable = [
        'id',
        'release_key',
        'mst_idle_incentive_item_group_id',
        'mst_item_id',
        'base_amount',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_idle_incentive_item_group_id,
            $this->mst_item_id,
            $this->base_amount,
        );
    }
}
