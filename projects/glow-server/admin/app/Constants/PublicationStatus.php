<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum PublicationStatus: string
{
    case PRIVATE = 'Private';
    case BEFORE_PUB = 'BeforePub';
    case ANNOUNCING = 'Announcing';
    case PUBLISHING = 'Publishing';
    case POST_PUB = 'PostPub';
    case ENDED = 'Ended';

    public function label(): string
    {
        return match ($this) {
            self::PRIVATE => '非公開',
            self::BEFORE_PUB => '掲載前',
            self::ANNOUNCING => '告知中',
            self::PUBLISHING => '掲載中',
            self::POST_PUB => '開催終了後掲載中',
            self::ENDED => '掲載終了',
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

    public function badge(): string
    {
        return match ($this) {
            self::PRIVATE => 'gray',
            self::BEFORE_PUB => 'gray',
            self::ANNOUNCING => 'primary',
            self::PUBLISHING => 'info',
            self::POST_PUB => 'success',
            self::ENDED => 'danger',
            default => 'gray',
        };
    }
}
