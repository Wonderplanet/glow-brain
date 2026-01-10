<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstApiActionEntity;
use App\Domain\Resource\Mst\Models\MstApiAction;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstApiActionRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<MstApiActionEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(MstApiAction::class);
    }

    /**
     * @param string $path
     * @return MstApiActionEntity|null
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function findByPath(string $path): ?MstApiActionEntity
    {
        return $this->getAll()->filter(function (MstApiActionEntity $mstApiActionEntity) use ($path) {
            return $mstApiActionEntity->getApiPath() === $path;
        })->first();
    }
}
