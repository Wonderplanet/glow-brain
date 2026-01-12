<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstShopPassEffectEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_shop_pass_id
 * @property string $effect_type
 * @property int $effect_value
 * @property int $release_key
 */
class MstShopPassEffect extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_shop_pass_id' => 'string',
        'effect_type' => 'string',
        'effect_value' => 'integer',
        'release_key' => 'integer',
    ];

    protected $guarded = [
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_shop_pass_id,
            $this->effect_type,
            $this->effect_value,
            $this->release_key,
        );
    }
}
