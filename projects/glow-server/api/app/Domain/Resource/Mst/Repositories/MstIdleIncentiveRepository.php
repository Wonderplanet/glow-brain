<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstIdleIncentiveEntity as Entity;
use App\Domain\Resource\Mst\Models\MstIdleIncentive as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstIdleIncentiveRepository
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
    public function getMapAll(): Collection
    {
        return $this->getAll()->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    /**
     * @param  Collection<string>  $ids
     * @return Collection<Entity>
     */
    public function getByIds(Collection $ids): Collection
    {
        $entities = $this->getAll()->filter(function ($entity) use ($ids) {
            return $ids->containsStrict($entity->getId());
        });
        return $entities->values();
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
                    'mst_idle_incentives record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    /**
     * @param  Collection<string>  $ids
     * @return Collection<Entity>
     */
    public function getMapById(Collection $ids): Collection
    {
        return $this->getByIds($ids)->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    public function getLast(bool $isThrowError = false): Entity
    {
        $entity = $this->getAll()->last();

        if ($isThrowError && is_null($entity)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                'mst_idle_incentives record not found.',
            );
        }

        return $entity;
    }
}
