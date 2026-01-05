<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstPvpRankEntity as Entity;
use App\Domain\Resource\Mst\Models\MstPvpRank as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstPvpRankRepository
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

    public function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAll()->get($id);

        if ($isThrowError && $entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_pvp_ranks record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entity;
    }

    public function getByClassTypeAndLevel(
        string $classType,
        int $level,
        bool $isThrowError = false,
    ): ?Entity {
        $entity = $this->getAll()->first(function (Entity $entity) use ($classType, $level) {
            return $entity->getRankClassType()->value === $classType && $entity->getRankClassLevel() === $level;
        });

        if ($isThrowError && $entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_pvp_ranks record is not found. (class type: %s, level: %d)',
                    $classType,
                    $level
                ),
            );
        }

        return $entity;
    }

    public function getByScore(
        int $score,
        bool $isThrowError = false,
    ): ?Entity {
        // スコアに対応するmst_pvp_ranksを取得
        $entity = $this->getAll()->filter(function (Entity $entity) use ($score) {
            return $entity->getRequiredLowerScore() <= $score;
        })->sortByDesc(function (Entity $entity) {
            return $entity->getRequiredLowerScore();
        })->first();

        if ($isThrowError && $entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_pvp_ranks record is not found. (score: %d)',
                    $score
                ),
            );
        }

        return $entity;
    }

    public function getAllSortedByRequiredLowerScore(): Collection
    {
        // スコアの照順でソートされたmst_pvp_ranksを取得
        return $this->getAll()->sortBy(function (Entity $entity) {
            return $entity->getRequiredLowerScore();
        });
    }
}
