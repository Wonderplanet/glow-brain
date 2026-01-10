<?php

declare(strict_types=1);

namespace App\Domain\JumpPlus\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $mng_jump_plus_reward_schedule_id
 */
class UsrJumpPlusReward extends UsrEloquentModel implements UsrJumpPlusRewardInterface
{
    use HasFactory;

    public function init(string $usrUserId, string $mngJumpPlusRewardScheduleId): UsrJumpPlusReward
    {
        $this->id = $this->newUniqueId();
        $this->usr_user_id = $usrUserId;
        $this->mng_jump_plus_reward_schedule_id = $mngJumpPlusRewardScheduleId;

        return $this;
    }

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return self::makeModelKeyAsStatic(
            $this->usr_user_id,
            $this->mng_jump_plus_reward_schedule_id,
        );
    }

    /**
     * Repositoryでキャッシュからデータを効率良く取得できるように、staticでキーを生成するメソッドを用意
     */
    public static function makeModelKeyAsStatic(
        string $usrUserId,
        string $mngJumpPlusRewardScheduleId,
    ): string {
        return $usrUserId . $mngJumpPlusRewardScheduleId;
    }

    public function getMngJumpPlusRewardScheduleId(): string
    {
        return $this->mng_jump_plus_reward_schedule_id;
    }
}
