<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstOutpostEnhancementLevelI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    public $table = 'mst_outpost_enhancement_levels_i18n';

    protected $connection = 'mst';

    protected $fillable = [
        'id',
        'mst_outpost_enhancement_level_id',
        'language',
        'description',
        'release_key',
    ];

    protected $casts = [
        'id' => 'string',
        'mst_outpost_enhancement_level_id' => 'string',
        'language' => 'string',
        'description' => 'string',
        'release_key' => 'integer',
    ];
}
