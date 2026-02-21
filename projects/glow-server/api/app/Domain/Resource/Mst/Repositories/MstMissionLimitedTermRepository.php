<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Resource\Mst\Entities\MstMissionLimitedTermEntity as Entity;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm as Model;
use App\Domain\Resource\Mst\Traits\MstRepositoryTrait;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstMissionLimitedTermRepository
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
     * @return Collection<string, Entity>
     */
    public function getMapAllActive(
        CarbonImmutable $now
    ): Collection {
        return $this->getAll()
            ->filter(function (Entity $entity) use ($now) {
                return $this->isActiveEntity($entity, $now);
            });
    }

    /**
     * カテゴリでフィルタした有効な期間限定ミッションを取得（メモリ効率改善版）
     *
     * @param CarbonImmutable $now
     * @param MissionLimitedTermCategory $category
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getMapAllActiveByCategory(
        CarbonImmutable $now,
        MissionLimitedTermCategory $category
    ): Collection {
        return $this->getAll()
            ->filter(function (Entity $entity) use ($now, $category) {
                return $this->isActiveEntity($entity, $now)
                    && $entity->getMissionCategory() === $category->value;
            });
    }

    /**
     * @param Collection<string> $ids
     * @return Collection<string, Entity>
     */
    public function getByIds(Collection $ids): Collection
    {
        return $this->getAll()->only($ids->unique()->toArray());
    }

    public function getActivesByIds(
        Collection $ids,
        CarbonImmutable $now
    ): Collection {
        return $this->getMapAllActive($now)->only($ids->unique()->toArray());
    }

    /**
     * @return Collection<Entity>
     */
    public function getTriggeredActiveMissionsByCriteria(
        Collection $criteria,
        CarbonImmutable $now,
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
            $criterionKey = MissionUtil::makeCriterionKey($entity->getCriterionType(), $entity->getCriterionValue());
            if (
                $this->isActiveEntity($entity, $now)
                && (
                    $targetCriterionKeys->has($criterionKey)
                    || $entity->isCompositeMission()
                )
            ) {
                $result->push($entity);
            }
        }

        return $result;
    }
}
