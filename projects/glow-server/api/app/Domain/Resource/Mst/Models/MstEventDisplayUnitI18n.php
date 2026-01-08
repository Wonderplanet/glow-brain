<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstEventDisplayUnitI18n extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_event_display_unit_id' => 'string',
        'language' => 'string',
        'speech_balloon_text1' => 'string',
        'speech_balloon_text2' => 'string',
        'speech_balloon_text3' => 'string',
    ];
}
