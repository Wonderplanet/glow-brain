<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Gacha\Enums\GachaType as BaseGachaType;
use Illuminate\Support\Collection;

enum GachaType: string
{
    case TUTORIAL = BaseGachaType::TUTORIAL->value;
    case NORMAL = BaseGachaType::NORMAL->value;
    case PREMIUM = BaseGachaType::PREMIUM->value;
    case PICKUP = BaseGachaType::PICKUP->value;
    case FREE = BaseGachaType::FREE->value;
    case TICKET = BaseGachaType::TICKET->value;
    case FESTIVAL = BaseGachaType::FESTIVAL->value;
    case PAID_ONLY = BaseGachaType::PAID_ONLY->value;
    case MEDAL = BaseGachaType::MEDAL->value;
    case STEPUP = BaseGachaType::STEPUP->value;

    public function label(): string
    {
        return match ($this) {
            self::TUTORIAL => 'チュートリアル',
            self::NORMAL => 'ノーマル',
            self::PREMIUM => 'プレミアム',
            self::PICKUP => 'ピックアップ',
            self::FREE => '無料',
            self::TICKET => 'チケット',
            self::FESTIVAL => 'フェス',
            self::PAID_ONLY => '有償限定',
            self::MEDAL => 'メダル',
            self::STEPUP => 'ステップアップ',
            default => '-',
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
