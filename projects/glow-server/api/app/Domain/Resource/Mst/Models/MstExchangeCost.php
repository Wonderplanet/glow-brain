<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstExchangeCostEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_exchange_lineup_id
 * @property string $cost_type
 * @property string|null $cost_id
 * @property int    $cost_amount
 * @property int    $release_key
 */
class MstExchangeCost extends MstModel
{
    use HasFactory;

    protected $casts = [
        'id' => 'string',
        'mst_exchange_lineup_id' => 'string',
        'cost_type' => 'string',
        'cost_id' => 'string',
        'cost_amount' => 'integer',
        'release_key' => 'integer',
    ];

    protected $guarded = [];

    public function toEntity(): MstExchangeCostEntity
    {
        return new MstExchangeCostEntity(
            $this->id,
            $this->mst_exchange_lineup_id,
            $this->cost_type,
            $this->cost_id,
            $this->cost_amount,
            $this->release_key,
        );
    }
}
