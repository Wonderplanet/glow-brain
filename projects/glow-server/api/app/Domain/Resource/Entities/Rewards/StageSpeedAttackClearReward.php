<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Stage\Enums\StageRewardCategory;

class StageSpeedAttackClearReward extends BaseReward
{
    private string $stageRewardCategory;
    private string $mstStageId;
    private string $mstStageClearTimeRewardTableId;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $mstStageId,
        string $mstStageClearTimeRewardTableId
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value,
                $mstStageId,
            )
        );

        $this->stageRewardCategory = StageRewardCategory::SPEED_ATTACK_CLEAR->value;
        $this->mstStageId = $mstStageId;
        $this->mstStageClearTimeRewardTableId = $mstStageClearTimeRewardTableId;
    }

    public function getStageRewardCategory(): string
    {
        return $this->stageRewardCategory;
    }

    public function getMstStageId(): string
    {
        return $this->mstStageId;
    }

    public function getMstStageClearTimeRewardTableId(): string
    {
        return $this->mstStageClearTimeRewardTableId;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'lapNumber' => 1,
            'rewardCategory' => $this->getStageRewardCategory(),
            // スピードクリア報酬にはキャンペーン適用がないのでnullを固定で返す
            'campaignPercentage' => null,
            'reward' => parent::formatToResponse(),
        ];
    }

    /**
     * @return array<mixed>
     */
    public function getActionDetailLog(): array
    {
        return [
            'reward_category' => $this->getStageRewardCategory(),
            'mst_stages.id' => $this->getMstStageId(),
            'mst_stage_clear_time_rewards.id' => $this->getMstStageClearTimeRewardTableId(),
        ];
    }
}
