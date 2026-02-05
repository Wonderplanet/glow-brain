<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UserLevelUpData;
use Illuminate\Support\Collection;

class StageEndResultData
{
    /**
     * @param Collection<\App\Domain\Resource\Entities\Rewards\StageAlwaysClearReward> $stageAlwaysClearRewards
     * @param Collection<\App\Domain\Resource\Entities\Rewards\StageRandomClearReward> $stageRandomClearRewards
     * @param Collection<\App\Domain\Resource\Entities\Rewards\StageFirstClearReward> $stageFirstClearRewards
     * @param Collection<\App\Domain\Resource\Entities\Rewards\StageSpeedAttackClearReward> $stageSpeedAttackClearRewards
     * @param Collection<\App\Domain\Resource\Usr\Entities\UsrConditionPackEntity> $usrConditionPacks
     * @param Collection<\App\Domain\Encyclopedia\Models\UsrArtworkInterface> $usrArtworks
     * @param Collection<\App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface> $usrArtworkFragments
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface>  $usrItems
     * @param Collection<\App\Domain\Unit\Models\UsrUnitInterface>  $usrUnits
     * @param Collection<\App\Domain\Resource\Usr\Entities\UsrEnemyDiscoveryEntity> $newUsrEnemyDiscoveries
     * @param Collection<string> $oprCampaignIds
     */
    public function __construct(
        public UserLevelUpData $userLevelUpData,
        public Collection $stageAlwaysClearRewards,
        public Collection $stageRandomClearRewards,
        public Collection $stageFirstClearRewards,
        public Collection $stageSpeedAttackClearRewards,
        public Collection $usrConditionPacks,
        public Collection $usrArtworks,
        public Collection $usrArtworkFragments,
        public Collection $usrItems,
        public Collection $usrUnits,
        public Collection $newUsrEnemyDiscoveries,
        public Collection $oprCampaignIds,
    ) {
    }
}
