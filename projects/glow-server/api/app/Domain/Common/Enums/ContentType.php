<?php

declare(strict_types=1);

namespace App\Domain\Common\Enums;

/**
 * ゲーム内コンテンツ種別
 */
enum ContentType: string
{
    // 探索
    case IDLE_INCENTIVE = 'IdleIncentive';

    // スタミナ購入
    case BUY_STAMINA = 'BuyStamina';

    // 交換所（mst_shop_itemsで管理しているショップの商品）
    case TRADE_SHOP_ITEM = 'TradeShopItem';

    // パック（mst_packsで管理しているパック商品）
    case SHOP_PACK = 'ShopPack';

    // ガシャ
    case GACHA = 'Gacha';

    // 降臨バトル
    case ADVENT_BATTLE = 'AdventBattle';

    // ステージ
    case STAGE = 'Stage';
}
