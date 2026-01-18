<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstStageEventSettingEntity as Entity;
use App\Domain\Resource\Mst\Models\MstStageEventSetting as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstStageEventSettingRepository
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
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param CarbonImmutable $now
     * @return Collection<Entity>
     * @throws GameException
     */
    public function getActiveAll(CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($now) {
            $startAt = CarbonImmutable::parse($entity->getStartAt());
            $endAt = CarbonImmutable::parse($entity->getEndAt());
            return $now->between($startAt, $endAt);
        })->keyBy(function ($entity): string {
            return $entity->getMstStageId();
        });
    }

    public function getByMstStageId(string $mstStageId, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAll()->filter(fn($entity) => $entity->getMstStageId() === $mstStageId)->first();
        if ($isThrowError && is_null($entity)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_stage_event_settings for the record is not found. (mst_stage_id: %s)',
                    $mstStageId
                ),
            );
        }
        return $entity;
    }

    /**
     * @param string $mstStageId
     * @param CarbonImmutable $now
     * @return Entity|null
     */
    public function getActiveByMstStageId(string $mstStageId, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($mstStageId, $now) {
            $startAt = CarbonImmutable::parse($entity->getStartAt());
            $endAt = CarbonImmutable::parse($entity->getEndAt());
            return $entity->getMstStageId() === $mstStageId && $now->between($startAt, $endAt);
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::STAGE_EVENT_PERIOD_OUTSIDE,
                sprintf(
                    'mst_stage_event_settings for the period record is not found. (mst_stage_id: %s)',
                    $mstStageId
                ),
            );
        }

        return $entities->first();
    }

    /**
     * @param Collection<string> $mstStageIds
     * @param CarbonImmutable $now
     * @return Collection<Entity>
     */
    public function getActiveByMstStageIds(Collection $mstStageIds, CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($mstStageIds, $now) {
            $startAt = CarbonImmutable::parse($entity->getStartAt());
            $endAt = CarbonImmutable::parse($entity->getEndAt());
            return $mstStageIds->contains($entity->getMstStageId()) && $now->between($startAt, $endAt);
        });
    }
}
