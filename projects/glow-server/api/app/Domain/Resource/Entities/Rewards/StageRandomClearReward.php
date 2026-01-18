<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Stage\Enums\StageRewardCategory;

class StageRandomClearReward extends BaseReward
{
    private ?int $campaignPercentage;

    private int $lapNumber;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $mstStageId,
        ?int $campaignPercentage = null,
        int $lapNumber = 1,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::STAGE_RANDOM_CLEAR_REWARD->value,
                $mstStageId,
            ),
        );

        $this->campaignPercentage = $campaignPercentage;
        $this->lapNumber = $lapNumber;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'lapNumber' => $this->lapNumber,
            'rewardCategory' => StageRewardCategory::RANDOM->value,
            'campaignPercentage' => $this->campaignPercentage,
            'reward' => parent::formatToResponse(),
        ];
    }
}
