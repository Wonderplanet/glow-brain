<?php

declare(strict_types=1);

namespace App\Domain\Stage\Enums;

enum LogStageResult: int
{
    // 結果未確定
    case UNDETERMINED = 0;

    // 勝利
    case VICTORY = 1;

    // 敗北
    case DEFEAT = 2;

    // リタイア
    case RETIRE = 3;

    // 中断復帰キャンセル
    case CANCEL = 4;

    /**
     * 無効なケース
     * stage/abortでabortTypeがリクエストパラメータに含まれていない または 無効な値が送られた際のケース
     */
    case NONE = 100;

    public static function getOrDefault(int $enumValue, self $default = self::NONE): self
    {
        return self::tryFrom($enumValue) ?? $default;
    }

    /**
     * stage/abortで送られくるケースかどうか
     * true: 想定内のケース, false: 想定外のケース
     */
    public function isAbortType(): bool
    {
        return in_array($this, [self::DEFEAT, self::RETIRE, self::CANCEL], true);
    }
}
