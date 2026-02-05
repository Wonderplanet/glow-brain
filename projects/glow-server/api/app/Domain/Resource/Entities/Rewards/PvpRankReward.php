<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Pvp\Enums\PvpRewardCategory;
// ...existing code...
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\PvpReward;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class PvpRankReward extends PvpReward
{
    private int $expirationDays = MessageConstant::PVP_REWARD_MESSAGE_EXPIRATION_DAYS;
    private string $title = MessageConstant::PVP_RANK_REWARD_TITLE;
    private string $body = MessageConstant::PVP_RANK_REWARD_BODY;

    public function __construct(
        string $resourceType,
        ?string $resourceId,
        int $amount,
        string $sysPvpSeasonId,
        string $rewardGroupId,
    ) {
        $logTriggerDto = new LogTriggerDto(
            LogResourceTriggerSource::PVP_RANK_REWARD->value,
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
            'rewardCategory' => PvpRewardCategory::RANK_ClASS->value,
            'reward' => parent::formatToResponse(),
        ];
    }

    // getRewardGroupIdは親クラスで実装済み

    public function getExpirationDays(): int
    {
        return $this->expirationDays;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
