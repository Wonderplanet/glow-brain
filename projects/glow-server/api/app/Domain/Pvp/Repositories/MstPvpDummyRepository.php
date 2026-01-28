<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Repositories;

use App\Domain\Resource\Mst\Entities\MstPvpDummyEntity;
use App\Domain\Resource\Mst\Models\MstPvpDummy;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstPvpDummyRepository
{
    public function __construct(
        private readonly MasterRepository $masterRepository,
    ) {
    }

    public function getMstPvpDummyByDummyUserId(string $mstDummyUserId): MstPvpDummyEntity
    {
        $getAll = $this->masterRepository->get(MstPvpDummy::class);
        $entities = $getAll->filter(function ($entity) use ($mstDummyUserId) {
            return $entity->getMstDummyUserId() === $mstDummyUserId;
        });
        return $entities->first();
    }

    /**
     * @param string $rankClassType
     * @param int $rankClassLevel
     * @return Collection<MstPvpDummyEntity>
     */
    public function getDummyUsersByRankTypeAndLevel(
        string $rankClassType,
        int $rankClassLevel
    ): Collection {
        return $this->masterRepository->getByColumns(MstPvpDummy::class, [
            'rank_class_type' => $rankClassType,
            'rank_class_level' => $rankClassLevel,
        ]);
    }
}
