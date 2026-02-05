<?php

declare(strict_types=1);

namespace App\Constants;

enum CurrencyCode: string
{
    case USD = 'US';
    case JPY = 'JP';
    case EUR = 'EU';
    case GBP = 'GB';
    case AUD = 'AU';
    case CAD = 'CA';
    case CHF = 'CH';
    case CNY = 'CN';
    case INR = 'IN';

    /**
     * 通貨コードから国コードを取得
     *
     * @param string $currencyCode
     * @return string
     */
    public static function getCountryCode(string $currencyCode): string
    {
        foreach (self::cases() as $case) {
            if ($case->name === $currencyCode) {
                return $case->value;
            }
        }

        return '';
    }
}
