<?php

declare(strict_types=1);

namespace App\Domain\Reward\Managers;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Reward\Entities\RewardSent;
use Illuminate\Support\Collection;

/**
 * Rewardドメイン内でのRewardManagerとの循環参照を避けるために最低限必要なメソッドを抽象化し定義したインターフェース
 */
interface RewardManagerInterface
{
    /**
     * 報酬オブジェクトを報酬付与前リストに追加する。
     *
     * @param BaseReward $reward
     * @return void
     */
    public function addReward(BaseReward $reward): void;

    /**
     * 報酬オブジェクトを報酬付与前リストにまとめて追加する。
     *
     * @param Collection $rewards
     * @return void
     */
    public function addRewards(Collection $rewards): void;

    /**
     * @return Collection<string, BaseReward>
     *   key: BaseReward.id, value: BaseReward
     */
    public function getNeedToSendRewards(): Collection;

    public function afterSend(RewardSent $sentData): void;

    public function isNeedSendRewards(): bool;
}
