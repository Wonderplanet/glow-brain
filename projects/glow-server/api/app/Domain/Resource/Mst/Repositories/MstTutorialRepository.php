<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstTutorialEntity as Entity;
use App\Domain\Resource\Mst\Models\MstTutorial as Model;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstTutorialRepository
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
                    'mst_tutorials record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    public function getActiveAll(CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function (Entity $entity) use ($now) {
            $startAt = new CarbonImmutable($entity->getStartAt());
            $endAt = new CarbonImmutable($entity->getEndAt());
            return $now->between($startAt, $endAt);
        });
    }

    public function getActiveByFunctionName(
        string $functionName,
        CarbonImmutable $now,
        bool $isThrowError = false,
    ): ?Entity {
        $entities = $this->getActiveAll($now)->filter(function (Entity $entity) use ($functionName) {
            return $entity->getFunctionName() === $functionName;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_tutorials record is not found. (functionName: %s)',
                    $functionName
                ),
            );
        }

        return $entities->first();
    }

    /**
     * @return Collection<Entity>
     */
    public function getActivesByType(
        TutorialType $type,
        CarbonImmutable $now,
    ): Collection {
        $typeStr = $type->value;

        return $this->getActiveAll($now)->filter(function (Entity $entity) use ($typeStr) {
            return $entity->getType() === $typeStr;
        });
    }
}
