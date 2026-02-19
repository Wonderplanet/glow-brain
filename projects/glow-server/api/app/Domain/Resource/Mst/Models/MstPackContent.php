<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstPackContentEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_pack_id
 * @property string $resource_type
 * @property string $resource_id
 * @property int    $resource_amount
 * @property int    $is_bonus
 * @property int    $display_order
 */
class MstPackContent extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_pack_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'is_bonus' => 'integer',
        'display_order' => 'integer',
        'release_key' => 'integer',
    ];

    protected $fillable = [
        'id',
        'mst_pack_id',
        'resource_type',
        'resource_id',
        'resource_amount',
        'is_bonus',
        'display_order',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_pack_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->is_bonus,
            $this->display_order,
        );
    }
}
