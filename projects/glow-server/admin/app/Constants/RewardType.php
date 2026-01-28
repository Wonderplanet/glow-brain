<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Resource\Enums\RewardType as ApiRewardType;
use Illuminate\Support\Collection;

enum RewardType: string
{
    case COIN = ApiRewardType::COIN->value;
    case FREE_DIAMOND = ApiRewardType::FREE_DIAMOND->value;
    case DIAMOND = 'Diamond';
    case STAMINA = ApiRewardType::STAMINA->value;
    case ITEM = ApiRewardType::ITEM->value;
    case EMBLEM = ApiRewardType::EMBLEM->value;
    case EXP = ApiRewardType::EXP->value;
    // NOTE mst_shop_itemsテーブルのみで使用想定なのでAPI側のRewardTypeには定義していない
    case IDLE_COIN = 'IdleCoin';
    case PAID_DIAMOND = 'PaidDiamond';
    case FREE = 'Free';
    case AD = 'Ad';
    case UNIT = ApiRewardType::UNIT->value;

    public function label(): string
    {
        return match ($this) {
            self::COIN => 'コイン',
            self::FREE_DIAMOND => '無償プリズム',
            self::DIAMOND => 'プリズム(有償無償合算)',
            self::STAMINA => 'スタミナ',
            self::ITEM => 'アイテム',
            self::EMBLEM => 'エンブレム',
            self::EXP => '経験値',
            self::IDLE_COIN => '探索連動コイン',
            self::PAID_DIAMOND => '有償プリズム',
            self::FREE => '無料',
            self::AD => '広告視聴',
            self::UNIT => 'キャラ',
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

    /**
     * resource_idの指定が必須のタイプかどうか
     * true: 必須, false: 不要
     */
    public function hasResourceId(): bool
    {
        $baseEnum = ApiRewardType::tryFrom($this->value);
        if ($baseEnum === null) {
            return false;
        }
        return $baseEnum->hasResourceId();
    }
}
