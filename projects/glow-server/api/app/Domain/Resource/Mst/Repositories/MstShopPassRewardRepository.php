<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\MstShopPassReward as Model;
use App\Domain\Shop\Enums\PassRewardType;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstShopPassRewardRepository
{
    public function __construct(
        private readonly MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstShopPassRewardEntity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param string $mstShopPassId
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstShopPassRewardEntity>
     */
    public function getImmediatelyMstShopPassId(string $mstShopPassId): Collection
    {
        return $this->getAll()
            ->filter(
                function ($entity) use ($mstShopPassId) {
                    return $entity->getMstShopPassId() === $mstShopPassId
                        && $entity->getPassRewardType() === PassRewardType::IMMEDIATELY->value;
                }
            );
    }

    /**
     * 毎日報酬のリワードデータの取得
     * @param string $mstShopPassId
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstShopPassRewardEntity>
     */
    public function getDailyMstShopPassId(string $mstShopPassId): Collection
    {
        return $this->getAll()
            ->filter(
                function ($entity) use ($mstShopPassId) {
                    return $entity->getMstShopPassId() === $mstShopPassId
                        && $entity->getPassRewardType() === PassRewardType::DAILY->value;
                }
            );
    }

    public function getDailyMstShopPassIds(Collection $mstShopPassIds): Collection
    {
        $targetMstShopPassIds = $mstShopPassIds->unique();

        $response = [];
        foreach ($targetMstShopPassIds as $targetMstShopPassId) {
            $mstReward = $this->getDailyMstShopPassId($targetMstShopPassId);
            $response[$targetMstShopPassId] = $mstReward;
        }

        return collect($response);
    }
}
