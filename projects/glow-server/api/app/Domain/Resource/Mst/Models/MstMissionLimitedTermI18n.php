<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstMissionLimitedTermI18n extends MstModel
{
    use HasFactory;

    public $table = 'mst_mission_limited_terms_i18n';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_mission_limited_term_id' => 'string',
        'language' => 'string',
        'description' => 'string',
    ];
}
