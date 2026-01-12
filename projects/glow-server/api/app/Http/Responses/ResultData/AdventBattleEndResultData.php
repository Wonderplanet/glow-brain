<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class AdventBattleEndResultData
{
    /**
     * @param \App\Domain\AdventBattle\Models\UsrAdventBattleInterface $usrAdventBattle
     * @param int $allUserTotalScore
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface>  $usrItems 報酬付与があったアイテムのみを含める
     * @param \App\Http\Responses\Data\UserLevelUpData $userLevelUpData
     * @param Collection<\App\Domain\Resource\Entities\Rewards\AdventBattleDropReward> $adventBattleDropRewards
     * @param Collection<\App\Domain\Resource\Entities\Rewards\AdventBattleRankReward> $adventBattleRankRewards
     * @param Collection<\App\Domain\Shop\Models\UsrConditionPackInterface> $usrConditionPacks
     * @param Collection<\App\Domain\Resource\Usr\Entities\UsrEnemyDiscoveryEntity> $newUsrEnemyDiscoveries
     */
    public function __construct(
        public readonly UsrAdventBattleInterface $usrAdventBattle,
        public readonly int $allUserTotalScore,
        public UsrParameterData $usrParameterData,
        public Collection $usrItems,
        public UserLevelUpData $userLevelUpData,
        public Collection $adventBattleAlwaysClearRewards,
        public Collection $adventBattleRandomClearRewards,
        public Collection $adventBattleFirstClearRewards,
        public Collection $adventBattleDropRewards,
        public Collection $adventBattleRankRewards,
        public Collection $usrConditionPacks,
        public Collection $newUsrEnemyDiscoveries,
    ) {
    }
}
