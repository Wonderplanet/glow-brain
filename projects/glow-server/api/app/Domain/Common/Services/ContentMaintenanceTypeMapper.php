<?php

declare(strict_types=1);

namespace App\Domain\Common\Services;

use App\Domain\Common\Enums\ContentMaintenanceType;

/**
 * リクエストパスからコンテンツメンテナンスタイプへのマッピングサービス
 */
class ContentMaintenanceTypeMapper
{
    /**
     * リクエストパスからコンテンツタイプを取得
     *
     * @param string $path リクエストパス（例: "api/gacha/draw"）
     * @return ContentMaintenanceType|null マッチするコンテンツタイプ、マッチしない場合はnull
     */
    public function getContentTypeFromPath(string $path): ?ContentMaintenanceType
    {
        $patterns = [
            // ガチャ関連のパターン
            '#^api/gacha/.*#' => ContentMaintenanceType::GACHA,

            // PvP関連のパターン
            '#^api/pvp/.*#' => ContentMaintenanceType::PVP,

            // 降臨バトル関連のパターン
            '#^api/advent_battle/.*#' => ContentMaintenanceType::ADVENT_BATTLE,

            // 強化クエスト関連のパターン
            '#^api/stage/.*#' => ContentMaintenanceType::ENHANCE_QUEST,

            // // ショップアイテム関連のパターン
            // '#^api/shop/trade_shop_item#' => ContentMaintenanceType::SHOP_ITEM,

            // // ショップパス関連のパターン
            // '#^api/shop/purchase_pass#' => ContentMaintenanceType::SHOP_PASS,

            // // ショップパック関連のパターン
            // '#^api/shop/trade_pack#' => ContentMaintenanceType::SHOP_PACK,
        ];

        foreach ($patterns as $pattern => $contentType) {
            if (preg_match($pattern, $path)) {
                return $contentType;
            }
        }

        return null;
    }
}
