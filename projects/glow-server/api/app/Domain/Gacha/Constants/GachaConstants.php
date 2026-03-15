<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Constants;

use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Resource\Enums\RarityType;

class GachaConstants
{
    /**
     * ガシャタイプ毎に許可されているコストタイプ(引き方)
     */
    public const PERMISSION_GACHA_COST = [
        GachaType::NORMAL->value => [CostType::ITEM->value, CostType::AD->value],
        GachaType::PREMIUM->value => [CostType::ITEM->value, CostType::AD->value, CostType::DIAMOND->value],
        GachaType::PICKUP->value => [CostType::ITEM->value, CostType::AD->value, CostType::DIAMOND->value],
        GachaType::FESTIVAL->value => [CostType::ITEM->value, CostType::AD->value, CostType::DIAMOND->value],
        GachaType::TICKET->value => [CostType::ITEM->value],
        GachaType::FREE->value => [CostType::FREE->value],
        // 有償限定だが、補填のためにチケットで引けるためにitemも許可
        GachaType::PAID_ONLY->value => [CostType::ITEM->value, CostType::PAID_DIAMOND->value],
        GachaType::MEDAL->value => [CostType::ITEM->value],
        GachaType::TUTORIAL->value => [CostType::DIAMOND->value],
        // 有償ダイヤモンドステップや補填チケットでも引けるようにPAID_DIAMONDとITEMも許可
        GachaType::STEPUP->value => [CostType::DIAMOND->value, CostType::PAID_DIAMOND->value, CostType::ITEM->value, CostType::FREE->value],
    ];

    /**
     * 確定枠(最低保証)が適用される最低ガシャ回数
     */
    public const MINIMUM_DRAW_COUNT_FOR_FIXED = 10;

    public const MAX_RARITY = RarityType::UR;

    /**
     * ガシャ履歴の最大保持数
     */
    public const GACHA_HISTORY_LIMIT = 50;

    /**
     * ガシャ履歴の保持期間(日)
     */
    public const HISTORY_DAYS = 7;
}
