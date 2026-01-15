<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\ImagePath;
use App\Domain\Resource\Mst\Models\MstArtworkFragment as BaseMstArtworkFragment;
use App\Domain\Resource\Mst\Models\MstArtworkFragmentPosition;
use App\Utils\AssetUtil;

class MstArtworkFragment extends BaseMstArtworkFragment implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_artwork_fragment_i18n()
    {
        return $this->hasOne(MstArtworkFragmentI18n::class, 'mst_artwork_fragment_id', 'id');
    }

    public function mst_artwork_fragment_position()
    {
        return $this->hasOne(MstArtworkFragmentPosition::class, 'mst_artwork_fragment_id', 'id');
    }

    public function mst_stage()
    {
        return $this->hasOne(MstStage::class, 'mst_artwork_fragment_drop_group_id', 'drop_group_id');
    }

    public function makeAssetPath(): ?string
    {
        $pathPrefix = ImagePath::ARTWORK_FRAGMENT_PATH->value;
        $assetNumPadded = str_pad((string)$this->asset_num, 2, '0', STR_PAD_LEFT);
        return AssetUtil::makeClientAssetBundlePath($pathPrefix . $assetNumPadded . '.png');
    }

    public function makeBgPath(): ?string
    {
        return null;
    }
}
