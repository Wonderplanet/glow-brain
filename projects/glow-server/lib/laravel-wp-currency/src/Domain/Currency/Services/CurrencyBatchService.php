<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Services;

use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\AddFreeCurrencyBatchTrigger;
use WonderPlanet\Domain\Currency\Entities\CollectFreeCurrencyBatchTrigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;

/**
 * コマンドで実行する通貨関連のサービス
 */
class CurrencyBatchService
{
    public function __construct(
        private CurrencyAdminService $currencyAdminService,
        private CurrencyService $currencyService,
    ) {
    }

    /**
     * 無償一次通貨回収を実行
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
        // バッチ操作であることがわかるように、ログに記録するosPlatformはBatchで固定
        $osPlatform = CurrencyConstants::OS_PLATFORM_BATCH;

        // Triggerの作成
        $trigger = new CollectFreeCurrencyBatchTrigger(
            $triggerDetail,
        );

        // 無償通貨の消費
        return $this->currencyService->useFree(
            $userId,
            $osPlatform,
            $type,
            $amount,
            $trigger,
        );
    }

    /**
     * 無償一次通貨付与を実行
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
        // バッチ操作であることがわかるように、ログに記録するOsPlatformはBatchで固定
        $osPlatform = CurrencyConstants::OS_PLATFORM_BATCH;

        // バッチ操作を記録するTrigger作成
        $trigger = new AddFreeCurrencyBatchTrigger(
            $triggerDetail
        );

        return $this->currencyAdminService
            ->addCurrencyFree(
                $userId,
                $osPlatform,
                $amount,
                $type,
                $trigger
            );
    }
}
