<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstStageEntity as Entity;
use App\Domain\Resource\Mst\Models\MstStage as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstStageRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<Entity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    public function getById(string $id): ?Entity
    {
        return $this->masterRepository->getByColumn(Model::class, 'id', $id)->first();
    }

    public function getByIdWithError(string $id): Entity
    {
        $entity = $this->getById($id);
        if ($entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('mst_stages record is not found. (mst_stage_id: %s)', $id),
            );
        }

        return $entity;
    }

    /**
     * @return Collection<Entity>
     */
    public function getByMstQuestId(string $mstQuestId): Collection
    {
        return $this->masterRepository->getByColumn(Model::class, 'mst_quest_id', $mstQuestId);
    }

    /**
     * @return Collection<string, Entity> key: mst_stages.id
     */
    public function getByMstQuestIds(Collection $mstQuestIds): Collection
    {
        $targetMstQuestIds = $mstQuestIds->mapWithKeys(
            fn($mstQuestId) => [$mstQuestId => true]
        );

        return $this->getAll()->filter(function (Entity $entity) use ($targetMstQuestIds) {
            return isset($targetMstQuestIds[$entity->getMstQuestId()]);
        });
    }

    /**
     * start_atとend_at内の対象IDのレコードを取得
     * @param string $id
     * @param CarbonImmutable $now
     * @return Entity|null
     * @throws GameException
     */
    public function getStagePeriod(string $id, CarbonImmutable $now): ?Entity
    {
        $entity = $this->getByIdWithError($id);

        if (!$now->between($entity->getStartAt(), $entity->getEndAt())) {
            throw new GameException(
                ErrorCode::QUEST_PERIOD_OUTSIDE,
                sprintf('mst_stages for the period record with grace is not found. (mst_stage_id: %s)', $id),
            );
        }

        return $entity;
    }

    /**
     * start_atとend_at内の対象IDのレコードをを取得(猶予期間を考慮)
     * @param string $id
     * @param CarbonImmutable $now
     * @param bool $isThrowError
     * @return Entity|null
     * @throws GameException
     */
    public function getStageGracePeriod(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        if ($isThrowError) {
            $entity = $this->getByIdWithError($id);
        } else {
            $entity = $this->getById($id);
            if ($entity === null) {
                return null;
            }
        }

        if (
            !$now->between(
                $entity->getStartAt(),
                $entity->getEndAt()
            )
        ) {
            if ($isThrowError) {
                throw new GameException(
                    ErrorCode::QUEST_PERIOD_OUTSIDE,
                    sprintf('mst_stages for the period record with grace is not found. (mst_stage_id: %s)', $id),
                );
            }
            return null;
        }

        return $entity;
    }
}
