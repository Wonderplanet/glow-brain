<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\ImagePath;
use App\Domain\Resource\Mst\Models\MstShopPass as BaseMstShopPass;
use App\Models\Mst\IAssetImage;
use App\Models\Opr\OprProduct;
use App\Utils\AssetUtil;

class MstShopPass extends BaseMstShopPass implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_shop_pass_i18n()
    {
        return $this->hasOne(MstShopPassI18n::class, 'mst_shop_pass_id', 'id');
    }

    public function mst_shop_pass_effect()
    {
        return $this->hasMany(MstShopPassEffect::class, 'mst_shop_pass_id', 'id');
    }

    public function mst_shop_pass_rewards()
    {
        return $this->hasMany(MstShopPassReward::class, 'mst_shop_pass_id', 'id');
    }

    public function opr_product()
    {
        return $this->hasOne(OprProduct::class, 'id', 'opr_product_id');
    }

    public function makeAssetPath(): string
    {
        $pathPrefix = ImagePath::SHOP_PASS_ICON->value;
        return AssetUtil::makeClientAssetBundlePath($pathPrefix . $this->asset_key . '.png');
    }

    public function makeBgPath(): ?string
    {
        return null;
    }
}
