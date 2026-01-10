<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstFragmentBoxGroupEntity as Entity;
use App\Domain\Resource\Mst\Models\MstFragmentBoxGroup as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstFragmentBoxGroupRepository
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
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @return Collection<Entity>
     */
    public function getFragmentBoxAll(): Collection
    {
        return $this->getAll()->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    /**
     * @param  Collection<string>  $ids
     * @return Collection<Entity>
     */
    public function getByIds(Collection $ids): Collection
    {
        $entities = $this->getAll()->filter(function ($entity) use ($ids) {
            return $ids->containsStrict($entity->getId());
        });
        return $entities->values();
    }

    public function getById(string $id): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        return $entities->first();
    }

    public function getByMstItemId(string $mstItemId): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($mstItemId) {
            return $entity->getMstItemId() === $mstItemId;
        });

        return $entities->first();
    }

    /**
     * @param string $groupId
     * @param CarbonImmutable $now
     * @return Collection<Entity>
     */
    public function getActiveFragmentByGroupId(string $groupId, CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($groupId, $now) {
            /** @var Entity $entity */
            $startAt = new CarbonImmutable($entity->getStartAt());
            $endAt = new CarbonImmutable($entity->getEndAt());
            return $entity->getMstFragmentBoxGroupId() === $groupId && $now->between($startAt, $endAt);
        });
    }

    /**
     * @param string $groupId
     * @param string $mstItemId
     * @param CarbonImmutable $now
     * @return Entity|null
     */
    public function getActiveByGroupIdAndMstItemId(string $groupId, string $mstItemId, CarbonImmutable $now): ?Entity
    {
        return $this->getActiveFragmentByGroupId($groupId, $now)->filter(function ($entity) use ($mstItemId) {
            return $entity->getMstItemId() === $mstItemId;
        })->first();
    }
}
