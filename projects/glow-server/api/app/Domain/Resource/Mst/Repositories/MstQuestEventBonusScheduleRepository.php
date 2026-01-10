<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\MstQuestEventBonusSchedule as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstQuestEventBonusScheduleRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstQuestEventBonusScheduleEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * 現在有効なデータを全て取得する
     *
     * @param CarbonImmutable $now
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstQuestEventBonusScheduleEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getActiveAll(CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($now) {
            // 引数の$nowも、DBに保存されている日時も、どちらもUTCであることを前提としている
            $startAt = CarbonImmutable::parse($entity->getStartAt());
            $endAt = CarbonImmutable::parse($entity->getEndAt());
            return $now->between($startAt, $endAt);
        })->keyBy(function (\App\Domain\Resource\Mst\Entities\MstQuestEventBonusScheduleEntity $entity): string {
            return $entity->getId();
        });
    }
}
