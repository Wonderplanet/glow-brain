<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstMissionBeginnerPromptPhraseI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'mst_mission_beginner_prompt_phrases_i18n';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'language' => 'string',
        'prompt_phrase_text' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
    ];
}
