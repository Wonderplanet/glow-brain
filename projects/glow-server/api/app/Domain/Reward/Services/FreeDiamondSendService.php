<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Resource\Entities\CurrencyTriggers\FreeDiamondSendTrigger;
use App\Domain\Resource\Enums\RewardSendMethod;
use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;
use App\Domain\Reward\Traits\RewardSendServiceTrait;

class FreeDiamondSendService implements RewardSendServiceInterface
{
    use RewardSendServiceTrait;

    public function __construct(
        private AppCurrencyDelegator $appCurrencyDelegator,
    ) {
    }

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $platform = $context->getPlatform();
        $rewards = $context->getRewards();
        $sendMethod = $context->getSendMethod();

        // aggregate
        $totalAmount = 0;
        foreach ($rewards as $reward) {
            $totalAmount += $reward->getAmount();
        }

        if ($totalAmount <= 0) {
            // 配布量は0だが、以降の配布処理で対象にならないように送信済みとしてマークしておく
            $this->markRewardsAsSent($rewards);
            return new RewardSent($rewards);
        }

        // send
        $sendCallback = function () use ($usrUserId, $platform, $totalAmount, $rewards) {
            $this->appCurrencyDelegator->addIngameFreeDiamond(
                $usrUserId,
                $platform,
                $totalAmount,
                new FreeDiamondSendTrigger($rewards),
            );
        };
        switch ($sendMethod) {
            case RewardSendMethod::SEND_TO_MESSAGE:
                $this->trySendRewardsOrMarkAsSentToMessage(
                    $rewards,
                    $sendCallback,
                );
                break;
            default:
                $sendCallback();
                break;
        }

        return new RewardSent($rewards);
    }
}
