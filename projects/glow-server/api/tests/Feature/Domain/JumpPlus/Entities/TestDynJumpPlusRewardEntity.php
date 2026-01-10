<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\JumpPlus\Entities;

use App\Domain\JumpPlus\Enums\DynJumpPlusRewardStatus;
use App\Domain\Resource\Dyn\Entities\DynJumpPlusRewardEntity;

/**
 * テスト用のDynJumpPlusReward
 */
class TestDynJumpPlusRewardEntity extends DynJumpPlusRewardEntity
{
    public function __construct(
        string $bnUserId,
        string $mngJumpPlusRewardScheduleId,
        DynJumpPlusRewardStatus $status,
        ?string $receivedUsrUserId = null,
        ?int $receivedRewardAt = null,
    ) {
        parent::__construct([
            'bn_user_id' => ['S' => $bnUserId],
            'mst_reward_id' => ['S' => $mngJumpPlusRewardScheduleId],
            'status' => ['N' => (string) $status->value],
            'received_usr_user_id' => $receivedUsrUserId ? ['S' => $receivedUsrUserId] : null,
            'received_reward_at' => $receivedRewardAt ? ['N' => (string) $receivedRewardAt] : null,
        ]);
    }
}
