<?php
declare(strict_types=1);

namespace App\Http\ResponseFactories;

use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;

trait CurrencySummaryResponderTrait
{
    /**
     * currency_summaryで返すレスポンスを共通化する
     *
     * $platformによってpaid_amountの値を変える
     *
     * @param string $billingPlatform
     * @param UsrCurrencySummaryEntity|null $usrCurrencySummary
     * @return array{paid_amount_apple: int, paid_amount_google: int, paid_amount: int, free_amount: int, cash: int}
     */
    private function createCurrencySummaryResponse(
        string $billingPlatform,
        ?UsrCurrencySummaryEntity $usrCurrencySummary
    ): array {
        if (is_null($usrCurrencySummary)) {
            return [
                'paid_amount_apple' => 0,
                'paid_amount_google' => 0,
                'paid_amount' => 0,
                'free_amount' => 0,
                'cash' => 0,
            ];
        }

        $paidAmount = 0;
        switch ($billingPlatform) {
            case CurrencyConstants::PLATFORM_APPSTORE:
                $paidAmount = $usrCurrencySummary->paid_amount_apple;
                break;
            case CurrencyConstants::PLATFORM_GOOGLEPLAY:
                $paidAmount = $usrCurrencySummary->paid_amount_google;
                break;
            default:
                $paidAmount = 0;
                break;
        }

        $result = [
            'paid_amount_apple' => $usrCurrencySummary->paid_amount_apple,
            'paid_amount_google' => $usrCurrencySummary->paid_amount_google,
            'paid_amount' => $paidAmount,
            'free_amount' => $usrCurrencySummary->free_amount,
            // クライアント側でレスポンスからcashを消した時に削除する
            'cash' => 0,
        ];

        return $result;
    }
}
