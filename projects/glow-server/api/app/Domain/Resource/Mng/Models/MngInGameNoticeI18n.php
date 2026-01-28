<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngInGameNoticeI18nEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MngInGameNoticeI18n extends MngModel
{
    use HasFactory;

    public $timestamps = true;

    protected $table = "mng_in_game_notices_i18n";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mng_in_game_notice_id' => 'string',
        'language' => 'string',
        'title' => 'string',
        'description' => 'string',
        'banner_url' => 'string',
        'button_title' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mng_in_game_notice_id,
            $this->language,
            $this->title,
            $this->description,
            $this->banner_url,
            $this->button_title,
        );
    }
}
