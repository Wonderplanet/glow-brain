<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

/**
 * 画面遷移先として設定できるゲーム内の機能画面
 */
enum DestinationInGamePath: string
{
    // ショップ(有償)
    case SHOP_PAID = 'ShopPaid';

    // ショップ(無償)
    case SHOP_FREE = 'ShopFree';

    // パス
    case PASS = 'Pass';

    // ガシャ
    case GACHA = 'Gacha';

    // イベント
    case EVENT = 'Event';

    // お知らせ
    case NOTICE = 'Notice';

    // ランクマッチ
    case PVP = 'Pvp';

    // 交換所
    case EXCHANGE = 'Exchange';

    public function label(): string
    {
        return match ($this) {
            self::SHOP_PAID => '課金ショップ',
            self::SHOP_FREE => 'ショップ',
            self::PASS => 'パス',
            self::GACHA => 'ガシャ',
            self::EVENT => 'イベント',
            self::NOTICE => 'お知らせ',
            self::PVP => 'ランクマッチ',
            self::EXCHANGE => '交換所',
        };
    }

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->label());
        }
        return $labels;
    }
}
