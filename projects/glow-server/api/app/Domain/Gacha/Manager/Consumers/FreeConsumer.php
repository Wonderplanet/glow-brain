<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Manager\Consumers;

use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Resource\Entities\CurrencyTriggers\GachaTrigger;

class FreeConsumer implements CostConsumerInterface
{
    /**
     * コストが足りているかチェックして消費情報に登録する(※無料なので登録は不要)
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
        // 無料は消費コストなし
        return;
    }

    /**
     * 消費情報で消費を行う(※無料なので消費は不要)
     *
     * @return void
     */
    public function execConsumeResource(ILogGachaAction $logGachaAction): void
    {
        // 無料は消費コストなし
        return;
    }
}
