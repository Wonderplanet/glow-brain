<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Exchange\Models\LogExchangeAction as BaseLogExchangeAction;
use App\Dtos\RewardDto;
use App\Entities\Reward;
use App\Models\Mst\MstExchangeLineup;
use App\Traits\AthenaModelTrait;
use Illuminate\Support\Collection;

class LogExchangeAction extends BaseLogExchangeAction implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    /**
     * 報酬エンティティのキャッシュ
     * @var Collection<Reward>|null
     */
    private ?Collection $rewardsEntitiesCache = null;

    public function mst_exchange_lineup()
    {
        return $this->hasOne(MstExchangeLineup::class, 'id', 'mst_exchange_lineup_id');
    }

    /**
     * 報酬配列をRewardエンティティのCollectionに変換（遅延初期化）
     * @return Collection<Reward>
     */
    private function getRewardsEntities(): Collection
    {
        if ($this->rewardsEntitiesCache !== null) {
            return $this->rewardsEntitiesCache;
        }

        $rewardsArray = $this->rewards ?? [];

        $result = collect();
        foreach ($rewardsArray as $rewardArray) {
            $result->push(Reward::createByArray($rewardArray));
        }

        $this->rewardsEntitiesCache = $result;
        return $this->rewardsEntitiesCache;
    }

    /**
     * 複数の報酬をRewardDtoのCollectionとして取得
     * API側の保存形式: resourceType (PascalCase), resourceId, resourceAmount, preConversionResource
     * @return Collection<RewardDto>
     */
    public function getRewardsDtos(): Collection
    {
        $result = collect();
        foreach ($this->getRewardsEntities() as $reward) {
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

    /**
     * 変換前の報酬をRewardDtoのCollectionとして取得
     * 1つでも変換されたデータがある場合のみ全データを返す
     * @return Collection<RewardDto>
     */
    public function getBeforeRewardsDtos(): Collection
    {
        $result = collect();
        $hasConverted = false;

        foreach ($this->getRewardsEntities() as $reward) {
            if ($reward->isConverted()) {
                $hasConverted = true;
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

        return $hasConverted ? $result : collect();
    }

    /**
     * 複数のコストをRewardDtoのCollectionとして取得
     * API側の保存形式: cost_type (snake_case), cost_id, cost_amount
     * @return Collection<RewardDto>
     */
    public function getCostsDtos(): Collection
    {
        $result = collect();

        $costsArray = $this->costs ?? [];

        foreach ($costsArray as $index => $cost) {
            $result->push(
                new RewardDto(
                    $this->id . '_cost_' . $index,
                    $cost['cost_type'] ?? '',
                    $cost['cost_id'] ?? null,
                    $cost['cost_amount'] ?? 0
                )
            );
        }

        return $result;
    }
}
