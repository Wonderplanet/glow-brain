<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstOutpostEnhancementI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    public $table = 'mst_outpost_enhancements_i18n';

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_outpost_enhancement_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'release_key' => 'integer',
    ];

    protected $fillable = [
        'id',
        'mst_outpost_enhancement_id',
        'language',
        'name',
        'release_key',
    ];
}
