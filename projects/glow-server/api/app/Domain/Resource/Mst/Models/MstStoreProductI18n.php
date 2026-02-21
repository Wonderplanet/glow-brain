<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstStoreProductI18nEntity;
use App\Domain\Resource\Traits\HasFactory;

class MstStoreProductI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    public $table = 'mst_store_products_i18n';

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_store_product_id' => 'string',
        'language' => 'string',
        'price_ios' => 'float',
        'price_android' => 'float',
        'price_webstore' => 'float',
        'paid_diamond_price_ios' => 'float',
        'paid_diamond_price_android' => 'float',
        'paid_diamond_price_webstore' => 'float',
        'release_key' => 'integer',
    ];

    protected $fillable = [
        'id',
        'mst_store_product_id',
        'language',
        'price_ios',
        'price_android',
        'price_webstore',
        'paid_diamond_price_ios',
        'paid_diamond_price_android',
        'paid_diamond_price_webstore',
        'release_key',
    ];

    public function toEntity(): MstStoreProductI18nEntity
    {
        return new MstStoreProductI18nEntity(
            $this->id,
            $this->mst_store_product_id,
            $this->language,
            $this->price_ios,
            $this->price_android,
            $this->price_webstore,
            $this->paid_diamond_price_ios,
            $this->paid_diamond_price_android,
            $this->paid_diamond_price_webstore,
            $this->release_key,
        );
    }
}
