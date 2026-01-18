<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUnitRankUpEntity as Entity;
use App\Domain\Resource\Mst\Models\MstUnitRankUp;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstUnitRankUpRepository
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
        return $this->masterRepository->get(MstUnitRankUp::class);
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

    public function getById(int $id): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        return $entities->first();
    }

    public function getByUnitLabelAndRank(string $unitLabel, int $rank, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($unitLabel, $rank) {
            /** @var Entity $entity */
            return $entity->getUnitLabel() === $unitLabel && $entity->getRank() === $rank;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_unit_rank_ups record is not found. (unitLabel: $unitLabel, rank: $rank)"
            );
        }

        return $entities->first();
    }

    /**
     * @return Collection<Entity>
     */
    public function getSumItemAmountByRank(string $unitLabel, int $rank): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($unitLabel, $rank) {
            /** @var Entity $entity */
            return $entity->getUnitLabel() === $unitLabel && $entity->getRank() <= $rank;
        });
    }

    /**
     * @param Collection<array{string, int}> $unitLabelAndRanks ユニットラベル、ランクの配列のコレクション
     * @return Collection<Collection<Entity>>
     */
    public function getByUnitLabelsAndRanks(Collection $unitLabelAndRanks): Collection
    {
        $mstUnitRankUps = collect();
        foreach ($this->getAll() as $entity) {
            $unitLabel = $entity->getUnitLabel();
            $rank = $entity->getRank();
            if ($unitLabelAndRanks->contains([$unitLabel, $rank])) {
                if (!$mstUnitRankUps->has($unitLabel)) {
                    $mstUnitRankUps->put($unitLabel, collect());
                }
                $mstUnitRankUps[$unitLabel]->put($rank, $entity);
            }
        }
        return $mstUnitRankUps;
    }
}
