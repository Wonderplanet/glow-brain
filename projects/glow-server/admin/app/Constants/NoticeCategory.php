<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum NoticeCategory: string
{
    // enum値がS3バケット内のフォルダ名になります

    // IGN：：インゲームノーティス
    case IGN = 'ign';

    case INFORMATION = 'information';

    case GACHA_CAUTION = 'gacha_caution';

    public function label(): string
    {
        return match ($this) {
            self::IGN => 'IGN',
            self::INFORMATION => 'お知らせ',
            self::GACHA_CAUTION => 'ガシャ注意事項',
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
