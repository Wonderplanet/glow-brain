<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstExchangeRewardEntity;
use App\Domain\Resource\Mst\Models\MstExchangeReward as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstExchangeRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * ラインナップIDで報酬一覧を取得
     *
     * @return Collection<MstExchangeRewardEntity>
     */
    public function getByLineupId(string $mstExchangeLineupId): Collection
    {
        return $this->masterRepository
            ->getByColumn(Model::class, 'mst_exchange_lineup_id', $mstExchangeLineupId)
            ->values();
    }
}
