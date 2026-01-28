<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;
use App\Domain\Reward\Managers\RewardManagerInterface;
use App\Domain\User\Delegators\UserDelegator;

class ExpSendService implements RewardSendServiceInterface
{
    public function __construct(
        private RewardManagerInterface $rewardManager,
        private UserDelegator $userDelegator,
    ) {
    }

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        $usrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $beforeAmount = $usrUserParameter->getExp();

        // aggregate
        $totalAmount = 0;
        foreach ($rewards as $reward) {
            $totalAmount += $reward->getAmount();

            $reward->setBeforeAmount($beforeAmount);

            $afterAmount = $beforeAmount + $reward->getAmount();
            $reward->setAfterAmount($afterAmount);

            $reward->markAsSent();

            $beforeAmount = $afterAmount;
        }

        // send
        if ($totalAmount <= 0) {
            return new RewardSent($rewards);
        }

        $userLevelUpData = $this->userDelegator->addExp($usrUserId, $totalAmount, $now);

        // after send
        $this->rewardManager->addRewards($userLevelUpData->levelUpRewards);

        return new RewardSent($rewards);
    }
}
