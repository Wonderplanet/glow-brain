<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstExchangeEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string      $id
 * @property string|null $mst_event_id
 * @property string      $exchange_trade_type
 * @property string      $start_at
 * @property string|null $end_at
 * @property string      $lineup_group_id
 * @property int         $display_order
 */
class MstExchange extends MstModel
{
    use HasFactory;

    protected $casts = [
        'id' => 'string',
        'mst_event_id' => 'string',
        'exchange_trade_type' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
        'lineup_group_id' => 'string',
        'display_order' => 'integer',
    ];

    protected $guarded = [];

    public function toEntity(): MstExchangeEntity
    {
        return new MstExchangeEntity(
            $this->id,
            $this->exchange_trade_type,
            $this->start_at,
            $this->end_at,
            $this->lineup_group_id,
            $this->display_order,
        );
    }
}
