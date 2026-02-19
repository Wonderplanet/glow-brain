<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Message\Enums\MessageSource as ApiMessageSource;
use Illuminate\Support\Collection;

enum MessageSource: string
{
    case MNG_MESSAGE = ApiMessageSource::MNG_MESSAGE->value;

    case RESOURCE_LIMIT_REACHED = ApiMessageSource::RESOURCE_LIMIT_REACHED->value;

    public function label(): string
    {
        return match ($this) {
            self::MNG_MESSAGE => '運営メッセージ',
            self::RESOURCE_LIMIT_REACHED => 'リソース上限超過',
            default => 'その他',
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
