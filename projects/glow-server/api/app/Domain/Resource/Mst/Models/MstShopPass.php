<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstShopPassEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $opr_product_id
 * @property int    $is_display_expiration
 * @property int    $pass_duration_days
 * @property string $asset_key
 * @property int    $release_key
 */
class MstShopPass extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'opr_product_id' => 'string',
        'is_display_expiration' => 'integer',
        'pass_duration_days' => 'integer',
        'asset_key' => 'string',
        'shop_pass_cell_color' => 'string',
        'release_key' => 'integer',
    ];

    protected $guarded = [
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->opr_product_id,
            $this->is_display_expiration,
            $this->pass_duration_days,
            $this->asset_key,
            $this->release_key,
        );
    }
}
