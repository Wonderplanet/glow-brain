<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\OprStepupGachaStepI18nEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class OprStepupGachaStepI18n extends MstModel
{
    use HasFactory;

    protected $table = "opr_stepup_gacha_steps_i18n";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'opr_stepup_gacha_step_id' => 'string',
        'language' => 'string',
        'fixed_prize_description' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->opr_stepup_gacha_step_id,
            $this->language,
            $this->fixed_prize_description,
        );
    }
}
