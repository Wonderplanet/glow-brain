<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstExchangeLineupEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $group_id
 * @property int|null $tradable_count
 * @property int    $display_order
 */
class MstExchangeLineup extends MstModel
{
    use HasFactory;

    protected $casts = [
        'id' => 'string',
        'group_id' => 'string',
        'tradable_count' => 'integer',
        'display_order' => 'integer',
    ];

    protected $guarded = [];

    public function toEntity(): MstExchangeLineupEntity
    {
        return new MstExchangeLineupEntity(
            $this->id,
            $this->group_id,
            $this->tradable_count,
            $this->display_order,
        );
    }
}
