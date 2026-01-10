<?php

declare(strict_types=1);

namespace App\Constants;

enum CountryCode: string
{
    case US = 'US';
    case JP = 'JP';
    case EU = 'EU';
    case GB = 'GB';
    case AU = 'AU';
    case CA = 'CA';
    case CH = 'CH';
    case CN = 'CN';
    case IN = 'IN';

    public function label(): string
    {
        return match ($this) {
            self::US => 'アメリカ',
            self::JP => '日本',
            self::EU => '欧州連合',
            self::GB => 'グレートブリテン及び北アイルランド連合王国',
            self::AU => 'オーストラリア',
            self::CA => 'カナダ',
            self::CH => 'スイス',
            self::CN => '中国',
            self::IN => 'インド',
        };
    }
}
