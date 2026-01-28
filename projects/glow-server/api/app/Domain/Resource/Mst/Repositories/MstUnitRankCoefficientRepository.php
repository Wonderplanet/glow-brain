<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUnitRankCoefficientEntity as Entity;
use App\Domain\Resource\Mst\Models\MstUnitRankCoefficient as Model;
use App\Domain\Unit\Constants\UnitConstant;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstUnitRankCoefficientRepository
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

    public function getByRank(int $rank, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($rank) {
            /** @var Entity $entity */
            return $entity->getRank() === $rank;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_unit_rank_coefficients record is not found. (rank: $rank)"
            );
        }

        return $entities->first();
    }

    public function getByRanks(Collection $ranks, bool $isThrowError = false): Collection
    {
        $filteredEntities = $this->getAll()->filter(function ($entity) use ($ranks) {
            /** @var Entity $entity */
            return $ranks->contains($entity->getRank());
        });

        if ($isThrowError && $filteredEntities->count() !== $ranks->count()) {
            $ranksStr = $ranks->join(',');
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_unit_rank_coefficients records is not found. (rank: $ranksStr)"
            );
        }

        return $ranks->mapWithKeys(function ($rank) use ($filteredEntities) {
            $entity = $filteredEntities->firstWhere(fn($e) => $e->getRank() === $rank);
            return [
                $rank => $entity ?: null, // エンティティが存在しない場合はnullを設定
            ];
        });
    }

    public function getCoefficientByRank(int $rank, bool $isThrowError = false): int
    {
        $entity = $this->getByRank($rank, $isThrowError);
        return $entity ? $entity->getCoefficient() : UnitConstant::DEFAULT_UNIT_RANK_COEFFICIENT;
    }

    public function getCoefficientsByRanks(Collection $ranks, bool $isThrowError = false): Collection
    {
        $uniqueRanks = $ranks->unique();
        $entities = $this->getByRanks($uniqueRanks, $isThrowError);
        return $entities->map(function ($entity) {
            return $entity ? $entity->getCoefficient() : UnitConstant::DEFAULT_UNIT_RANK_COEFFICIENT;
        });
    }
}
