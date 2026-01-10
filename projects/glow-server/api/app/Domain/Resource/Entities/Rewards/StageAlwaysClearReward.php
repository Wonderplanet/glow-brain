<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Stage\Enums\StageRewardCategory;

class StageAlwaysClearReward extends BaseReward
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
                LogResourceTriggerSource::STAGE_ALWAYS_CLEAR_REWARD->value,
                $mstStageId,
            ),
        );

        $this->campaignPercentage = $campaignPercentage;
        $this->lapNumber = $lapNumber;
    }

    /**
     * @return int
     */
    public function getLapNumber(): int
    {
        return $this->lapNumber;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'lapNumber' => $this->lapNumber,
            'rewardCategory' => StageRewardCategory::ALWAYS->value,
            // 初回クリア報酬にはキャンペーン適用がないのでnullを固定で返す
            'campaignPercentage' => $this->campaignPercentage,
            'reward' => parent::formatToResponse(),
        ];
    }
}
