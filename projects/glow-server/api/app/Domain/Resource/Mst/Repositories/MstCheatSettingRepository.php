<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstCheatSettingEntity as Entity;
use App\Domain\Resource\Mst\Models\MstCheatSetting as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstCheatSettingRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<Entity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @return Collection<Entity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getMapAll(): Collection
    {
        return $this->getAll()->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    /**
     * @param CarbonImmutable $now
     * @return Collection<Entity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getActiveAll(CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($now) {
            /** @var Entity $entity */
            $startDate = new CarbonImmutable($entity->getStartAt());
            $endDate = new CarbonImmutable($entity->getEndAt());
            return $now->between($startDate, $endDate);
        })->keyBy(function ($entity) {
            /** @var Entity $entity */
            return $entity->getId();
        });
    }

    /**
     * @param string $contentType
     * @param string $cheatType
     * @param CarbonImmutable $now
     * @return Collection<Entity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByType(string $contentType, string $cheatType, CarbonImmutable $now): Collection
    {
        return $this->getActiveAll($now)->filter(function ($entity) use ($contentType, $cheatType) {
            /** @var Entity $entity */
            return $entity->getContentType() === $contentType && $entity->getCheatType() === $cheatType;
        });
    }
}
