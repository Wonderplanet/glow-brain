<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\OprStepupGachaEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class OprStepupGacha extends MstModel
{
    use HasFactory;

    protected $table = "opr_stepup_gachas";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'opr_gacha_id' => 'string',
        'max_step_number' => 'integer',
        'max_loop_count' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->opr_gacha_id,
            $this->max_step_number,
            $this->max_loop_count,
        );
    }
}
