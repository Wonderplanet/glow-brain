<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Repositories;

use App\Domain\Resource\Mst\Models\MstDummyOutpost;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstDummyOutpostRepository
{
    public function __construct(
        private readonly MasterRepository $masterRepository,
    ) {
    }

    public function getMstDummyOutpostEnhancementIdByUserId(string $mstDummyUserId): Collection
    {
        $getAll = $this->masterRepository->get(MstDummyOutpost::class);
        $entities = $getAll->filter(function ($entity) use ($mstDummyUserId) {
            return $entity->getMstDummyUserId() === $mstDummyUserId;
        });

        return $entities;
    }
}
