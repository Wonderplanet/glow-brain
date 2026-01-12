<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUnitEntity as Entity;
use App\Domain\Resource\Mst\Models\MstUnit as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstUnitRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<Entity>
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param  Collection<string>  $ids
     * @return Collection<string, Entity> key: id
     */
    public function getByIds(Collection $ids): Collection
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->getAll()->only($ids->toArray());
    }

    public function getById(string $id): ?Entity
    {
        return $this->getAll()->get($id);
    }

    /**
     * @param string $id
     * @return Entity
     * @throws GameException
     */
    public function getByIdWithError(string $id): Entity
    {
        $entity = $this->getById($id);
        if ($entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('mst_units record is not found. (mst_unit_id: %s)', $id),
            );
        }

        return $entity;
    }
}
