<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstComebackBonusEntity as Entity;
use App\Domain\Resource\Mst\Models\MstComebackBonusSchedule;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstComebackBonusScheduleRepository
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
        return $this->masterRepository->get(MstComebackBonusSchedule::class);
    }

    /**
     * @return Collection<Entity>
     */
    public function getMapAll(): Collection
    {
        return $this->getAll();
    }

    /**
     * @return Collection<Entity>
     */
    public function getActiveMapAll(CarbonImmutable $now): Collection
    {
        return $this->getMapAll()->filter(function ($entity) use ($now) {
            $startAt = new CarbonImmutable($entity->getStartAt());
            $endAt = new CarbonImmutable($entity->getEndAt());
            return $now->between($startAt, $endAt);
        });
    }

    public function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->get($id);
        if ($isThrowError && $entities === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_comeback_bonus_schedules record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities;
    }
}
