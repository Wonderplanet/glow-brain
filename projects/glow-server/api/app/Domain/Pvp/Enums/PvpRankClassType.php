<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Enums;

use Illuminate\Support\Collection;

enum PvpRankClassType: string
{
    // ブロンズ
    case BRONZE = 'Bronze';
    // シルバー
    case SILVER = 'Silver';
    // ゴールド
    case GOLD = 'Gold';
    // プラチナ
    case PLATINUM = 'Platinum';

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->value);
        }
        return $labels;
    }

    public function order(): int
    {
        return match ($this) {
            self::BRONZE => 1,
            self::SILVER => 2,
            self::GOLD => 3,
            self::PLATINUM => 4,
        };
    }

    /**
     * 指定した数分ランクを降格して取得する
     *
     * @param integer $downCount
     * @return array{0: PvpRankClassType, 1: int}
     */
    public function getLowerWithLevel(int $downCount, int $rankLevel): array
    {
        $current = $this;
        $currentLevel = $rankLevel;
        for ($i = 0; $i < $downCount; $i++) {
            // $currentがBRONZEかつレベル1以下の場合は初期値で返す
            if ($current === self::BRONZE && $currentLevel <= 1) {
                return [self::BRONZE, 0];
            }

            // １つ下のランクに降格し、レベルは一番下のレベルに設定する
            $current = $current->getPrevious() ?? self::BRONZE;
            $currentLevel = 1;
        }
        return [$current, $currentLevel];
    }

    public function getPrevious(): ?self
    {
        return match ($this) {
            self::BRONZE => null,
            self::SILVER => self::BRONZE,
            self::GOLD => self::SILVER,
            self::PLATINUM => self::GOLD,
        };
    }

    public function getNext(): ?self
    {
        return match ($this) {
            self::BRONZE => self::SILVER,
            self::SILVER => self::GOLD,
            self::GOLD => self::PLATINUM,
            self::PLATINUM => null,
        };
    }
}
