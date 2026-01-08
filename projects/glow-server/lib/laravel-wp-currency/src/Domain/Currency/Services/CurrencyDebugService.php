<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Services;

use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyInvalidDebugException;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

/**
 * デバッグ機能向けサービス
 *
 * 本番環境ではエラーになるように、各APIは処理に入る前に環境のチェックを行うこと
 */
class CurrencyDebugService
{
    public function __construct(
        private CurrencyService $currencyService,
    ) {
    }

    /**
     * デバッグ機能が使える環境かどうか
     *
     * 実行できない環境の場合は例外が発生する
     *
     * @return void
     * @throws WpCurrencyInvalidDebugException
     */
    private function validateDebugEnvironment()
    {
        if (!CommonUtility::isDebuggableEnvironment()) {
            throw new WpCurrencyInvalidDebugException();
        }
    }

    /**
     * 有償一次通貨を追加する (デバッグ用)
     *
     * currency_summaryの更新も行う
     *
     * デバッグ用のため、追加したcurrency_paidのIDのみ返す
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $amount
     * @param string $currencyCode
     * @param string $price
     * @param integer $vipPoint
     * @param string $receiptUniqueId
     * @param boolean $isSandbox
     * @return string
     */
    public function addCurrencyPaid(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        string $currencyCode,
        string $price,
        int $vipPoint,
        string $receiptUniqueId,
        bool $isSandbox,
        Trigger $trigger,
    ): string {
        // 環境チェック
        $this->validateDebugEnvironment();

        // 有償一次通貨の追加
        $currencyPaid = $this->currencyService->addCurrencyPaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $amount,
            $currencyCode,
            $price,
            $vipPoint,
            $receiptUniqueId,
            $isSandbox,
            $trigger,
        );

        return $currencyPaid->id;
    }
}
