<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstBoxGachaEntity as Entity;
use App\Domain\Resource\Mst\Models\MstBoxGacha as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstBoxGachaRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Entity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param string $id
     * @param bool $isThrowError
     * @return Entity|null
     * @throws GameException
     */
    public function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAll()->get($id);

        if ($isThrowError && is_null($entity)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('mst_box_gachas record is not found. (id: %s)', $id),
            );
        }

        return $entity;
    }

    /**
     * @param string $mstEventId
     * @return Collection<string, Entity>
     */
    public function getByMstEventId(string $mstEventId): Collection
    {
        return $this->getAll()->filter(
            function (Entity $entity) use ($mstEventId) {
                return $entity->getMstEventId() === $mstEventId;
            }
        );
    }
}
