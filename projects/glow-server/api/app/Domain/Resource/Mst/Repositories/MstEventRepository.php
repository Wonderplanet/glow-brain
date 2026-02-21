<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstEventEntity as Entity;
use App\Domain\Resource\Mst\Models\MstEvent as Model;
use App\Domain\Resource\Mst\Traits\MstRepositoryTrait;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstEventRepository
{
    use MstRepositoryTrait;

    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * start_atとend_at内の対象IDのレコードを取得
     * @param string $id
     * @param CarbonImmutable $now
     * @return Entity|null
     * @throws GameException
     */
    public function getActiveEvent(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAllActiveEvents($now)->get($id);

        $this->throwMstNotFoundException(
            $isThrowError,
            Model::class,
            $entity,
            [
                'id' => $id,
            ],
        );

        return $entity;
    }

    /**
     * start_atとend_at内の対象レコードを全て取得
     * @param CarbonImmutable $now
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getAllActiveEvents(CarbonImmutable $now): Collection
    {
        return $this->masterRepository->getDayActives(Model::class, $now)
            ->filter(
                function (Entity $entity) use ($now) {
                    return $this->isActiveEntity($entity, $now);
                }
            );
    }
}
