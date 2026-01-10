<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Pvp\Enums\PvpRewardCategory;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class PvpTotalScoreReward extends PvpReward
{
    public function __construct(
        string $resourceType,
        ?string $resourceId,
        int $amount,
        string $sysPvpSeasonId,
        string $rewardGroupId,
    ) {
        $logTriggerDto = new LogTriggerDto(
            LogResourceTriggerSource::PVP_TOTAL_SCORE_REWARD->value,
            $sysPvpSeasonId,
        );
        parent::__construct(
            $resourceType,
            $resourceId,
            $amount,
            $logTriggerDto,
            $rewardGroupId,
            $sysPvpSeasonId,
        );
    }

    public function formatToResponse(): array
    {
        return [
            'rewardCategory' => PvpRewardCategory::TOTAL_SCORE->value,
            'reward' => parent::formatToResponse(),
        ];
    }

    // getRewardGroupIdは親クラスで実装済み

    public function getExpirationDays(): int
    {
        return 0;
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getBody(): string
    {
        return '';
    }
}
