<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstStageI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "mst_stages_i18n";

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_stage_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'release_key' => 'integer',
    ];
}
