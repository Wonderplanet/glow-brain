<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Facades;

use WonderPlanet\Domain\Common\Facades\BaseFacade;

// phpcs:disable -- コメントが120行を超えてしまうため無視
/**
 * 通貨処理のFacade
 * 
 * @method static \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity createUser(string $userId, string $osPlatform, string $billingPlatform, int $freeAmount)
 * @method static \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity usePaid(string $userId, string $osPlatform, string $billingPlatform, int $amount, \WonderPlanet\Domain\Currency\Entities\Trigger $trigger)
 * @method static \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity useCurrency(string $userId, string $osPlatform, string $billingPlatform, int $amount, \WonderPlanet\Domain\Currency\Entities\Trigger $trigger)
 * @method static \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity addFree(string $userId, string $osPlatform,int $amount, string $type, \WonderPlanet\Domain\Currency\Entities\Trigger $trigger)
 * @method static \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity addFrees(string $userId, string $osPlatform, array<\WonderPlanet\Domain\Currency\Entities\FreeCurrencyAddEntity> $freeCurrencyAddEntities)
 * @method static \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity|null getCurrnecySummary(string $userId)
 * @method static array<\WonderPlanet\Domain\Currency\Entities\UsrCurrencyPaidEntity> getCurrencyPaid(string $userId, string $billingPlatform)
 * @method static void softDeleteCurrencyAndBillingDataByUserId(string $userId, string $loggingOsPlatform)
 * @method static int getMaxOwnedCurrencyAmount()
 * @method static bool isMaxOwnedCurrencyAmountUnlimited()
 * 
 * @see \WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator
 */
// phpcs:enable
class Currency extends BaseFacade
{
    public const FACADE_ACCESSOR = 'wp-facade-currency';
}
