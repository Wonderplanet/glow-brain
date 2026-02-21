<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\AdventBattle\Enums\AdventBattleClearRewardCategory;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class AdventBattleRandomClearReward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $mstAdventBattleId,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::ADVENT_BATTLE_RANDOM_CLEAR_REWARD->value,
                $mstAdventBattleId,
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'rewardCategory' => AdventBattleClearRewardCategory::RANDOM->value,
            'reward' => parent::formatToResponse(),
        ];
    }
}
