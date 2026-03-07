<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

/**
 * ジャンプ+連携報酬 エンティティ
 */
class JumpPlusReward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        private string $mngJumpPlusRewardScheduleId,
        private string $receiveExpireAt,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::JUMP_PLUS_REWARD->value,
                $mngJumpPlusRewardScheduleId,
            ),
        );
    }

    public function getMngJumpPlusRewardScheduleId(): string
    {
        return $this->mngJumpPlusRewardScheduleId;
    }

    public function getReceiveExpireAt(): string
    {
        return $this->receiveExpireAt;
    }
}
