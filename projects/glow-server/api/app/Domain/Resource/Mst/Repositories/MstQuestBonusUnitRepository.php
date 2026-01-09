<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstQuestBonusUnitEntity as Entity;
use App\Domain\Resource\Mst\Models\MstQuestBonusUnit;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstQuestBonusUnitRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<Entity>
     * @throws GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(MstQuestBonusUnit::class);
    }

    /**
     * @return Collection<Entity>
     * @throws GameException
     */
    public function getMapAll(): Collection
    {
        return $this->getAll()->keyBy(function ($entity): string {
            return $entity->getId();
        });
    }

    /**
     * アクティブなリストを取得する
     *
     * @param CarbonImmutable $now
     * @return Collection
     * @throws GameException
     */
    public function getActives(CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($now) {
            $startAt = new CarbonImmutable($entity->getStartAt());
            $endAt = new CarbonImmutable($entity->getEndAt());
            return $now->between($startAt, $endAt);
        });
    }

    /**
     * mst_quest_idで取得する
     *
     * @param string $mstQuestId
     * @param CarbonImmutable $now
     * @return Collection<MstQuestBonusUnit>
     * @throws GameException
     */
    public function getListByMstQuestId(string $mstQuestId, CarbonImmutable $now): Collection
    {
        return $this->getActives($now)->filter(function ($entity) use ($mstQuestId) {
            return $entity->getMstQuestId() === $mstQuestId;
        });
    }
}
