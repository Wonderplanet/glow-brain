<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;
use App\Domain\Unit\Delegators\UnitDelegator;

class UnitSendService implements RewardSendServiceInterface
{
    public function __construct(
        private UnitDelegator $unitDelegator,
    ) {
    }

    public function send(RewardSendContext $context): RewardSent
    {
        $rewards = $context->getRewards();

        // aggregate
        $newMstUnitIds = collect();
        foreach ($rewards as $reward) {
            $newMstUnitIds->push($reward->getResourceId());
            $reward->markAsSent();
        }

        // send
        // ここに到達するユニットは全て新規獲得
        // 重複ありの場合は、beforeSendで他リソースへ変換されている
        $this->unitDelegator->bulkCreate($context->getUsrUserId(), $newMstUnitIds);

        return new RewardSent($rewards);
    }
}
