<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Outpost\Enums\OutpostEnhancementType as ApiOutpostEnhancementType;

enum OutpostEnhancementType: string
{
    case LEADER_POINT_SPEED = ApiOutpostEnhancementType::LEADER_POINT_SPEED->value;
    case LEADER_POINT_LIMIT = ApiOutpostEnhancementType::LEADER_POINT_LIMIT->value;
    case OUTPOST_HP = ApiOutpostEnhancementType::OUTPOST_HP->value;
    case SUMMON_INTERVAL = ApiOutpostEnhancementType::SUMMON_INTERVAL->value;
    case LEADER_POINT_UP = ApiOutpostEnhancementType::LEADER_POINT_UP->value;
    case RUSH_CHARGE_SPEED = ApiOutpostEnhancementType::RUSH_CHARGE_SPEED->value;

    public function label(): string
    {
        return match ($this) {
            self::LEADER_POINT_SPEED => 'リーダーP加速',
            self::LEADER_POINT_LIMIT => 'リーダーP上昇値',
            self::OUTPOST_HP => 'ゲートHP',
            self::SUMMON_INTERVAL => '再召喚スピード',
            self::LEADER_POINT_UP => 'リーダーP獲得量',
            self::RUSH_CHARGE_SPEED => '総攻撃チャージスピード',
        };
    }
}
