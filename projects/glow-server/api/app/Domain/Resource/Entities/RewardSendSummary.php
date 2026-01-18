<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use Illuminate\Support\Collection;

/**
 * 報酬配布した結果についてまとめるentity
 */
class RewardSendSummary
{
    /**
     * @var Collection<BaseReward>
     */
    private Collection $rewards;

    public function __construct()
    {
        $this->rewards = collect();
    }

    public function getRewards(): Collection
    {
        return $this->rewards;
    }

    /**
     * @param Collection<BaseReward> $rewards
     */
    public function addRewards(Collection $rewards): void
    {
        // 要素を上書きまたは削除をしないようにvaluesしてからmergeする
        $this->rewards = $this->rewards->values()
            ->merge($rewards->values());
    }

    public function merge(RewardSendSummary $rewardSendSummary): void
    {
        $this->addRewards($rewardSendSummary->getRewards());
    }

    /**
     * 対象の報酬の内で、所持上限を超えたリソースがあるかどうか
     * @param array<string> $rewardTypes チェック対象の報酬タイプ RewardType enum の値を要素にもつ配列
     * @return bool true: 所持上限を超えたリソースがある, false: 所持上限を超えたリソースはない
     */
    public function hasResourceOverflow(array $rewardTypes): bool
    {
        if (count($rewardTypes) === 0) {
            return false;
        }
        $rewardTypes = array_fill_keys($rewardTypes, true);

        foreach ($this->rewards as $reward) {
            if (isset($rewardTypes[$reward->getType()]) === false) {
                continue; // チェック対象外の報酬タイプなのでスキップ
            }

            switch ($reward->getUnreceivedRewardReason()) {
                case UnreceivedRewardReason::RESOURCE_LIMIT_REACHED:
                case UnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDED:
                    // 所持上限を超えたリソースがある
                    return true;
                default:
                    continue 2; // 次のループへ
            }
        }

        return false;
    }
}
