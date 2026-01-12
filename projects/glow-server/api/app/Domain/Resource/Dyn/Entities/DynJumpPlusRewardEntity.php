<?php

declare(strict_types=1);

namespace App\Domain\Resource\Dyn\Entities;

use App\Domain\JumpPlus\Enums\DynJumpPlusRewardStatus;

class DynJumpPlusRewardEntity
{
    private string $bnUserId;
    private string $mngJumpPlusRewardScheduleId;
    private int $status;

    /**
     * @param array<mixed> $dynamoDbItem
     */
    public function __construct(
        array $dynamoDbItem,
    ) {
        // partition key
        $this->bnUserId = $dynamoDbItem['bn_user_id']['S'];

        // sort key
        $this->mngJumpPlusRewardScheduleId = $dynamoDbItem['mst_reward_id']['S'];

        // attributes
        if (isset($dynamoDbItem['status']['N'])) {
            $this->status = (int) $dynamoDbItem['status']['N'];
        } else {
            $this->status = DynJumpPlusRewardStatus::NOT_RECEIVED->value;
        }
    }

    public function getBnUserId(): string
    {
        return $this->bnUserId;
    }

    public function getMngJumpPlusRewardScheduleId(): string
    {
        return $this->mngJumpPlusRewardScheduleId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function canReceive(): bool
    {
        return $this->status === DynJumpPlusRewardStatus::NOT_RECEIVED->value;
    }
}
