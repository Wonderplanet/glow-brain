<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_artwork_id
 * @property string $content_type
 * @property string $content_id
 * @property int    $release_key
 */
class MstArtworkAcquisitionRoute extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_id' => 'string',
        'content_type' => 'string',
        'content_id' => 'string',
        'release_key' => 'integer',
    ];
}
