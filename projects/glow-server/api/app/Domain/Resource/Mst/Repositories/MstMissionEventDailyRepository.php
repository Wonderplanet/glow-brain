<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Resource\Mst\Entities\MstMissionEventDailyEntity as Entity;
use App\Domain\Resource\Mst\Models\MstMissionEventDaily as Model;
use App\Domain\Resource\Mst\Traits\MstRepositoryTrait;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstMissionEventDailyRepository
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

    private function getByMstEventId(string $mstEventId): Collection
    {
        return $this->masterRepository->getByColumn(Model::class, 'mst_event_id', $mstEventId);
    }

    /**
     * @return Collection<string, Entity>
     */
    public function getMapByMstEventIds(Collection $mstEventIds): Collection
    {
        $result = collect();

        foreach ($mstEventIds as $mstEventId) {
            $entities = $this->getByMstEventId($mstEventId);
            foreach ($entities as $entity) {
                $result->put($entity->getId(), $entity);
            }
        }

        return $result;
    }

    /**
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getByIds(Collection $ids): Collection
    {
        return $this->getAll()->only($ids->unique()->toArray());
    }

    public function getByIdsAndMstEventIds(Collection $ids, Collection $mstEventIds): Collection
    {
        return $this->getMapByMstEventIds($mstEventIds)->only($ids->unique()->toArray());
    }

    /**
     * @return Collection<string, Entity>
     */
    public function getTriggeredMissionsByCriteriaAndMstEventIds(
        Collection $criteria,
        Collection $mstEventIds,
    ): Collection {
        if ($criteria->isEmpty() || $mstEventIds->isEmpty()) {
            return collect();
        }

        $result = collect();
        $all = $this->getAll();

        $targetCriterionKeys = collect();
        foreach ($criteria as $criterion) {
            $criterionKey = $criterion->getCriterionKey();

            $criterion->isClearCondition() && $targetCriterionKeys->put($criterionKey, true);
        }

        $targetMstEventIds = $mstEventIds->mapWithKeys(function ($mstEventId) {
            return [$mstEventId => true];
        });

        foreach ($all as $entity) {
            $criterionKey = MissionUtil::makeCriterionKey($entity->getCriterionType(), $entity->getCriterionValue());
            if (
                $targetMstEventIds->has($entity->getMstEventId())
                && (
                    $targetCriterionKeys->has($criterionKey)
                    || $entity->isCompositeMission()
                )
            ) {
                $result->put($entity->getId(), $entity);
            }
        }

        return $result;
    }
}
