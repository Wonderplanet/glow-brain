<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstItemRarityTradeEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstItemRarityTrade extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'rarity' => 'string',
        'cost_amount' => 'integer',
        'reset_type' => 'string',
        'max_tradable_amount' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->rarity,
            $this->cost_amount,
            $this->reset_type,
            $this->max_tradable_amount,
        );
    }
}
