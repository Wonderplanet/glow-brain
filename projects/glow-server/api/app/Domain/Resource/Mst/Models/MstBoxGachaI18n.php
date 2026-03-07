<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstBoxGachaI18n extends MstModel
{
    use HasFactory;

    protected $table = "mst_box_gachas_i18n";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_box_gacha_id' => 'string',
        'language' => 'string',
        'name' => 'string',
    ];
}
