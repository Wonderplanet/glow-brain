<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Models;

use Illuminate\Support\Str;
use WonderPlanet\Domain\Common\Models\BaseMngModel as CommonBaseMngModel;
use WonderPlanet\Domain\MasterAssetRelease\Utils\DBUtility;

/**
 * mng DBレコードの基底クラス
 *
 * Commonの規定クラスを継承する
 * マスターアセット管理基盤向けの追加修正する必要がある場合がここに記載すること
 */
abstract class BaseMngModel extends CommonBaseMngModel
{
    /**
     * CommonBaseMngModelのコンストラクタのオーバーライド
     * wp_master_asset_api.tablenamesを参照するためにオーバーライドしている
     *
     * @param array<mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // tableにクラス名からテーブル名を設定する
        // デフォルトはクラス名のスネークケース(複数形)になるので、それを変換テーブルを通して設定されたテーブル名に直す
        $this->table = DBUtility::getTableName(Str::snake(Str::pluralStudly(class_basename($this))));
    }
}
