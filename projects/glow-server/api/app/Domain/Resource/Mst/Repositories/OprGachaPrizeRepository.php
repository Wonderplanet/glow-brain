<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class OprGachaPrizeRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\OprGachaPrizeEntity>
     * @throws GameException
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(OprGachaPrize::class);
    }

    /**
     * @param string $groupId
     *
     * @return Collection<\App\Domain\Resource\Mst\Entities\OprGachaPrizeEntity>
     * @throws GameException
     */
    private function getByGroupId(string $groupId): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($groupId) {
            return $entity->getGroupId() === $groupId;
        })->values();
    }

    /**
     * @param string $groupId
     *
     * @return Collection<\App\Domain\Resource\Mst\Entities\OprGachaPrizeEntity>
     * @throws GameException
     */
    public function getByGroupIdWithError(string $groupId): Collection
    {
        $entity = $this->getByGroupId($groupId);
        if ($entity->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('opr_gacha_prizes record is not found. (group_id: %s)', $groupId),
            );
        }

        return $entity;
    }
}
