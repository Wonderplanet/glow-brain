<?php

declare(strict_types=1);

namespace App\Domain\Reward\Entities;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\RewardSendMethod;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class RewardSendContext
{
    private string $usrUserId;

    private int $platform;

    /**
     * @var Collection<BaseReward>
     */
    private Collection $rewards;

    private CarbonImmutable $now;

    private RewardSendMethod $sendMethod;

    /**
     * @param Collection<BaseReward> $rewards 報酬のコレクション
     */
    public function __construct(
        string $usrUserId,
        int $platform,
        Collection $rewards,
        CarbonImmutable $now,
        RewardSendMethod $sendMethod,
    ) {
        $this->usrUserId = $usrUserId;
        $this->platform = $platform;
        $this->rewards = $rewards;
        $this->now = $now;
        $this->sendMethod = $sendMethod;
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getPlatform(): int
    {
        return $this->platform;
    }

    /**
     * @return Collection<BaseReward>
     */
    public function getRewards(): Collection
    {
        return $this->rewards;
    }

    public function getNow(): CarbonImmutable
    {
        return $this->now;
    }

    public function getSendMethod(): RewardSendMethod
    {
        return $this->sendMethod;
    }
}
