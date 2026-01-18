<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstUnitFragmentConvertEntity as Entity;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstUnitFragmentConvertRepository
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
    public function getMapAllByUnitLabel(): Collection
    {
        return $this->getAll()->keyBy(function (Entity $entity): string {
            return $entity->getUnitLabel();
        });
    }
}
