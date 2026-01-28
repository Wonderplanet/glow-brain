<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Campaign\Enums\CampaignType as ApiCampaignType;
use Illuminate\Support\Collection;

enum CampaignType: string
{
    case STAMINA = ApiCampaignType::STAMINA->value;
    case EXP = ApiCampaignType::EXP->value;
    case ARTWORK_FRAGMENT = ApiCampaignType::ARTWORK_FRAGMENT->value;
    case ITEM_DROP = ApiCampaignType::ITEM_DROP->value;
    case COIN_DROP = ApiCampaignType::COIN_DROP->value;
    case CHALLENGE_COUNT = ApiCampaignType::CHALLENGE_COUNT->value;

    public function label(): string
    {
        return match ($this) {
            self::STAMINA => 'スタミナ消費量調整',
            self::EXP => '獲得リーダーEXP調整',
            self::ARTWORK_FRAGMENT => '原画のかけらドロップ確率調整',
            self::ITEM_DROP => 'アイテムドロップ量調整',
            self::COIN_DROP => 'コインドロップ量調整',
            self::CHALLENGE_COUNT => '挑戦回数変更',
        };
    }

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->value);
        }
        return $labels;
    }
}
