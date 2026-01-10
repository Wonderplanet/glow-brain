<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Manager\Consumers;

use App\Domain\Common\Utils\PlatformUtil;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Resource\Entities\CurrencyTriggers\GachaTrigger;
use WonderPlanet\Domain\Currency\Constants\ErrorCode;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;

class DiamondConsumer implements CostConsumerInterface
{
    private ?string $usrUserId = null;
    private ?int $costNum = null;
    private ?int $platform = null;
    private ?string $billingPlatform = null;
    private ?GachaTrigger $gachaTrigger = null;

    public function __construct(
        private AppCurrencyDelegator $appCurrencyDelegator
    ) {
    }

    /**
     * コストが足りているかチェックして消費情報に登録する
     *
     * @param string $usrUserId
     * @param ?string $costId
     * @param int $costNum
     * @param int $platform
     * @param string $billingPlatform
     * @param bool $checkedAd
     * @param ?GachaTrigger $gachaTrigger
     *
     * @return void
     */
    public function setConsumeResource(
        string $usrUserId,
        ?string $costId,
        int $costNum,
        int $platform,
        string $billingPlatform,
        bool $checkedAd,
        ?GachaTrigger $gachaTrigger
    ): void {
        $currencySummary = $this->appCurrencyDelegator->getCurrencySummary($usrUserId);
        $diamondAmount = $currencySummary->getPlatformTotalAmount(
            PlatformUtil::convertPlatformToBillingPlatform($platform)
        );
        if ($costNum > $diamondAmount) {
            // 足りない場合
            throw new WpCurrencyException(
                "currency summary is not enough. userId: {$usrUserId}, billingPlatform: {$billingPlatform}, " .
                "amount: {$costNum}, beforeAmount: {$diamondAmount}",
                ErrorCode::NOT_ENOUGH_CURRENCY
            );
        }

        $this->usrUserId = $usrUserId;
        $this->costNum = $costNum;
        $this->platform = $platform;
        $this->billingPlatform = $billingPlatform;
        $this->gachaTrigger = $gachaTrigger;
    }

    /**
     * 消費情報で消費を行う
     *
     * @return void
     */
    public function execConsumeResource(ILogGachaAction $logGachaAction): void
    {
        $this->appCurrencyDelegator->consumeDiamond(
            $this->usrUserId,
            $this->costNum,
            $this->platform,
            $this->billingPlatform,
            $this->gachaTrigger,
        );
    }
}
