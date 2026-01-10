<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstUnit as BaseMstUnit;
use App\Dtos\RewardDto;
use App\Constants\RewardType;
use App\Utils\AssetUtil;

class MstUnit extends BaseMstUnit implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_unit_i18n()
    {
        return $this->hasOne(MstUnitI18n::class, 'mst_unit_id', 'id');
    }

    public function mst_series()
    {
        return $this->hasOne(MstSeries::class, 'id', 'mst_series_id');
    }

    /**
     * $this->rewardにアクセスした際に呼ばれる
     * @return RewardDto
     */
    public function getRewardAttribute()
    {
        return new RewardDto(
            $this->id,
            RewardType::UNIT->value,
            $this->id,
            0,
        );
    }

    public function makeAssetPath(): ?string
    {
        return AssetUtil::findAssetPathFromTemplates(
            [
                'unit_icon_tutorial/unit_icon_{asset_key}.png',
                'unit_icon/unit_icon!{release_key}/unit_icon_{asset_key}.png',
                'unit_icon/unit_icon_l/unit_icon_{asset_key}.png', // バリエーション
                'unit_icon/unit_icon_sp/unit_icon_{asset_key}.png', // バリエーション
            ],
            $this->asset_key,
            $this->release_key
        );
    }

    public function makeBgPath(): ?string
    {
        return AssetUtil::makeBgItemIconFramePathByRarity($this->rarity);
    }
}
