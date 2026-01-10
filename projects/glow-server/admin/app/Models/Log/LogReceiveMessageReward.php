<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Message\Models\LogReceiveMessageReward as BaseLogReceiveMessageReward;
use App\Dtos\RewardDto;
use App\Entities\Reward;
use App\Traits\AthenaModelTrait;
use Illuminate\Support\Collection;

/**
 * @property string|Collection $received_reward 受け取った報酬（JSON文字列またはRewardのコレクション）
 */
class LogReceiveMessageReward extends BaseLogReceiveMessageReward implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    /**
     * 受け取った報酬のコレクションを取得（遅延初期化）
     */
    private function getReceivedRewards(): Collection
    {
        // 既にCollectionならそのまま返す
        if ($this->received_reward instanceof Collection) {
            return $this->received_reward;
        }

        // JSON文字列をパースしてCollectionに変換
        $rewardsArray = json_decode($this->received_reward, true) ?? [];
        $result = collect();

        foreach ($rewardsArray as $rewardArray) {
            $result->push(
                Reward::createByArray($rewardArray)
            );
        }

        $this->received_reward = $result;
        return $this->received_reward;
    }

    public function getReceivedRewardDtos(): Collection
    {
        $result = collect();
        foreach ($this->getReceivedRewards() as $reward) {
            $result->push(
                new RewardDto(
                    $reward->getId(),
                    $reward->getType(),
                    $reward->getResourceId(),
                    $reward->getAmount()
                )
            );
        }

        return $result;
    }

    public function getBeforeReceivedRewardDtos(): Collection
    {
        $result = collect();
        foreach ($this->getReceivedRewards() as $reward) {
            if (!$reward->isConverted()) {
                continue;
            }
            $originalReward = $reward->getOriginalRewardData();
            $result->push(
                new RewardDto(
                    $reward->getId(),
                    $originalReward->getType(),
                    $originalReward->getResourceId(),
                    $originalReward->getAmount()
                )
            );
        }

        return $result;
    }
}
