<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstBoxGachaPrizeEntity as Entity;
use App\Domain\Resource\Mst\Models\MstBoxGachaPrize as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstBoxGachaPrizeRepository
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
     * @param string $mstBoxGachaGroupId
     * @return Collection<Entity>
     */
    public function getByMstBoxGachaGroupId(string $mstBoxGachaGroupId): Collection
    {
        return $this->getAll()->filter(
            function (Entity $entity) use ($mstBoxGachaGroupId) {
                return $entity->getMstBoxGachaGroupId() === $mstBoxGachaGroupId;
            }
        )->values();
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
                sprintf('mst_box_gacha_prizes record is not found. (id: %s)', $id),
            );
        }

        return $entity;
    }
}
