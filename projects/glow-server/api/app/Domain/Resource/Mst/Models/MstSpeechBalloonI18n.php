<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstSpeechBalloonI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_speech_balloons_i18n';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_unit_id' => 'string',
        'language' => 'string',
        'condition_type' => 'string',
        'balloon_type' => 'string',
        'side' => 'string',
        'duration' => 'float',
        'text' => 'string',
    ];
}
