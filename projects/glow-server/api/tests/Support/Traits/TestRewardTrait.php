<?php

declare(strict_types=1);

namespace Tests\Support\Traits;
use App\Domain\Reward\Services\RewardSendService;
use Carbon\CarbonImmutable;

trait TestRewardTrait
{
    public function sendRewards(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        bool $isSaveAll = true,
    ): void {
        /** @var RewardSendService $rewardSendService */
        $rewardSendService = app(RewardSendService::class);
        $rewardSendService->sendRewards($usrUserId, $platform, $now);

        $isSaveAll && $this->saveAll();
    }
}
