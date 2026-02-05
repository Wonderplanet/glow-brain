<?php

namespace App\Constants;

use Illuminate\Support\Collection;

enum OsType: string
{
    case ALL = 'All';
    case IOS = 'iOS';
    case ANDROID = 'Android';
    case OTHERS = 'others';
    case UNKNOWN = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::ALL => '全て',
            self::IOS => 'iOS',
            self::ANDROID => 'Android',
            self::OTHERS => 'その他',
            self::UNKNOWN => '不明',

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

    public static function getFormSelectOptions(): Collection
    {
        return collect([
            self::ALL->value => self::ALL->label(),
            self::IOS->value => self::IOS->label(),
            self::ANDROID->value => self::ANDROID->label(),
        ]);
    }
}
