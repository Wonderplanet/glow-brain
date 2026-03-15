<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Common\Enums\ContentMaintenanceType as BaseContentMaintenanceType;
use Illuminate\Support\Collection;

/**
 * メンテナンス用コンテンツ種別
 */
enum ContentMaintenanceType: string
{
    // 降臨バトル
    case ADVENT_BATTLE = BaseContentMaintenanceType::ADVENT_BATTLE->value;

    // ランクマッチ
    case PVP = BaseContentMaintenanceType::PVP->value;

    // 強化クエスト
    case ENHANCE_QUEST = BaseContentMaintenanceType::ENHANCE_QUEST->value;

    // ガシャ
    case GACHA = BaseContentMaintenanceType::GACHA->value;

    // ショップ
    case SHOP_ITEM = BaseContentMaintenanceType::SHOP_ITEM->value;

    // パス
    case SHOP_PASS = BaseContentMaintenanceType::SHOP_PASS->value;

    // パック
    case SHOP_PACK = BaseContentMaintenanceType::SHOP_PACK->value;

    public function label(): string
    {
        return match ($this) {
            self::ADVENT_BATTLE => '降臨バトル',
            self::PVP => 'ランクマッチ',
            self::ENHANCE_QUEST => 'コイン獲得クエスト',
            self::GACHA => 'ガシャ',
            // self::SHOP_ITEM => 'ショップ（アイテム）',
            // self::SHOP_PASS => 'ショップ（パス）',
            // self::SHOP_PACK => 'ショップ（パック）',
        };
    }

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->value);
        }
        return $labels;
    }

    public static function getOptions(): Collection
    {
        // $cases = self::cases();
        $cases = [
            self::ADVENT_BATTLE,
            self::PVP,
            self::ENHANCE_QUEST,
            self::GACHA,
        ];
        $options = collect();
        foreach ($cases as $case) {
            $options->put($case->value, $case->label());
        }
        return $options;
    }
}
