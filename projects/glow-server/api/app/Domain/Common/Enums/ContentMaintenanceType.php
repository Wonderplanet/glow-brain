<?php

declare(strict_types=1);

namespace App\Domain\Common\Enums;

/**
 * メンテナンス用コンテンツ種別
 */
enum ContentMaintenanceType: string
{
    // 降臨バトル
    case ADVENT_BATTLE = 'AdventBattle';

    // ランクマッチ
    case PVP = 'Pvp';

    // 強化クエスト
    case ENHANCE_QUEST = 'EnhanceQuest';

    // ガシャ
    case GACHA = 'Gacha';

    // ショップ
    case SHOP_ITEM = 'ShopItem';

    // パス
    case SHOP_PASS = 'ShopPass';

    // パック
    case SHOP_PACK = 'ShopPack';

    /**
     * コンテンツタイプに対応するリクエストパラメータ名を取得
     *
     * @return string|null
     */
    public function getRequestParameterName(): ?string
    {
        return match ($this) {
            self::GACHA => 'oprGachaId',
            self::PVP => null,
            self::ADVENT_BATTLE => null,
            self::ENHANCE_QUEST => 'mstStageId',
            self::SHOP_ITEM => null,
            self::SHOP_PASS => null,
            self::SHOP_PACK => null,
        };
    }

    public function isEnhanceQuestType(): bool
    {
        return $this === self::ENHANCE_QUEST;
    }
}
