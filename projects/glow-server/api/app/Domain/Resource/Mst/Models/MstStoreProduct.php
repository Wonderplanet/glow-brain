<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstStoreProductEntity;
use App\Domain\Resource\Traits\HasFactory;

class MstStoreProduct extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'product_id_ios' => 'string',
        'product_id_android' => 'string',
    ];

    protected $fillable = [
        'id',
        'release_key',
        'product_id_ios',
        'product_id_android',
    ];

    public function toEntity(): MstStoreProductEntity
    {
        return new MstStoreProductEntity(
            $this->id,
            $this->release_key,
            $this->product_id_ios,
            $this->product_id_android
        );
    }
}
