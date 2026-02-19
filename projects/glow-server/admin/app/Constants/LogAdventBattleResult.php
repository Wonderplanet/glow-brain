<?php

declare(strict_types=1);

namespace App\Constants;
use Illuminate\Support\Collection;

use App\Domain\AdventBattle\Enums\LogAdventBattleResult as ApiLogAdventBattleResult;

enum LogAdventBattleResult: int
{
    case UNDETERMINED = ApiLogAdventBattleResult::UNDETERMINED->value;
    case VICTORY = ApiLogAdventBattleResult::VICTORY->value;
    case RETIRE = ApiLogAdventBattleResult::RETIRE->value;
    case CANCEL = ApiLogAdventBattleResult::CANCEL->value;
    case NONE = ApiLogAdventBattleResult::NONE->value;

    public function label(): string
    {
        return match ($this) {
            self::UNDETERMINED => '結果未確定',
            self::VICTORY => '勝利',
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
