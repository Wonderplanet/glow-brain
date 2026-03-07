<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Manager\Consumers;

use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Resource\Entities\CurrencyTriggers\GachaTrigger;

class AdConsumer implements CostConsumerInterface
{
    /**
     * コストが足りているかチェックして消費情報に登録する(※広告なので登録は不要)
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
        // 広告は消費コストなし
        return;
    }

    /**
     * 消費情報で消費を行う(※広告なので消費は不要)
     *
     * @return void
     */
    public function execConsumeResource(ILogGachaAction $logGachaAction): void
    {
        // 広告は消費コストなし
        return;
    }
}
