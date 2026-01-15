<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstEnemyCharacterI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    public $table = 'mst_enemy_characters_i18n';

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_enemy_character_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'description' => 'string',
        'release_key' => 'integer',
    ];
}
