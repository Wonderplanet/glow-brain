<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstFragmentBoxEntity as Entity;
use App\Domain\Resource\Mst\Models\MstFragmentBox as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstFragmentBoxRepository
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

    public function getByMstItemId(string $mstItemId, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($mstItemId) {
            return $entity->getMstItemId() === $mstItemId;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_fragment_boxes record is not found. (mst_item_id: %s)',
                    $mstItemId
                ),
            );
        }

        return $entities->first();
    }
}
