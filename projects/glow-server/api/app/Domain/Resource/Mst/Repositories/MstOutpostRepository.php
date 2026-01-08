<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstOutpostEntity as Entity;
use App\Domain\Resource\Mst\Models\MstOutpost as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstOutpostRepository
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
    public function getMapAll(): Collection
    {
        return $this->getAll()->keyBy(function ($entity): string {
            return $entity->getId();
        });
    }

    public function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_outposts record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }
}
