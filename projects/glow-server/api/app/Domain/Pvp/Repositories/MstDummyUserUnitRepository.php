<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Repositories;

use App\Domain\Resource\Mst\Models\MstDummyUserUnit;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstDummyUserUnitRepository
{
    public function __construct(
        private readonly MasterRepository $masterRepository,
    ) {
    }

    public function getDummyUnitByUserId(string $mstDummyUserId): Collection
    {
        $getAll = $this->masterRepository->get(MstDummyUserUnit::class);
        $entities = $getAll->filter(function ($entity) use ($mstDummyUserId) {
            return $entity->getMstDummyUserId() === $mstDummyUserId;
        });
        return $entities;
    }
}
