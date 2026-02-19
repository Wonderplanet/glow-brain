<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUnitSpecificRankUpEntity as Entity;
use App\Domain\Resource\Mst\Models\MstUnitSpecificRankUp as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstUnitSpecificRankUpRepository
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
        return $this->getAll()->keyBy(function ($entity): string {
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
                    'mst_unit_specific_rank_ups record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    public function getByMstUnitIdAndRank(string $mstUnitId, int $rank, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($mstUnitId, $rank) {
            return $entity->getMstUnitId() === $mstUnitId && $entity->getRank() === $rank;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_unit_specific_rank_ups record is not found. (mst_unit_id: %s, rank: %d)',
                    $mstUnitId,
                    $rank
                ),
            );
        }

        return $entities->first();
    }

    /**
     * @param Collection<string, int> $mstUnitIdRankMap キー:mstUnitId, 値:rank
     * @return Collection<Collection<Entity>>
     */
    public function getByMstUnitIdRankMap(Collection $mstUnitIdRankMap): Collection
    {
        $mstUnitRankUps = collect();
        foreach ($this->getAll() as $entity) {
            $mstUnitId = $entity->getMstUnitId();
            $rank = $entity->getRank();
            $searchRank = $mstUnitIdRankMap->get($mstUnitId);
            if ($searchRank === $rank) {
                if (!$mstUnitRankUps->has($mstUnitId)) {
                    $mstUnitRankUps->put($mstUnitId, collect());
                }
                $mstUnitRankUps[$mstUnitId]->put($rank, $entity);
            }
        }
        return $mstUnitRankUps;
    }
}
