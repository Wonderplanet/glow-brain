<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstEmblemEntity as Entity;
use App\Domain\Resource\Mst\Models\MstEmblem as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstEmblemRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Entity> key: id
     * @throws GameException
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param  Collection<string>  $ids
     * @return Collection<string, Entity> key: id
     * @throws GameException
     */
    public function getByIds(Collection $ids): Collection
    {
        return $this->getAll()->only($ids->toArray());
    }

    /**
     * @param string $id
     * @return Entity|null
     * @throws GameException
     */
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
                sprintf('mst_emblems record is not found. (mst_emblem_id: %s)', $id),
            );
        }

        return $entity;
    }
}
