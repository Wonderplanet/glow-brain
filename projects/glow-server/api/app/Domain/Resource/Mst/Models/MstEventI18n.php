<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_event_id
 * @property string $language
 * @property string $name
 * @property string $balloon
 * @property int $release_key
 */
class MstEventI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = "mst_events_i18n";

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_event_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'balloon' => 'string',
        'release_key' => 'integer',
    ];
}
