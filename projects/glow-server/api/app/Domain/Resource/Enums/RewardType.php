<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

enum RewardType: string
{
    case COIN = 'Coin';
    case FREE_DIAMOND = 'FreeDiamond';
    case STAMINA = 'Stamina';
    case ITEM = 'Item';
    case EMBLEM = 'Emblem';
    case EXP = 'Exp';
    case UNIT = 'Unit';
    case PAID_DIAMOND = 'PaidDiamond';
    case ARTWORK = 'Artwork';

    public function label(): string
    {
        return match ($this) {
            self::COIN => 'コイン',
            self::FREE_DIAMOND => '無償プリズム',
            self::STAMINA => 'スタミナ',
            self::ITEM => 'アイテム',
            self::EMBLEM => 'エンブレム',
            self::EXP => '経験値',
            self::UNIT => 'キャラ',
            self::PAID_DIAMOND => '有償プリズム',
            self::ARTWORK => '原画',
        };
    }

    /**
     * resource_idの指定が必須のタイプかどうか
     * true: 必須, false: 不要
     */
    public function hasResourceId(): bool
    {
        return match ($this) {
            self::COIN => false,
            self::FREE_DIAMOND => false,
            self::STAMINA => false,
            self::ITEM => true,
            self::EMBLEM => true,
            self::EXP => false,
            self::UNIT => true,
            self::PAID_DIAMOND => false,
            self::ARTWORK => true,
        };
    }
}
