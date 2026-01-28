<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstExchangeCostEntity;
use App\Domain\Resource\Mst\Models\MstExchangeCost as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstExchangeCostRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * ラインナップIDでコスト一覧を取得
     *
     * @return Collection<MstExchangeCostEntity>
     */
    public function getByLineupId(string $mstExchangeLineupId): Collection
    {
        return $this->masterRepository
            ->getByColumn(Model::class, 'mst_exchange_lineup_id', $mstExchangeLineupId)
            ->values();
    }
}
