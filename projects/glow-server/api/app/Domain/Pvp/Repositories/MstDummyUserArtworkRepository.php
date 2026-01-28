<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Repositories;

use App\Domain\Resource\Mst\Models\MstDummyUserArtwork;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstDummyUserArtworkRepository
{
    public function __construct(
        private readonly MasterRepository $masterRepository,
    ) {
    }

    public function getArtworkIdsByDummyUserId(string $mstDummyUserId): Collection
    {
        $getAll = $this->masterRepository->get(MstDummyUserArtwork::class);
        $entities = $getAll->filter(function ($entity) use ($mstDummyUserId) {
            return $entity->getMstDummyUserId() === $mstDummyUserId;
        })->map(function ($entity) {
            return $entity->getMstArtworkId();
        })->values();

        return $entities;
    }
}
