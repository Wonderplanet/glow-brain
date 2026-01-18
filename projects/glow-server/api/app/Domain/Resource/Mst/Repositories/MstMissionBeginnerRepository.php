<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Entities\MstMissionBeginnerEntity as Entity;
use App\Domain\Resource\Mst\Models\MstMissionBeginner as Model;
use App\Domain\Resource\Mst\Traits\MstRepositoryTrait;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstMissionBeginnerRepository
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
    public function getMapAll(): Collection
    {
        return $this->getAll();
    }

    public function getById(string $id, bool $isThrowError = false): ?Entity
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

    public function getByIds(Collection $ids): Collection
    {
        return $this->getAll()->only($ids->unique()->toArray());
    }

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
     * ミッション機能開放からの経過日数を考慮して、開放済みの初心者ミッションマスタを取得する
     * @param int $maxUnlockDay ミッション機能開放からの経過日数
     * @return Collection<string, Entity>
     */
    public function getByMaxUnlockDay(int $maxUnlockDay): Collection
    {
        return $this->getAll()->filter(function (Entity $entity) use ($maxUnlockDay) {
            return $entity->getUnlockCriterionCount() <= $maxUnlockDay;
        });
    }
}
