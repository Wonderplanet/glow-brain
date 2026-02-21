<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Gacha\Enums\CostType;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Mst\Entities\OprStepupGachaStepEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class OprStepupGachaStep extends MstModel
{
    use HasFactory;

    protected $table = "opr_stepup_gacha_steps";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'opr_gacha_id' => 'string',
        'step_number' => 'integer',
        'cost_type' => CostType::class,
        'cost_id' => 'string',
        'cost_num' => 'integer',
        'draw_count' => 'integer',
        'fixed_prize_count' => 'integer',
        'fixed_prize_rarity_threshold_type' => RarityType::class,
        'prize_group_id' => 'string',
        'fixed_prize_group_id' => 'string',
        'is_first_free' => 'boolean',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->opr_gacha_id,
            $this->step_number,
            $this->cost_type,
            $this->cost_id,
            $this->cost_num,
            $this->draw_count,
            $this->fixed_prize_count,
            $this->fixed_prize_rarity_threshold_type,
            $this->prize_group_id,
            $this->fixed_prize_group_id,
            $this->is_first_free,
        );
    }
}
