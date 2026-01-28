<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Delegators;

use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

/**
 * 課金・通貨基盤で通貨関連の処理を扱うDelegator
 *
 * 課金・通貨基盤ライブラリ外からはこのDelegetorを使用してください。
 */
class CurrencyDelegator
{
    public function __construct(
        private CurrencyService $currencyService,
    ) {
    }

    /**
     * 課金・通貨基盤の初期データ登録
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $freeAmount
     * @return UsrCurrencySummaryEntity
     */
    public function createUser(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $freeAmount,
    ): UsrCurrencySummaryEntity {
        // 初期登録
        return $this->currencyService->createUser($userId, $osPlatform, $billingPlatform, $freeAmount);
    }

    /**
     * 有償一次通貨を消費する
     *
     * 消費優先順について
     * - 有償一次通貨は古いものから消費される
     *
     * 詳細は次のURLを参照してください。
     * https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/106135583
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $amount
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     * @throws \Wonderplanet\Domain\Currency\Exceptions\WpCurrencyException
     */
    public function usePaid(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        return $this->currencyService->usePaid($userId, $osPlatform, $billingPlatform, $amount, $trigger);
    }

    /**
     * 有償・無償一次通貨を合算で消費する
     *
     * 消費優先順について
     * - 無償一次通貨がある場合、無償一次通貨から消費される
     * - 無償一次通貨はingame, bonus, rewardの順で消費される
     * - 有償一次通貨は古いものから消費される
     *
     * 詳細は次のURLを参照してください。
     * https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/106135583
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $amount
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     * @throws \Wonderplanet\Domain\Currency\Exceptions\WpCurrencyException
     */
    public function useCurrency(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        return $this->currencyService->useCurrency($userId, $osPlatform, $billingPlatform, $amount, $trigger);
    }

    /**
     * 無償一次通貨を加算する
     *
     * ※bonusは課金アイテム購入時に有償一次通貨と同時に基盤側で加算されるものを想定しています。
     *   プロダクトによっては有償一次通貨専用の消費でも使用できるようにする可能性があるため、注意すること
     *
     * @param string $userId
     * @param string $osPlatform
     * @param integer $amount
     * @param string $type ingame, bonus, rewardのいずれか (CurrencyConstantsに定義あり)
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function addFree(
        string $userId,
        string $osPlatform,
        int $amount,
        string $type,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        $usrCurrencySummary = $this->currencyService->addFree($userId, $osPlatform, $amount, $type, $trigger);

        return $usrCurrencySummary;
    }

    /**
     * 無償一次通貨を複数登録する
     *
     * 登録に必要なぶんだけFreeCurrencyAddEntityオブジェクトを作成して配列として渡す。
     * 各typeのオブジェクトを作成するときは、FreeCurrencyAddEntity::fromType()が使用可能。
     *
     * @param string $userId
     * @param string $osPlatform
     * @param array<\WonderPlanet\Domain\Currency\Entities\FreeCurrencyAddEntity> $freeCurrencyAddEntities
     * @return UsrCurrencySummaryEntity
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function addFrees(
        string $userId,
        string $osPlatform,
        array $freeCurrencyAddEntities,
    ): UsrCurrencySummaryEntity {
        return $this->currencyService->addFrees($userId, $osPlatform, $freeCurrencyAddEntities);
    }

    /**
     * 所持通貨情報を取得する
     *
     * @param string $userId
     * @return UsrCurrencySummaryEntity|null
     */
    public function getCurrencySummary(string $userId): ?UsrCurrencySummaryEntity
    {
        return $this->currencyService->getCurrencySummary($userId);
    }

    /**
     * ユーザーの有償一次通貨の所持内訳を取得する
     *
     * left_amount=0のレコードは消費済みのため返却しない。
     * left_amountがマイナスの場合はマイナス値として計算されているため、返却する。
     *
     * @param string $userId
     * @param string $billingPlatform
     * @return array<\WonderPlanet\Domain\Currency\Entities\UsrCurrencyPaidEntity>
     */
    public function getCurrencyPaid(string $userId, string $billingPlatform): array
    {
        return $this->currencyService->getCurrencyPaid($userId, $billingPlatform);
    }

    /**
     * ユーザーの課金・通貨関連データを論理削除する
     *
     * 削除する際に、残高はすべて0に更新する。
     * 残高が0に補正されたことを各種ログに残している。
     *
     * @param string $userId
     * @param string $loggingOsPlatform
     * @return void
     */
    public function softDeleteCurrencyAndBillingDataByUserId(string $userId, string $loggingOsPlatform): void
    {
        $this->currencyService->softDeleteCurrencyAndBillingDataByUserId($userId, $loggingOsPlatform);
    }

    /**
     * 一次通貨の所持数上限を取得する
     *
     * @return integer
     */
    public function getMaxOwnedCurrencyAmount(): int
    {
        return $this->currencyService->getMaxOwnedCurrencyAmount();
    }

    /**
     * 無償一次通貨の上限所持数を取得する
     *
     * @return integer
     */
    public function getMaxOwnedCurrencyFreeAmount(): int
    {
        return $this->currencyService->getMaxOwnedCurrencyFreeAmount();
    }

    /**
     * 一次通貨の所持数上限が無制限かどうかを取得する
     *
     * @return boolean
     */
    public function isMaxOwnedCurrencyAmountUnlimited(): bool
    {
        return $this->currencyService->isMaxOwnedCurrencyAmountUnlimited();
    }

    public function validateAddCurrency(string $userId, int $addPaidAmount, int $addFreeAmount): void
    {
        $this->currencyService->validateAddCurrency($userId, $addPaidAmount, $addFreeAmount);
    }
}
