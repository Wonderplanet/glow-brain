<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstArtworkFragmentI18nEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_artwork_fragment_id
 * @property string $language
 * @property string $name
 * @property int $release_key
 */
class MstArtworkFragmentI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_artwork_fragments_i18n';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_artwork_fragment_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): MstArtworkFragmentI18nEntity
    {
        return new MstArtworkFragmentI18nEntity(
            $this->id,
            $this->mst_artwork_fragment_id,
            $this->language,
            $this->name,
            $this->release_key,
        );
    }
}
