<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Stage\Enums\StageRewardCategory;

class StageFirstClearReward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $mstStageId,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value,
                $mstStageId,
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'lapNumber' => 1,
            'rewardCategory' => StageRewardCategory::FIRST_CLEAR->value,
            // 初回クリア報酬にはキャンペーン適用がないのでnullを固定で返す
            'campaignPercentage' => null,
            'reward' => parent::formatToResponse(),
        ];
    }
}
