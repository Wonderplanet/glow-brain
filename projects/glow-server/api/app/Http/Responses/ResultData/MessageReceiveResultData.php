<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class MessageReceiveResultData
{
    /**
     * @param Collection<\App\Domain\Resource\Entities\Rewards\MessageReward> $messageRewards
     * @param UsrParameterData $usrUserParameter
     * @param Collection<\App\Domain\Unit\Models\UsrUnitInterface> $usrUnits
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface>  $usrItems
     * @param Collection<\App\Domain\Emblem\Models\UsrEmblemInterface> $usrEmblems
     * @param UserLevelUpData $userLevelUpData
     * @param Collection<\App\Domain\Shop\Models\UsrConditionPackInterface> $usrConditionPacks
     */
    public function __construct(
        public Collection $messageRewards,
        public UsrParameterData $usrUserParameter,
        public Collection $usrUnits,
        public Collection $usrItems,
        public Collection $usrEmblems,
        public UserLevelUpData $userLevelUpData,
        public Collection $usrConditionPacks,
    ) {
    }
}
