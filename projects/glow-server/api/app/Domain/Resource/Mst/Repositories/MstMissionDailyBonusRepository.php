<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstMissionDailyBonusEntity as Entity;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus;
use App\Domain\Resource\Mst\Repositories\Contracts\MstMissionRepositoryReceiveRewardInterface;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstMissionDailyBonusRepository implements MstMissionRepositoryReceiveRewardInterface
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
        return $this->masterRepository->get(MstMissionDailyBonus::class);
    }

    /**
     * @return Collection<Entity>
     */
    public function getMapAll(): Collection
    {
        return $this->getAll()->keyBy(function (Entity $entity): string {
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
                    'mst_mission_daily_bonuses record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    /**
     * デイリーボーナス進捗更新に必要な分だけデータを取得する
     *
     * マスタデータが新規追加されるパターンもあるため、達成可能な分だけを取得する。
     *
     * @param int $totalProgress 生涯累積ログイン日数
     * @return Collection<string, Entity> key: mst_mission_daily_bonus_id, value: Entity
     */
    public function getMapForUpdateStatus(int $totalProgress): Collection
    {
        $entities = collect();

        $all = $this->getAll();
        foreach ($all as $entity) {
            /** @var Entity $entity */
            // DailyBonusタイプのみを対象とする
            if ($entity->isDailyBonusType()) {
                $entities->put($entity->getId(), $entity);
            }
        }

        return $entities;
    }
}
