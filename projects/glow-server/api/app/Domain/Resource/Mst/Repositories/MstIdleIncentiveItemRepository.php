<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstIdleIncentiveItemEntity as Entity;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveItem as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstIdleIncentiveItemRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @param string $mstIdleIncentiveItemGroupId
     * @return Collection<Entity>
     */
    public function getByMstIdleIncentiveItemGroupId(string $mstIdleIncentiveItemGroupId): Collection
    {
        return $this->masterRepository->getByColumn(
            Model::class,
            'mst_idle_incentive_item_group_id',
            $mstIdleIncentiveItemGroupId,
        );
    }
}
