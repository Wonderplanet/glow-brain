<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Repositories;

use App\Domain\Resource\Mst\Entities\MstDummyUserEntity;
use App\Domain\Resource\Mst\Models\MstDummyUser;
use App\Domain\Resource\Mst\Models\MstDummyUserI18n;
use App\Infrastructure\MasterRepository;

class MstDummyUserRepository
{
    public function __construct(
        private readonly MasterRepository $masterRepository,
    ) {
    }

    public function getDummyUserById(string $id): MstDummyUserEntity
    {
        $getAll = $this->masterRepository->get(MstDummyUser::class);
        $entities = $getAll->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });
        return $entities->first();
    }

    public function getNameI18nByDummyUserId(string $dummyUserId): ?string
    {
        $getAll = $this->masterRepository->get(MstDummyUserI18n::class);
        $entities = $getAll->filter(function ($entity) use ($dummyUserId) {
            return $entity->getMstDummyUserId() === $dummyUserId;
        });
        $mstDummyUserI18n = $entities->first();
        return $mstDummyUserI18n->getName() ?? null;
    }
}
