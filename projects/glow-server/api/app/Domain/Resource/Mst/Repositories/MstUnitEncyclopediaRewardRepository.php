<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaRewardEntity;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

readonly class MstUnitEncyclopediaRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<MstUnitEncyclopediaRewardEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(MstUnitEncyclopediaReward::class);
    }


    /**
     * @param Collection $ids
     * @return Collection<MstUnitEncyclopediaRewardEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByIds(Collection $ids): Collection
    {
        $entities = $this->getAll()->filter(function (MstUnitEncyclopediaRewardEntity $entity) use ($ids) {
            return $ids->containsStrict($entity->getId());
        });
        return $entities->values();
    }

    /**
     * 指定ランク以下のデータを全て取得する
     * ランクはユーザーが所持するユニットの合計grade
     *
     * @param int $unitEncyclopediaRank
     * @return Collection<MstUnitEncyclopediaRewardEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByRankOrLower(int $unitEncyclopediaRank): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($unitEncyclopediaRank) {
            /** @var MstUnitEncyclopediaRewardEntity $entity */
            return $entity->getUnitEncyclopediaRank() <= $unitEncyclopediaRank;
        });
    }
}
