<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\OprGachaUpper;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class OprGachaUpperRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\OprGachaUpperEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(OprGachaUpper::class);
    }

    /**
     * @param string $upperGroup
     *
     * @return Collection<\App\Domain\Resource\Mst\Entities\OprGachaUpperEntity>
     */
    public function getByUpperGroup(string $upperGroup): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($upperGroup) {
            return $entity->getUpperGroup() === $upperGroup;
        })->values();
    }
}
