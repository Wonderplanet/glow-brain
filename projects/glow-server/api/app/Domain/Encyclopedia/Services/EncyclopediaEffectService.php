<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Services;

use App\Domain\Encyclopedia\Enums\EncyclopediaEffectType;
use App\Domain\Resource\Entities\EncyclopediaEffect;
use App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaEffectEntity;
use App\Domain\Resource\Mst\Repositories\MstUnitEncyclopediaEffectRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitEncyclopediaRewardRepository;
use Illuminate\Support\Collection;

readonly class EncyclopediaEffectService
{
    public function __construct(
        // Repositories
        private MstUnitEncyclopediaRewardRepository $mstUnitEncyclopediaRewardRepository,
        private MstUnitEncyclopediaEffectRepository $mstUnitEncyclopediaEffectRepository,
    ) {
    }


    /**
     * 図鑑効果データを取得する
     *
     * @param Collection<string> $mstUnitEncyclopediaEffectIds
     * @return EncyclopediaEffect
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getEncyclopediaEffectDataByIds(Collection $mstUnitEncyclopediaEffectIds): EncyclopediaEffect
    {
        $mstEffects = $this->mstUnitEncyclopediaEffectRepository->getByIds($mstUnitEncyclopediaEffectIds);
        return new EncyclopediaEffect(
            $this->calculateUnitEncyclopediaEffectValue($mstEffects, EncyclopediaEffectType::HP),
            $this->calculateUnitEncyclopediaEffectValue($mstEffects, EncyclopediaEffectType::ATTACK_POWER),
            $this->calculateUnitEncyclopediaEffectValue($mstEffects, EncyclopediaEffectType::HEAL)
        );
    }

    /**
     * ユニット図鑑効果値の合計を計算する
     *
     * @param Collection<\App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaEffectEntity> $mstEffects
     * @param EncyclopediaEffectType $effectType
     * @return float
     */
    private function calculateUnitEncyclopediaEffectValue(
        Collection $mstEffects,
        EncyclopediaEffectType $effectType
    ): float {
        return $mstEffects
            ->filter(fn($mstEffect) => $mstEffect->getEffectType() === $effectType->value)
            ->sum(fn($mstEffect) => $mstEffect->getValue());
    }

    /**
     * グレード合算値から、対応するキャラ図鑑ランク効果のマスタデータを取得する
     *
     * @param int $unitTotalGrade
     * @return Collection<MstUnitEncyclopediaEffectEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getMstUnitEncyclopediaEffectsByGrade(int $unitTotalGrade): Collection
    {
        $mstRewards = $this->mstUnitEncyclopediaRewardRepository->getByRankOrLower($unitTotalGrade);
        if ($mstRewards->isEmpty()) {
            return collect();
        }
        $mstRewardIds = $mstRewards->map(function ($mstReward) {
            return $mstReward->getId();
        });
        return $this->mstUnitEncyclopediaEffectRepository->getByMstUnitEncyclopediaRewardIds($mstRewardIds);
    }
}
