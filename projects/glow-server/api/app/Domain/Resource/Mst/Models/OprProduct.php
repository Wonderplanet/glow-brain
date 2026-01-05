<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\OprProductEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_store_product_id
 * @property string $product_type
 * @property int $purchasable_count
 * @property int $paid_amount
 * @property int $display_priority
 * @property string $start_date
 * @property string $end_date
 */
class OprProduct extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'mst_store_product_id' => 'string',
        'product_type' => 'string',
        'purchasable_count' => 'integer',
        'paid_amount' => 'integer',
        'display_priority' => 'integer',
        'start_date' => 'string',
        'end_date' => 'string',
    ];

    protected $fillable = [
        'id',
        'mst_store_product_id',
        'product_type',
        'purchasable_count',
        'paid_amount',
        'display_priority',
        'start_date',
        'end_date',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_store_product_id,
            $this->product_type,
            $this->purchasable_count,
            $this->paid_amount,
            $this->display_priority,
            $this->start_date,
            $this->end_date,
        );
    }
}
