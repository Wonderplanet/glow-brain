<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * group_id単位でまとめられたMngJumpPlusRewardScheduleに関連するEntityを束ねるクラス
 */
class MngJumpPlusRewardBundle
{
    /**
     * @param Collection<MngJumpPlusRewardEntity> $mngJumpPlusRewards
     */
    public function __construct(
        private MngJumpPlusRewardScheduleEntity $mngJumpPlusRewardSchedule,
        private Collection $mngJumpPlusRewards,
    ) {
    }

    public function getMngJumpPlusRewardSchedule(): MngJumpPlusRewardScheduleEntity
    {
        return $this->mngJumpPlusRewardSchedule;
    }

    /**
     * @return Collection<MngJumpPlusRewardEntity>
     */
    public function getMngJumpPlusRewards(): Collection
    {
        return $this->mngJumpPlusRewards;
    }

    public function isActive(CarbonImmutable $now): bool
    {
        return $this->mngJumpPlusRewardSchedule->isActive($now);
    }
}
