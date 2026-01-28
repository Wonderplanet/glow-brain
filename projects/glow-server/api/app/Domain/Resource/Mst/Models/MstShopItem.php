<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstShopItemEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $shop_type
 * @property string $cost_type
 * @property int    $cost_amount
 * @property int    $is_first_time_free
 * @property int    $tradable_count
 * @property string $resource_type
 * @property string $resource_id
 * @property int    $resource_amount
 * @property string $start_date
 * @property string $end_date
 */
class MstShopItem extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'shop_type' => 'string',
        'cost_type' => 'string',
        'cost_amount' => 'integer',
        'is_first_time_free' => 'integer',
        'tradable_count' => 'integer',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'start_date' => 'string',
        'end_date' => 'string',
        'release_key' => 'integer',
    ];

    protected $fillable = [
        'id',
        'shop_type',
        'cost_type',
        'cost_amount',
        'is_first_time_free',
        'tradable_count',
        'resource_type',
        'resource_id',
        'resource_amount',
        'start_date',
        'end_date',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->shop_type,
            $this->cost_type,
            $this->cost_amount,
            $this->is_first_time_free,
            $this->tradable_count,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->start_date,
            $this->end_date,
        );
    }
}
