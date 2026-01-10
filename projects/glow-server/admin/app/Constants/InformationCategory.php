<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum InformationCategory: string
{
    /**
     * cssのクラス名に使うため、小文字始まりで定義
     */

    // ガシャ
    case GACHA = 'gacha';

    // キャンペーン
    case CAMPAIGN = 'campaign';

    // イベント
    case EVENT = 'event';

    // その他
    case OTHER = 'other';

    // 不具合
    case BUG = 'bug';

    // 重要
    case IMPORTANT = 'important';

    // インフォメーション
    // お知らせ機能の命名と被らないように、INFORMATIONとはせず、INFOとしています
    case INFO = 'info';

    public function label(): string
    {
        return match ($this) {
            self::GACHA => 'ガシャ',
            self::CAMPAIGN => 'キャンペーン',
            self::EVENT => 'イベント',
            self::OTHER => 'その他',
            self::BUG => '不具合',
            self::IMPORTANT => '重要',
            self::INFO => 'インフォメーション',
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
