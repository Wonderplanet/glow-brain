<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

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
        'release_key' => 'integer',
    ];

    protected $fillable = [
        'id',
        'mst_store_product_id',
        'language',
        'price_ios',
        'price_android',
        'release_key',
    ];
}
