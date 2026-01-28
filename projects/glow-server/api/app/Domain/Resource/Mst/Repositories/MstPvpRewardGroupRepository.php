<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Resource\Mst\Entities\MstPvpRewardGroupEntity as Entity;
use App\Domain\Resource\Mst\Models\MstPvpRewardGroup as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstPvpRewardGroupRepository
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

    public function getByRank(
        string $targetId,
        string $mstPvpRankId,
        bool $isThrowError = false,
    ): ?Entity {
        $entity = $this->getAll()->first(function (Entity $entity) use ($targetId, $mstPvpRankId) {
            return $entity->getMstPvpId() === $targetId
                && $entity->isRankClass()
                && $entity->getConditionValue() === $mstPvpRankId;
        });

        if ($entity !== null) {
            return $entity;
        }

        $entity = $this->getAll()->first(function (Entity $entity) use ($mstPvpRankId) {
            return $entity->getMstPvpId() === PvpConstant::DEFAULT_MST_PVP_ID
                && $entity->isRankClass()
                && $entity->getConditionValue() === $mstPvpRankId;
        });

        if ($isThrowError && $entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_pvp_reward_groups record is not found. (mstPvpId: %s mstPvpRankId: %s)',
                    $targetId,
                    $mstPvpRankId,
                ),
            );
        }

        return $entity;
    }

    public function getByRanking(
        string $targetId,
        int $ranking,
        bool $isThrowError = false,
    ): ?Entity {
        $entity = $this->getAll()->filter(function (Entity $entity) use ($targetId, $ranking) {
            return $entity->getMstPvpId() === $targetId
                && $entity->isRanking()
                && $entity->getConditionValue() >= $ranking;
        })->sortBy(fn (Entity $entity) => $entity->getConditionValue())
        ->first();

        if ($entity !== null) {
            return $entity;
        }

        $entity = $this->getAll()->filter(function (Entity $entity) use ($ranking) {
            return $entity->getMstPvpId() === PvpConstant::DEFAULT_MST_PVP_ID
                && $entity->isRanking()
                && $entity->getConditionValue() >= $ranking;
        })->sortBy(fn (Entity $entity) => $entity->getConditionValue())
        ->first();

        if ($isThrowError && $entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_pvp_reward_groups record is not found. (mstPvpId: %s ranking: %d)',
                    $targetId,
                    $ranking
                ),
            );
        }

        return $entity;
    }

    public function getByTotalScore(
        string $targetId,
        int $upperRangeScore,
        int $lowerRangeScore,
    ): Collection {
        $entities = $this->getAll()->filter(function (Entity $entity) use (
            $targetId,
            $upperRangeScore,
            $lowerRangeScore,
        ) {
            return $entity->getMstPvpId() === $targetId
                && $entity->isTotalScore()
                && $entity->getConditionValue() <= $upperRangeScore
                && $entity->getConditionValue() >= $lowerRangeScore;
        })->sortBy(fn (Entity $entity) => $entity->getConditionValue());

        if ($entities->isNotEmpty()) {
            return $entities;
        }

        return $this->getAll()->filter(function (Entity $entity) use (
            $upperRangeScore,
            $lowerRangeScore,
        ) {
            return $entity->getMstPvpId() === PvpConstant::DEFAULT_MST_PVP_ID
                && $entity->isTotalScore()
                && $entity->getConditionValue() <= $upperRangeScore
                && $entity->getConditionValue() >= $lowerRangeScore;
        })->sortBy(fn (Entity $entity) => $entity->getConditionValue());
    }
}
