<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstStageEnhanceRewardParamEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstStageEnhanceRewardParam extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'min_threshold_score' => 'integer',
        'coin_reward_amount' => 'integer',
        'coin_reward_size_type' => 'string',
        'release_key' => 'integer',
    ];

    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->min_threshold_score,
            $this->coin_reward_amount,
        );
    }
}
