<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstResultTipI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "mst_result_tips_i18n";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'language' => 'string',
        'user_level' => 'integer',
        'result_tips' => 'string',
        'release_key' => 'integer',
    ];
}
