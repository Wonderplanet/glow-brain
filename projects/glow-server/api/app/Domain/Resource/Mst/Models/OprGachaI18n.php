<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Common\Enums\Language;
use App\Domain\Resource\Mst\Entities\OprGachaI18nEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class OprGachaI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "opr_gachas_i18n";

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'opr_gacha_id' => 'string',
        'language' => Language::class,
        'name' => 'string',
        'description' => 'string',
        'max_rarity_upper_description' => 'string',
        'pickup_upper_description' => 'string',
        'banner_url' => 'string',
        'logo_asset_key' => 'string',
        'logo_banner_url' => 'string',
        'gacha_background_color' => 'string',
        'gacha_banner_size' => 'string',
        'fixed_prize_description' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->opr_gacha_id,
            $this->language,
            $this->name,
            $this->description,
            $this->max_rarity_upper_description,
            $this->pickup_upper_description,
            $this->banner_url,
            $this->logo_banner_url,
        );
    }
}
