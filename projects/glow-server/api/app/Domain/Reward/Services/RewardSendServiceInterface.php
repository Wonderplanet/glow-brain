<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;

interface RewardSendServiceInterface
{
    /**
     * @param RewardSendContext $context
     * @return RewardSent
     */
    public function send(RewardSendContext $context): RewardSent;
}
