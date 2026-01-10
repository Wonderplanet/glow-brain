<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Repositories\Contracts;

use App\Domain\Resource\Entities\Rewards\BaseReward;

interface ILogModelRepositoryRewardSend
{
    public function createByReward(string $usrUserId, BaseReward $reward): void;
}
