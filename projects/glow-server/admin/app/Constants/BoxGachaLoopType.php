<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\BoxGacha\Enums\BoxGachaLoopType as ApiBoxGachaLoopType;

/**
 * BOXガシャのループタイプ（admin表示用）
 *
 * ALL: 全てのBOXレベルをループ（1箱目→2箱目→...→N箱目→1箱目→...）
 * LAST: 最後のBOXレベルのみループ（1箱目→2箱目→...→N箱目→N箱目→...）
 * FIRST: 最終BOXを引ききったら1箱目に戻り、以降1箱目をループ
 */
enum BoxGachaLoopType: string
{
    case ALL = ApiBoxGachaLoopType::ALL->value;
    case LAST = ApiBoxGachaLoopType::LAST->value;
    case FIRST = ApiBoxGachaLoopType::FIRST->value;

    /**
     * 表示用ラベルを取得
     */
    public function label(): string
    {
        return match ($this) {
            self::ALL => '全てループ',
            self::LAST => '最後のみループ',
            self::FIRST => '最初に戻る',
        };
    }

    /**
     * 挙動説明付きラベルを取得
     */
    public function labelWithDescription(): string
    {
        return match ($this) {
            self::ALL => '全てループ (1→2→...→N→1→...)',
            self::LAST => '最後のみループ (...→N→N→N)',
            self::FIRST => '最初に戻る (...→N→1→1→1)',
        };
    }

    /**
     * 文字列値からラベルを取得（不明な値はそのまま返す）
     */
    public static function toLabel(string $value): string
    {
        return self::tryFrom($value)?->label() ?? $value;
    }

    /**
     * 文字列値から詳細説明付きラベルを取得（不明な値はそのまま返す）
     */
    public static function toLabelWithDescription(string $value): string
    {
        return self::tryFrom($value)?->labelWithDescription() ?? $value;
    }
}
