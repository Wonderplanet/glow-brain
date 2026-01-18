<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstItemEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $type
 * @property string $group_type
 * @property string $rarity
 * @property string $asset_key
 * @property string $effect_value
 * @property int $sort_order
 * @property string $start_date
 * @property string $end_date
 */
class MstItem extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'type' => 'string',
        'group_type' => 'string',
        'rarity' => 'string',
        'asset_key' => 'string',
        'effect_value' => 'string',
        'mst_series_id' => 'string',
        'sort_order' => 'integer',
        'start_date' => 'string',
        'end_date' => 'string',
        'release_key' => 'integer',
        'destination_opr_product_id' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->type,
            $this->group_type,
            $this->rarity,
            $this->asset_key,
            $this->effect_value,
            $this->sort_order,
            $this->start_date,
            $this->end_date,
        );
    }
}
