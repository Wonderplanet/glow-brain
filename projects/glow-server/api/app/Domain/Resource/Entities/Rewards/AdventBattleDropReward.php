<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\AdventBattle\Enums\AdventBattleClearRewardCategory;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class AdventBattleDropReward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        private readonly string $mstAdventBattleId,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::ADVENT_BATTLE_DROP_REWARD->value,
                $mstAdventBattleId,
            ),
        );
    }

    public function getMstAdventBattleId(): string
    {
        return $this->mstAdventBattleId;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'rewardCategory' => AdventBattleClearRewardCategory::DROP->value,
            'reward' => parent::formatToResponse(),
        ];
    }
}
