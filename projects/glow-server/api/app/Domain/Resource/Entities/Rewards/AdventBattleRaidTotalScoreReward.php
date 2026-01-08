<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class AdventBattleRaidTotalScoreReward extends BaseReward
{
    private string $title = MessageConstant::ADVENT_BATTLE_TITLE;
    private string $body = MessageConstant::ADVENT_BATTLE_BODY;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        private readonly string $mstAdventBattleId,
        private readonly string $mstAdventBattleRewardGroupId,
        private readonly string $mstAdventBattleRewardId,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::ADVENT_BATTLE_RAID_TOTAL_SCORE_REWARD->value,
                $mstAdventBattleId,
            ),
        );
    }

    public function getMstAdventBattleId(): string
    {
        return $this->mstAdventBattleId;
    }

    public function getAdventBattleRewardCategory(): string
    {
        return AdventBattleRewardCategory::RAID_TOTAL_SCORE->value;
    }

    public function getMstAdventBattleRewardGroupId(): string
    {
        return $this->mstAdventBattleRewardGroupId;
    }

    public function getMstAdventBattleRewardId(): string
    {
        return $this->mstAdventBattleRewardId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }
    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'rewardCategory' => AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
            'reward' => parent::formatToResponse(),
        ];
    }
}
