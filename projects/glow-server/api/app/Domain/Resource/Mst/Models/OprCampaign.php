<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\OprCampaignEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $campaign_type
 * @property string $target_type
 * @property null|string $difficulty
 * @property null|string $target_id_type
 * @property null|string $target_id
 * @property int $effect_value
 * @property string $start_at
 * @property string $end_at
 */
class OprCampaign extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'campaign_type' => 'string',
        'target_type' => 'string',
        'difficulty' => 'string',
        'target_id_type' => 'string',
        'target_id' => 'string',
        'effect_value' => 'integer',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    public function toEntity(): OprCampaignEntity
    {
        return new OprCampaignEntity(
            $this->id,
            $this->campaign_type,
            $this->target_type,
            $this->difficulty,
            $this->target_id_type,
            $this->target_id,
            $this->effect_value,
            $this->start_at,
            $this->end_at,
        );
    }
}
