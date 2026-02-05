<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstPackEntity as Entity;

/**
 * @property string $id
 * @property string $product_sub_id
 * @property int    $discount_rate
 * @property string $sale_condition
 * @property string $sale_condition_value
 * @property int    $sale_hours
 * @property string $cost_type
 * @property int    $cost_amount
 * @property int    $is_recommend
 * @property string $asset_key
 */
class MstPack extends MstModel
{
    protected $casts = [
        'id' => 'string',
        'product_sub_type' => 'string',
        'discount_rate' => 'integer',
        'pack_type' => 'string',
        'sale_condition' => 'string',
        'sale_condition_value' => 'string',
        'sale_hours' => 'integer',
        'tradable_count' => 'integer',
        'cost_type' => 'string',
        'cost_amount' => 'integer',
        'is_recommend' => 'integer',
        'is_first_time_free' => 'integer',
        'is_display_expiration' => 'integer',
        'asset_key' => 'string',
        'pack_decoration' => 'string',
        'release_key' => 'integer',
    ];

    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->product_sub_id,
            $this->discount_rate,
            $this->pack_type,
            $this->sale_condition,
            $this->sale_condition_value,
            $this->sale_hours,
            $this->tradable_count,
            $this->cost_type,
            $this->cost_amount,
            $this->is_recommend,
            $this->asset_key,
            $this->pack_decoration,
            $this->is_first_time_free,
        );
    }
}
