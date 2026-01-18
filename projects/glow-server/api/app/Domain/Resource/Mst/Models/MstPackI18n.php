<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_pack_id
 * @property string $language
 * @property string $name
 * @property int $release_key
 */
class MstPackI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    public $table = 'mst_packs_i18n';

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_pack_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'release_key' => 'integer',
    ];

    protected $fillable = [
        'id',
        'mst_pack_id',
        'language',
        'name',
        'release_key',
    ];
}
