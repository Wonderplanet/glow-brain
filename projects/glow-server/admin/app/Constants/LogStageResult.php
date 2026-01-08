<?php

declare(strict_types=1);

namespace App\Constants;
use Illuminate\Support\Collection;

use App\Domain\Stage\Enums\LogStageResult as ApiLogStageResult;

enum LogStageResult: int
{
    case UNDETERMINED = ApiLogStageResult::UNDETERMINED->value;
    case VICTORY = ApiLogStageResult::VICTORY->value;
    case DEFEAT = ApiLogStageResult::DEFEAT->value;
    case RETIRE = ApiLogStageResult::RETIRE->value;
    case CANCEL = ApiLogStageResult::CANCEL->value;
    case NONE = ApiLogStageResult::NONE->value;

    public function label(): string
    {
        return match ($this) {
            self::UNDETERMINED => '結果未確定',
            self::VICTORY => '勝利',
            self::DEFEAT => '敗北',
            self::RETIRE => 'リタイア',
            self::CANCEL => '中断復帰キャンセル',
            self::NONE => '無効なケース',
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
