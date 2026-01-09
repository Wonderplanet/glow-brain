<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstAdventBattleEntity as Entity;
use App\Domain\Resource\Mst\Models\MstAdventBattle as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstAdventBattleRepository
{
    public function __construct(
        private readonly MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<Entity>
     * @throws GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param string $id
     * @return Entity|null
     * @throws GameException
     */
    public function getById(string $id): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        return $entities->first();
    }

    /**
     * @param string $id
     * @return Entity
     * @throws GameException
     */
    public function getByIdWithError(string $id): Entity
    {
        $entity = $this->getById($id);
        if ($entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('mst_advent_battles record is not found. (mst_advent_battle_id: %s)', $id),
            );
        }
        return $entity;
    }

    /**
     * start_atとend_at内の対象IDのレコードを取得
     * @param string $id
     * @param CarbonImmutable $now
     * @param bool $isThrowError
     * @return Entity|null
     * @throws GameException
     */
    public function getActive(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        if ($isThrowError) {
            $entity = $this->getByIdWithError($id);
        } else {
            $entity = $this->getById($id);
            if ($entity === null) {
                return null;
            }
        }

        if (!$now->between($entity->getStartAt(), $entity->getEndAt())) {
            if ($isThrowError) {
                throw new GameException(
                    ErrorCode::ADVENT_BATTLE_PERIOD_OUTSIDE,
                    sprintf('mst_advent_battles for the period record is not found. (mst_advent_battle_id: %s)', $id),
                );
            }
            return null;
        }

        return $entity;
    }

    /**
     * start_atとend_at内の対象レコードを全て取得
     * @param CarbonImmutable $now
     * @return Collection<Entity>
     * @throws GameException
     */
    public function getActiveAll(CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($now) {
            /** @var Entity $entity */
            $startDate = new CarbonImmutable($entity->getStartAt());
            $endDate = new CarbonImmutable($entity->getEndAt());
            return $now->between($startDate, $endDate);
        })->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    /**
     * 直近終了した降臨バトルを取得
     * @param CarbonImmutable $now
     * @return Entity|null
     * @throws GameException
     */
    public function getRecentlyFinishedAdventBattle(CarbonImmutable $now): ?Entity
    {
        return $this->getAll()->filter(function ($entity) use ($now) {
            /** @var Entity $entity */
            $endDate = new CarbonImmutable($entity->getEndAt());
            return $now->gt($endDate);
        })->sortByDesc(function ($entity) {
            /** @var Entity $entity */
            return $entity->getEndAt();
        })->first();
    }

    /**
     * 報酬受け取り期間内の降臨バトルを取得
     * @param CarbonImmutable $now
     * @param int             $aggregationHours
     * @return Collection
     * @throws GameException
     */
    public function getWithinRewardReceivePeriod(CarbonImmutable $now, int $aggregationHours): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($now, $aggregationHours) {
            /** @var Entity $entity */
            $endDate = new CarbonImmutable($entity->getEndAt());
            // 報酬受け取り開始(終了日に集計時間を加算した日時)
            $rewardStartDate = $endDate->addHours($aggregationHours);
            // 報酬受け取り期限(報酬受け取り開始に30日を加算した日時)
            $rewardEndDate = $rewardStartDate->addDays(AdventBattleConstant::SEASON_REWARD_LIMIT_DAYS);
            return $now->between($rewardStartDate, $rewardEndDate);
        })->keyBy(function ($entity) {
            /** @var Entity $entity */
            return $entity->getId();
        });
    }

    public function getPreviousMstAdventBattle(string $mstAdventBattleId): Entity
    {
        $currentEntity = $this->getById($mstAdventBattleId);
        if (is_null($currentEntity)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('Previous mst_advent_battle_id not found for %s', $mstAdventBattleId),
            );
        }

        $result =  $this->getAll()->filter(function (Entity $entity) use ($currentEntity) {
            return $entity->getEndAt() < $currentEntity->getStartAt();
        })->sortByDesc(function (Entity $entity) {
            return $entity->getEndAt();
        })->first();

        if (is_null($result)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('Previous mst_advent_battle_id not found for %s', $mstAdventBattleId),
            );
        }

        return $result;
    }
}
