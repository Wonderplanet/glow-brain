<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstEmblemI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = "mst_emblems_i18n";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_emblem_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'description' => 'string',
        'release_key' => 'integer',
    ];
}
