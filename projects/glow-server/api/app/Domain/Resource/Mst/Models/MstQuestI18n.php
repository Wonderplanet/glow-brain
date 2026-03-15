<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstQuestI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    public $table = 'mst_quests_i18n';

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_quest_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'category_name' => 'string',
        'flavor_text' => 'string',
        'release_key' => 'integer',
    ];

    protected $fillable = [
        'id',
        'mst_quest_id',
        'language',
        'name',
        'category_name',
        'flavor_text',
        'release_key',
    ];
}
