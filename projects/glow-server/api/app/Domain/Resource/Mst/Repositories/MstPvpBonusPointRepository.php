<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Pvp\Enums\PvpBonusType;
use App\Domain\Resource\Mst\Entities\MstPvpBonusPointEntity as Entity;
use App\Domain\Resource\Mst\Models\MstPvpBonusPoint as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstPvpBonusPointRepository
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

    /**
     * @return Collection<Entity>
     */
    public function getByOpponentScore(string $rankClassType): Collection
    {
        return $this->getAll()->filter(function (Entity $entity) use ($rankClassType) {
            return $entity->getConditionValue() === $rankClassType
                && ($entity->getBonusType() === PvpBonusType::WinUpperBonus->value
                || $entity->getBonusType() === PvpBonusType::WinSameBonus->value
                || $entity->getBonusType() === PvpBonusType::WinLowerBonus->value);
        })->keyBy(fn(Entity $entity) => $entity->getBonusType());
    }

    public function getByClearTime(int $clearTimeMs): ?Entity
    {
        return $this->getAll()->filter(function (Entity $entity) use ($clearTimeMs) {
            return $entity->getBonusType() === PvpBonusType::ClearTime->value
                && $entity->getConditionValueAsInt() >= $clearTimeMs;
        })->sortBy(
            fn(Entity $entity) => $entity->getConditionValueAsInt()
        )->first();
    }
}
