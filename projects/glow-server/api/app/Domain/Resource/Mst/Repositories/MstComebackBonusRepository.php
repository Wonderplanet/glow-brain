<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstComebackBonusEntity as Entity;
use App\Domain\Resource\Mst\Models\MstComebackBonus;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstComebackBonusRepository
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
        return $this->masterRepository->get(MstComebackBonus::class);
    }

    /**
     * @return Collection<Entity>
     */
    public function getMapAll(): Collection
    {
        return $this->getAll();
    }

    public function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->get($id);
        if ($isThrowError && $entities === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_comeback_bonuses record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities;
    }

    public function getMapByMstScheduleIds(Collection $mstScheduleIds): Collection
    {
        return $this->getAll()->filter(function (Entity $entity) use ($mstScheduleIds) {
            return $mstScheduleIds->containsStrict($entity->getMstScheduleId());
        });
    }

    public function getMapByMstScheduleId(string $mstScheduleId): Collection
    {
        return $this->getAll()->filter(function (Entity $entity) use ($mstScheduleId) {
            return $entity->getMstScheduleId() === $mstScheduleId;
        });
    }
}
