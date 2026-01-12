<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\OprCampaignI18nEntity;
use App\Domain\Resource\Traits\HasFactory;

class OprCampaignI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'opr_campaign_id' => 'string',
        'language' => 'string',
        'description' => 'string',
    ];

    public function toEntity(): OprCampaignI18nEntity
    {
        return new OprCampaignI18nEntity(
            $this->id,
            $this->opr_campaign_id,
            $this->language,
            $this->description,
        );
    }
}
