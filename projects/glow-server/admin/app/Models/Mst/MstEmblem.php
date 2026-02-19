<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstEmblem as BaseMstEmblem;
use App\Utils\AssetUtil;

class MstEmblem extends BaseMstEmblem implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_emblem_i18n()
    {
        return $this->hasOne(MstEmblemI18n::class, 'mst_emblem_id', 'id');
    }

    public function mst_series()
    {
        return $this->belongsTo(MstSeries::class, 'mst_series_id', 'id');
    }

    public function getName(): string
    {
        return $this->mst_emblem_i18n?->name ?? '';
    }

    public function makeAssetPath(): ?string
    {
        return AssetUtil::findAssetPathFromTemplates(
            [
                'emblem_icon/emblem_icon!{release_key}/emblem_icon_{asset_key}.png',
                'emblem_icon/emblem_icon!{release_key}/{asset_key}.png',
            ],
            $this->asset_key,
            $this->release_key
        );
    }

    public function makeBgPath(): ?string
    {
        // 背景なし
        return null;
    }
}
