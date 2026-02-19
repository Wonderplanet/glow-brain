<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\BoxGacha\Enums\BoxGachaActionType as ApiBoxGachaActionType;

/**
 * BOXガシャのログタイプ（admin表示用）
 */
enum BoxGachaLogType: string
{
    case DRAW = ApiBoxGachaActionType::DRAW->value;
    case RESET = ApiBoxGachaActionType::RESET->value;

    /**
     * 表示用ラベルを取得
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAW => '抽選 (Draw)',
            self::RESET => 'リセット (Reset)',
        };
    }

    /**
     * バッジカラーを取得
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAW => 'success',
            self::RESET => 'warning',
        };
    }

    /**
     * SelectFilterのオプション用配列を取得
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    /**
     * 文字列値からバッジカラーを取得（不明な値はgrayを返す）
     */
    public static function toBadgeColor(string $value): string
    {
        return self::tryFrom($value)?->badgeColor() ?? 'gray';
    }
}
