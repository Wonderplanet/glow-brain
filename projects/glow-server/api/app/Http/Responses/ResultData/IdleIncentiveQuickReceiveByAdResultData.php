<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\IdleIncentive\Models\UsrIdleIncentiveInterface;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class IdleIncentiveQuickReceiveByAdResultData
{
    /**
     * @param Collection<\App\Domain\Resource\Entities\Rewards\IdleIncentiveReward> $idleIncentiveRewards
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface>  $usrItems 報酬付与があったアイテムのみを含める
     * @param Collection<\App\Domain\Shop\Models\UsrConditionPackInterface> $usrConditionPacks
     */
    public function __construct(
        public Collection $idleIncentiveRewards,
        public UserLevelUpData $userLevelUpData,
        public UsrIdleIncentiveInterface $usrIdleIncentive,
        public UsrParameterData $usrUserParameter,
        public Collection $usrItems,
        public Collection $usrConditionPacks,
    ) {
    }
}
