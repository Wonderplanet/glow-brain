<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkI18nEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_artwork_id
 * @property string $language
 * @property string $name
 * @property string $description
 * @property int    $release_key
 */
class MstArtworkI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'mst_artworks_i18n';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'description' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): MstArtworkI18nEntity
    {
        return new MstArtworkI18nEntity(
            $this->id,
            $this->mst_artwork_id,
            $this->language,
            $this->name,
        );
    }
}
