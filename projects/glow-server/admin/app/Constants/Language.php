<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Common\Enums\Language as BaseLanguage;
use Illuminate\Support\Collection;

enum Language: string
{
    /**
     * 日本語
     */
    case Ja = BaseLanguage::Ja->value;

    public function label(): string
    {
        // 日本人が管理ツールを見る前提なので、日本語で表示する
        return match ($this) {
            self::Ja => '日本語',
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
