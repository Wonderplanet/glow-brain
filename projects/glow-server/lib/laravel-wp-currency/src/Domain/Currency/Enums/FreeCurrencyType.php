<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Enums;

use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * 無償一次通貨の種類
 *
 * CurrencyConstants.phpに定義されているものをEnumとして定義
 */
enum FreeCurrencyType: string
{
    case Ingame = CurrencyConstants::FREE_CURRENCY_TYPE_INGAME;
    case Bonus = CurrencyConstants::FREE_CURRENCY_TYPE_BONUS;
    case Reward = CurrencyConstants::FREE_CURRENCY_TYPE_REWARD;
}
