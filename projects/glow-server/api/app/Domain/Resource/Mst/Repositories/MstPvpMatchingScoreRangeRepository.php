<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstPvpMatchingScoreRangeEntity as Entity;
use App\Domain\Resource\Mst\Models\MstPvpMatchingScoreRange as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstPvpMatchingScoreRangeRepository
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

    public function getByTypeAndLevel(string $rankClassType, int $rankClassLevel, bool $isThrowError = false): Entity
    {
        $entities = $this->getAll()->filter(function (Entity $entity) use ($rankClassType, $rankClassLevel) {
            return $entity->getRankClassType() === $rankClassType && $entity->getRankClassLevel() === $rankClassLevel;
        });
        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'MstPvpMatchingScoreRange record is not found. (rankClassType: %s, rankClassLevel: %s)',
                    $rankClassType,
                    $rankClassLevel
                ),
            );
        }

        return $entities->first();
    }
}
