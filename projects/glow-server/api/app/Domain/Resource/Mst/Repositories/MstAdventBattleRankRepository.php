<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\MstAdventBattleRank as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstAdventBattleRankRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @param string $adventBattleId
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstAdventBattleRankEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByAdventBattleId(string $adventBattleId): Collection
    {
        return $this->masterRepository->getByColumn(Model::class, 'mst_advent_battle_id', $adventBattleId);
    }
}
