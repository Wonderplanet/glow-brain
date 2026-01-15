<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstKomaLine as BaseMstKomaLine;
use App\Utils\AssetUtil;

class MstKomaLine extends BaseMstKomaLine implements IAssetImage
{
    const KOMA_ASSET_SETTING_COUNT = 4; // コマのアセット最大設定数
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function makeAssetPathByKey($assetKeyName): ?string
    {
        if (empty($this->$assetKeyName)) {
            return null;
        }

        $pathPrefix = 'koma_background/koma_background/koma_background_';
        return AssetUtil::makeClientAssetBundlePath($pathPrefix . $this->{$assetKeyName} . '.png');
    }

    public function makeAssetPath(): ?string
    {
        return AssetUtil::findAssetPathFromTemplates(
            ['koma_background/koma_background!{release_key}/koma_background_{asset_key}.png'],
            $this->asset_key,
            $this->release_key
        );
    }

    public function makeBgPath(): ?string
    {
        return AssetUtil::makeBgItemIconFramePathByRarity($this->rarity);
    }

    /**
     * 設定されている有効コマの数を取得する
     * @return int
     */
    public function getKomaCount(): int
    {
        $validKomaCount = 0;
        $komaLoopCount = self::KOMA_ASSET_SETTING_COUNT;
        for ($i = 1; $i <= $komaLoopCount; $i++) {
            $komaKey = 'koma'.$i.'_asset_key';
            if (!empty($this->$komaKey)) {
                $validKomaCount++;
            }
        }

        return $validKomaCount ?? 0;
    }
}
