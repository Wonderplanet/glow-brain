<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Entities\MstMissionDailyEntity as Entity;
use App\Domain\Resource\Mst\Models\MstMissionDaily as Model;
use App\Domain\Resource\Mst\Traits\MstRepositoryTrait;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstMissionDailyRepository
{
    use MstRepositoryTrait;

    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Entity> key: id, value: Entity
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @return Collection<string, Entity> | Collection<string, MstMissionEntityInterface>
     */
    public function getMapAll(): Collection
    {
        return $this->getAll();
    }

    public function getByIds(Collection $ids): Collection
    {
        return $this->getAll()->only($ids->unique()->toArray());
    }

    /**
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getByCriterionType(
        string $criterionType,
    ): Collection {
        return $this->getAll()->filter(function (Entity $entity) use ($criterionType) {
            return $entity->getCriterionType() === $criterionType;
        });
    }

    /**
     * 指定されたidのデータと、ボーナスポイントのデータを合わせて取得
     * @return Collection<string, Entity|MstMissionEntityInterface>
     */
    public function getByIdsAndBonusPoints(
        Collection $ids,
    ): Collection {
        return $this->getByIds($ids)
            ->merge($this->getByCriterionType(MissionCriterionType::MISSION_BONUS_POINT->value));
    }

    /**
     * @return Collection<Entity>
     */
    public function getTriggeredMissionsByCriteria(
        Collection $criteria,
    ): Collection {
        if ($criteria->isEmpty()) {
            return collect();
        }

        $result = collect();
        $all = $this->getAll();

        $targetCriterionKeys = collect();
        foreach ($criteria as $criterion) {
            $criterionKey = $criterion->getCriterionKey();

            $criterion->isClearCondition() && $targetCriterionKeys->put($criterionKey, true);
        }

        foreach ($all as $entity) {
            if (
                $targetCriterionKeys->has($entity->getCriterionKey())
                || $entity->isCompositeMission()
            ) {
                $result->push($entity);
            }
        }

        return $result;
    }

    /**
     * ボーナスポイントミッションの最後を取得
     */
    public function getLastBonusPointMission(): ?Entity
    {
        return $this->getByCriterionType(MissionCriterionType::MISSION_BONUS_POINT->value)
            ->sortByDesc(function (Entity $entity) {
                return $entity->getCriterionCount();
            })->first();
    }
}
