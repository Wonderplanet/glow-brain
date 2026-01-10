<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Entities;

use Illuminate\Support\Collection;

class AdventBattleReceivableReward
{
    /**
     * @param Collection<\App\Domain\Resource\Mst\Entities\MstAdventBattleRewardEntity> $mstAdventBattleRewards
     * @param string|null $latestMstAdventBattleRewardGroupId
     */
    public function __construct(
        private readonly Collection $mstAdventBattleRewards,
        private readonly ?string $latestMstAdventBattleRewardGroupId,
    ) {
    }

    public function getMstAdventBattleRewards(): Collection
    {
        return $this->mstAdventBattleRewards;
    }

    public function getLatestMstAdventBattleRewardGroupId(): ?string
    {
        return $this->latestMstAdventBattleRewardGroupId;
    }
}
