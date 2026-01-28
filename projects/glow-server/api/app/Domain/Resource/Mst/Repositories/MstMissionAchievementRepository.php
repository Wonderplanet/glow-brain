<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Entities\MstMissionAchievementEntity as Entity;
use App\Domain\Resource\Mst\Models\MstMissionAchievement as Model;
use App\Domain\Resource\Mst\Traits\MstRepositoryTrait;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstMissionAchievementRepository
{
    use MstRepositoryTrait;

    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Entity>
     * key: id, value: Entity
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @return Collection<string, Entity>
     * key: id, value: Entity
     */
    public function getMapAll(): Collection
    {
        return $this->getAll();
    }

    public function getById(string $id, bool $isThrowError = false): Entity|null
    {
        $entity = $this->getAll()->get($id);

        $this->throwMstNotFoundException(
            $isThrowError,
            Model::class,
            $entity,
            ['id' => $id],
        );

        return $entity;
    }

    /**
     * @return Collection<string, Entity>|Collection<string, MstMissionEntityInterface>
     */
    public function getByIds(Collection $ids): Collection
    {
        return $this->getAll()->only($ids->unique()->toArray());
    }

    public function getByCriterionTypes(
        Collection $criterionTypes,
    ): Collection {
        return $this->filterWhereIn(
            entities: $this->getAll(),
            getterMethod: 'getCriterionType',
            values: $criterionTypes,
        );
    }

    /**
     * 指定されたMissionCriterionに該当するミッションと、複合ミッションのマスタデータを取得する
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
        $targetUnlockCriterionKeys = collect();
        foreach ($criteria as $criterion) {
            $criterionKey = $criterion->getCriterionKey();

            $criterion->isClearCondition() && $targetCriterionKeys->put($criterionKey, true);
            $criterion->isUnlockCondition() && $targetUnlockCriterionKeys->put($criterionKey, true);
        }

        foreach ($all as $entity) {
            if (
                $targetCriterionKeys->has($entity->getCriterionKey())
                || $targetUnlockCriterionKeys->has($entity->getUnlockCriterionKey())
                || $entity->isCompositeMission()
            ) {
                $result->push($entity);
            }
        }

        return $result;
    }
}
