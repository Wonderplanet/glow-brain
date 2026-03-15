<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Stage\Enums\StageRuleType as ApiStageRuleType;

enum StageRuleType: string
{
    case OUTPOST_HP = ApiStageRuleType::OUTPOST_HP->value;
    case NO_CONTINUE = ApiStageRuleType::NO_CONTINUE->value;

    public function label(): string
    {
        return match ($this) {
            self::OUTPOST_HP => 'ヒーローゲートのHPが[ルール条件値]で開始',
            self::NO_CONTINUE => 'コンティニュー不可',
        };
    }
}
