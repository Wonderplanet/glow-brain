<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstMissionEventDailyBonusEntity;
use App\Domain\Resource\Mst\Entities\MstMissionEventDailyBonusEntity as Entity;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonus;
use App\Domain\Resource\Mst\Repositories\Contracts\MstMissionRepositoryReceiveRewardInterface;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstMissionEventDailyBonusRepository implements MstMissionRepositoryReceiveRewardInterface
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
        return $this->masterRepository->get(MstMissionEventDailyBonus::class);
    }

    /**
     * @return Collection<Entity>
     */
    public function getMapAll(): Collection
    {
        return $this->getAll()->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    public function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_mission_event_daily_bonuses record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    public function getMapByMstScheduleIds(Collection $mstScheduleIds): Collection
    {
        return $this->getAll()->filter(function (MstMissionEventDailyBonusEntity $entity) use ($mstScheduleIds) {
            return $mstScheduleIds->containsStrict($entity->getMstMissionEventDailyBonusScheduleId());
        })->keyBy(function (MstMissionEventDailyBonusEntity $entity) {
            return $entity->getId();
        });
    }

    public function getMapByMstScheduleId(string $mstScheduleId): Collection
    {
        return $this->getAll()->filter(function (MstMissionEventDailyBonusEntity $entity) use ($mstScheduleId) {
            return $entity->getMstMissionEventDailyBonusScheduleId() === $mstScheduleId;
        })->keyBy(function (MstMissionEventDailyBonusEntity $entity) {
            return $entity->getId();
        });
    }
}
