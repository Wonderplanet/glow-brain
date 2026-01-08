<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstArtwork as BaseMstArtwork;
use App\Utils\AssetUtil;

class MstArtwork extends BaseMstArtwork implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_artwork_i18n()
    {
        return $this->hasOne(MstArtworkI18n::class, 'mst_artwork_id', 'id');
    }

    public function mst_artwork_fragment()
    {
        return $this->hasMany(MstArtworkFragment::class, 'mst_artwork_id', 'id');
    }

    public function mst_series()
    {
        return $this->hasOne(MstSeries::class, 'id', 'mst_series_id');
    }

    public function getName(): string
    {
        return $this->mst_artwork_i18n?->name ?? '';
    }

    public function makeAssetPath(): ?string
    {
        return AssetUtil::findAssetPathFromTemplates(
            ['artwork_a/artwork_a!{release_key}/artwork_{asset_key}a.png'],
            $this->asset_key,
            $this->release_key
        );
    }

    public function makeBgPath(): ?string
    {
        return null;
    }
}
