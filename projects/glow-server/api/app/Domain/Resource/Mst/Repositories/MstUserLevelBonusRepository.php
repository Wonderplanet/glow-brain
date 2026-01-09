<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Mst\Entities\MstUserLevelBonusEntity;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstUserLevelBonusRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<int, MstUserLevelBonusEntity> key: level
     */
    private function getMstUserLevelBonusAll(): Collection
    {
        return $this->masterRepository->get(
            MstUserLevelBonus::class,
            function ($entities) {
                return $entities->keyBy->getLevel();
            }
        );
    }

    /**
     * @return Collection<string, Collection<MstUserLevelBonusGroup>> key: mst_user_level_bonus_group_id
     */
    private function getMstUserLevelBonusGroupAll(): Collection
    {
        return $this->masterRepository->get(
            MstUserLevelBonusGroup::class,
            function ($entities) {
                return $entities->groupBy->getMstUserLevelBonusGroupId();
            }
        );
    }

    /**
     * ユーザーレベルアップ報酬情報を取得
     *
     * マスタデータ取得以外のロジックも含まれるので、本来はサービスクラスに移動するのが良い。
     * 開発初期に実装されたのもあるが、このメソッドを使用する箇所は増えない想定なので、このままにしている。
     *
     * @param integer $currentLevel 現在のレベル
     * @param integer $maxLevel レベルアップした後のレベル
     * @return Collection<UserLevelUpReward>
     */
    public function getBonuses(int $currentLevel, int $maxLevel): Collection
    {
        if ($currentLevel >= $maxLevel) {
            return collect();
        }

        $bonusGroupIdLevelMap = $this->getMstUserLevelBonusAll()
            ->only(range($currentLevel + 1, $maxLevel))
            ->mapWithKeys(function (MstUserLevelBonusEntity $entity) {
                return [$entity->getMstUserLevelBonusGroupId() => $entity->getLevel()];
            });

        $bonusGroups = $this->getMstUserLevelBonusGroupAll()
            ->only($bonusGroupIdLevelMap->keys())
            ->flatten();

        $rewards = collect();
        foreach ($bonusGroups as $bonusGroup) {
            $targetLevel = $bonusGroupIdLevelMap->get($bonusGroup->getMstUserLevelBonusGroupId());
            if ($targetLevel === null) {
                continue;
            }
            $reward = new UserLevelUpReward(
                $bonusGroup->getResourceType(),
                $bonusGroup->getResourceId(),
                $bonusGroup->getResourceAmount(),
                $targetLevel,
            );
            $rewards->push($reward);
        }

        return $rewards;
    }
}
