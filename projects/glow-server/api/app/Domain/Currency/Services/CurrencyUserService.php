<?php

declare(strict_types=1);

namespace App\Domain\Currency\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\PlatformUtil;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;

/**
 * ユーザーのダイヤ(一次通貨)管理を行うサービス
 */
readonly class CurrencyUserService
{
    public function __construct(
        private readonly CurrencyDelegator $currencyDelegator,
    ) {
    }

    /**
     * ユーザーのダイヤ情報を取得する
     *
     * @param string $userId
     * @return UsrCurrencySummaryEntity
     */
    public function getCurrencySummary(string $userId): UsrCurrencySummaryEntity
    {
        return $this->currencyDelegator->getCurrencySummary($userId);
    }

    /**
     * 無償ダイヤを取得する
     *
     * @param string $userId
     * @return integer
     */
    public function getFreeDiamond(string $userId): int
    {
        $summary = $this->currencyDelegator->getCurrencySummary($userId);

        return $summary->getFreeAmount();
    }

    /**
     * ゲーム内で獲得した無償ダイヤを登録する
     *
     * ミッション、プレゼントなどでの獲得時に使用する
     * 課金時および広告リワードなどのダイヤでは使用しない
     *
     * @param string $userId
     * @param integer $platform
     * @param integer $amount
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     */
    public function addIngameFreeDiamond(
        string $userId,
        int $platform,
        int $amount,
        Trigger $trigger,
    ): UsrCurrencySummaryEntity {
        // 課金基盤向けOSプラットフォームを取得する
        $osPlatform = PlatformUtil::convertPlatformToCurrencyOsPlatform($platform);

        return $this->currencyDelegator->addFree(
            $userId,
            $osPlatform,
            $amount,
            CurrencyConstants::FREE_CURRENCY_TYPE_INGAME,
            $trigger,
        );
    }

    /**
     * 必要な量のダイヤを持っているかをチェックする（不足時は例外を投げる）
     *
     * @param string $userId
     * @param integer $diamond
     * @param integer $platform
     * @param string $billingPlatform
     *
     * @throws GameException
     */
    public function validateDiamond(string $userId, int $diamond, int $platform, string $billingPlatform): void
    {
        $result = $this->isEnoughDiamondAmount($userId, $diamond, $platform, $billingPlatform);

        if (! $result) {
            throw new GameException(ErrorCode::LACK_OF_RESOURCES);
        }
    }

    /**
     * 必要な量のダイヤを持っているかをチェックする（チェックのみで例外は投げない）
     *
     * @param string $userId
     * @param int $diamond
     * @param int $platform
     * @param string $billingPlatform
     *
     * @return bool
     */
    public function isEnoughDiamondAmount(string $userId, int $diamond, int $platform, string $billingPlatform): bool
    {
        $summary = $this->currencyDelegator->getCurrencySummary($userId);
        $platformAmount = $summary->getPlatformTotalAmount($billingPlatform);
        if ($platformAmount < $diamond) {
            return false;
        }

        return true;
    }

    /**
     * ユーザーのダイヤを消費する
     *
     * 無償ダイヤから消費し、不足していたらプラットフォームの有償ダイヤを消費する
     *
     * @param string $usrUserId
     * @param integer $consumedDiamond
     * @param integer $platform
     * @param string $billingPlatform
     * @param Trigger $trigger 消費理由のトリガー
     * @return UsrCurrencySummaryEntity
     */
    public function consumeDiamond(
        string $usrUserId,
        int $consumedDiamond,
        int $platform,
        string $billingPlatform,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        $osPlatform = PlatformUtil::convertPlatformToCurrencyOsPlatform($platform);

        return $this->currencyDelegator->useCurrency(
            $usrUserId,
            $osPlatform,
            $billingPlatform,
            $consumedDiamond,
            $trigger,
        );
    }

    /**
     * 必要な量の有償ダイヤを持っているかをチェックする（不足時はエラーを投げる）
     *
     * @param string $userId
     * @param integer $paiDdiamond
     *
     * @throws GameException
     */
    public function validatePaidDiamond(string $userId, int $paiDdiamond): void
    {
        $result = $this->isEnoughPaidDiamond($userId, $paiDdiamond);
        if (! $result) {
            throw new GameException(ErrorCode::LACK_OF_RESOURCES);
        }
    }

    /**
     * 必要な量の有償ダイヤを持っているかをチェックする（チェックのみで例外は投げない）
     *
     * @param string $userId
     * @param int $paiDdiamond
     *
     * @return bool
     */
    public function isEnoughPaidDiamond(string $userId, int $paiDdiamond): bool
    {
        $summary = $this->currencyDelegator->getCurrencySummary($userId);
        if ($summary->getTotalPaidAmount() < $paiDdiamond) {
            return false;
        }

        return true;
    }

    /**
     * ユーザーの有償ダイヤを消費する
     *
     * @param string $usrUserId
     * @param integer $consumedPaidDiamond
     * @param integer $platform
     * @param string $billingPlatform
     * @param Trigger $trigger 消費理由のトリガー
     * @return UsrCurrencySummaryEntity
     */
    public function consumePaidDiamond(
        string $usrUserId,
        int $consumedPaidDiamond,
        int $platform,
        string $billingPlatform,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        $osPlatform = PlatformUtil::convertPlatformToCurrencyOsPlatform($platform);

        return $this->currencyDelegator->usePaid(
            $usrUserId,
            $osPlatform,
            $billingPlatform,
            $consumedPaidDiamond,
            $trigger,
        );
    }

    public function validateAddCurrency(string $userId, int $addPaidAmount, int $addFreeAmount): void
    {
        $this->currencyDelegator->validateAddCurrency($userId, $addPaidAmount, $addFreeAmount);
    }
}
