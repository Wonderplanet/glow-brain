<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

/**
 * 画面遷移先として設定できる遷移先の種別   
 */
enum DestinationType: string
{
    // 遷移先なし
    case NONE = 'None';

    // ゲーム内
    case IN_GAME = 'InGame';

    // 外部サイト
    case WEB = 'Web';

    public function label(): string
    {
        return match ($this) {
            self::NONE => '遷移先なし',
            self::IN_GAME => 'ゲーム内',
            self::WEB => '外部サイト',
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
