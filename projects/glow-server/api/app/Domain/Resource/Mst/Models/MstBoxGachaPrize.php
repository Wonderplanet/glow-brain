<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstBoxGachaPrizeEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_box_gacha_group_id
 * @property bool $is_pickup
 * @property string $resource_type
 * @property string|null $resource_id
 * @property int $resource_amount
 * @property int $stock
 */
class MstBoxGachaPrize extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_box_gacha_group_id' => 'string',
        'is_pickup' => 'boolean',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'stock' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_box_gacha_group_id,
            $this->is_pickup,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->stock,
        );
    }
}
