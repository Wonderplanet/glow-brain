<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUserLevelEntity;
use App\Domain\Resource\Mst\Models\MstUserLevel as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstUserLevelRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<MstUserLevelEntity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param int $level
     * @return Collection
     */
    public function getHigherLevelData(int $level): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($level) {
            /** @var MstUserLevelEntity $entity */
            return $entity->getLevel() >= $level;
        })->sortBy(function ($entity) {
            /** @var MstUserLevelEntity $entity */
            return (int) $entity->getLevel();
        });
    }

    /**
     * @param int  $level
     * @param bool $isThrowError
     * @return MstUserLevelEntity|null
     * @throws GameException
     */
    public function getByLevel(int $level, bool $isThrowError = false): ?MstUserLevelEntity
    {
        $result = $this->getAll()->first(function ($entity) use ($level) {
            /** @var MstUserLevelEntity $entity */
            return (int)$entity->getLevel() === (int)$level;
        });

        if ($isThrowError && is_null($result)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_user_levels record is not found. (level: $level)"
            );
        }
        return $result;
    }
}
