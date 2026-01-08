<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstQuestEntity as Entity;
use App\Domain\Resource\Mst\Models\MstQuest as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstQuestRepository
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
        return $this->getAll()->get($id);
    }

    public function getByIdWithError(string $id): Entity
    {
        $entity = $this->getById($id);
        if ($entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('mst_quests record is not found. (mst_quest_id: %s)', $id),
            );
        }
        return $entity;
    }

    // start_dateとend_date内の対象IDのレコードを取得
    public function getQuestPeriod(string $id, CarbonImmutable $now): ?Entity
    {
        $entity = $this->getByIdWithError($id);

        if (!$now->between($entity->getStartDate(), $entity->getEndDate())) {
            throw new GameException(
                ErrorCode::QUEST_PERIOD_OUTSIDE,
                sprintf('mst_quests for the period record is not found. (mst_quest_id: %s)', $id),
            );
        }

        return $entity;
    }

    /**
     * start_dateとend_date内の対象IDのレコードを猶予期間をつけて取得
     * @param string $id
     * @param CarbonImmutable $now
     * @param bool $isThrowError
     * @return Entity|null
     * @throws GameException
     */
    public function getQuestGracePeriod(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
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
                $entity->getStartDate(),
                $entity->getEndDate()
            )
        ) {
            if ($isThrowError) {
                throw new GameException(
                    ErrorCode::QUEST_PERIOD_OUTSIDE,
                    sprintf('mst_quests for the period record with grace is not found. (mst_quest_id: %s)', $id),
                );
            }
            return null;
        }

        return $entity;
    }

    /**
     * @return Collection<string, Entity> key: mst_quests.id
     */
    public function getActivesByQuestType(string $questType, CarbonImmutable $now): Collection
    {
        $entities = $this->masterRepository->getByColumn(
            Model::class,
            'quest_type',
            $questType,
        );

        $result = collect();
        foreach ($entities as $entity) {
            if (!$now->between($entity->getStartDate(), $entity->getEndDate())) {
                continue;
            }

            $result->put($entity->getId(), $entity);
        }

        return $result;
    }
}
