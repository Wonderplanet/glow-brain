<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

/**
 * 原画完成報酬
 *
 * 原画のかけらを全て集めて原画が完成した際に付与される報酬
 */
class ArtworkFragmentCompletionReward extends BaseReward
{
    public function __construct(
        string $resourceId,
        int $amount,
    ) {
        parent::__construct(
            RewardType::ARTWORK->value,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::ARTWORK_FRAGMENT_COMPLETION_REWARD->value,
                '',
            ),
        );
    }
}
