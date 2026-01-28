<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Gacha\Enums\CostType;
use App\Domain\Resource\Mst\Entities\OprGachaUseResourceEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class OprGachaUseResource extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = "opr_gacha_use_resources";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'opr_gacha_id' => 'string',
        'cost_type' => CostType::class,
        'cost_id' => 'string',
        'cost_num' => 'integer',
        'draw_count' => 'integer',
        'cost_priority' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->opr_gacha_id,
            $this->cost_type,
            $this->cost_id,
            $this->cost_num,
            $this->draw_count,
            $this->cost_priority
        );
    }
}
