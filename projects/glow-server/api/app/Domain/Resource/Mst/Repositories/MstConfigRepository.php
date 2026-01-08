<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstConfigEntity as Entity;
use App\Domain\Resource\Mst\Models\MstConfig as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstConfigRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Entity>
     * key: key, value: Entity
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(
            Model::class,
            function (Collection $entities) {
                return $entities->keyBy(function (Entity $entity) {
                    return $entity->getKey();
                });
            }
        );
    }

    public function getValueByKey(string $key, bool $isThrowError = false): int|string|null
    {
        $entity = $this->getAll()->get($key);

        if ($isThrowError && is_null($entity)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_configs record is not found. (key: %s)',
                    $key
                ),
            );
        }

        return $entity?->getValue();
    }
}
