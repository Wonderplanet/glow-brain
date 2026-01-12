<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\OprProductI18nEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $opr_product_id
 * @property string $language
 * @property string $asset_key
 */
class OprProductI18n extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'opr_products_i18n';

    protected $casts = [
        'id' => 'string',
        'opr_product_id' => 'string',
        'language' => 'string',
        'asset_key' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->opr_product_id,
            $this->language,
            $this->asset_key,
        );
    }
}
