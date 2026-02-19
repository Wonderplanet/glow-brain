<?php

declare(strict_types=1);

namespace App\Entities;

use App\Models\Mng\MngJumpPlusReward;
use App\Models\Mng\MngJumpPlusRewardSchedule;
use Illuminate\Support\Collection;

class JumpPlusRewardPromotionEntity
{
    private const KEY_MNG_JUMP_PLUS_REWARD_SCHEDULE = 'mngJumpPlusRewardSchedule';
    private const KEY_MNG_JUMP_PLUS_REWARD = 'mngJumpPlusReward';

    /**
     * @param Collection<MngJumpPlusRewardSchedule> $mngJumpPlusRewardSchedules
     * @param Collection<MngJumpPlusReward> $mngJumpPlusRewards
     */
    public function __construct(
        private Collection $mngJumpPlusRewardSchedules,
        private Collection $mngJumpPlusRewards,
    ) {
    }

    public function formatToResponse(): array
    {
        return [
            self::KEY_MNG_JUMP_PLUS_REWARD_SCHEDULE => $this->mngJumpPlusRewardSchedules
                ->map(fn(MngJumpPlusRewardSchedule $schedule) => $schedule->formatToResponse())
                ->values()
                ->all(),
            self::KEY_MNG_JUMP_PLUS_REWARD => $this->mngJumpPlusRewards
                ->map(fn(MngJumpPlusReward $reward) => $reward->formatToResponse())
                ->values()
                ->all(),
        ];
    }

    public static function createFromResponseArray(array $response): self
    {
        $mngJumpPlusRewardSchedules = collect($response[self::KEY_MNG_JUMP_PLUS_REWARD_SCHEDULE] ?? [])
            ->map(fn($item) => MngJumpPlusRewardSchedule::createFromResponseArray($item));

        $mngJumpPlusRewards = collect($response[self::KEY_MNG_JUMP_PLUS_REWARD] ?? [])
            ->map(fn($item) => MngJumpPlusReward::createFromResponseArray($item));

        return new self($mngJumpPlusRewardSchedules, $mngJumpPlusRewards);
    }

    public function isEmpty(): bool
    {
        return $this->mngJumpPlusRewardSchedules->isEmpty();
    }

    /**
     * @return Collection<MngJumpPlusRewardSchedule>
     */
    public function getMngJumpPlusRewardSchedules(): Collection
    {
        return $this->mngJumpPlusRewardSchedules;
    }

    /**
     * @return Collection<MngJumpPlusReward>
     */
    public function getMngJumpPlusRewards(): Collection
    {
        return $this->mngJumpPlusRewards;
    }
}
