<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Delegators;

use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;
use WonderPlanet\Domain\Currency\Services\CurrencyBatchService;

/**
 * コマンド実行からCurrencyを操作する為のDelegator
 */
class CurrencyBatchDelegator
{
    public function __construct(
        private CurrencyBatchService $currencyBatchService
    ) {
    }

    /**
     * 無償一次通貨回収処理
     *  バッチで複数のユーザーから回収したい場合などで使用
     *
     * @param string $userId
     * @param string $type
     * @param int $amount
     * @param string $triggerDetail
     * @return UsrCurrencySummaryEntity
     */
    public function collectFreeCurrencyByBatch(
        string $userId,
        string $type,
        int $amount,
        string $triggerDetail,
    ): UsrCurrencySummaryEntity {
        return $this->currencyBatchService->collectFreeCurrencyByBatch(
            $userId,
            $type,
            $amount,
            $triggerDetail
        );
    }

    /**
     * 無償一次通貨付与処理
     *  バッチで複数のユーザーに付与したい場合などで使用
     *
     * @param string $userId
     * @param string $type
     * @param int $amount
     * @param string $triggerDetail
     * @return UsrCurrencySummaryEntity
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function addFreeCurrencyByBatch(
        string $userId,
        string $type,
        int $amount,
        string $triggerDetail,
    ): UsrCurrencySummaryEntity {
        return $this->currencyBatchService->addFreeCurrencyByBatch(
            $userId,
            $type,
            $amount,
            $triggerDetail
        );
    }
}
