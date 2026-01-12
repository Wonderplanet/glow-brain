<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Resource\Mst\Entities\MstQuestBonusUnitEntity;
use App\Domain\Resource\Mst\Repositories\MstQuestBonusUnitRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class QuestBonusUnitService
{
    public function __construct(
        private MstQuestBonusUnitRepository $mstQuestBonusUnitRepository,
    ) {
    }

    /**
     * コインボーナス倍率を取得する
     *
     * @param string $mstQuestId
     * @param Collection $mstUnitIds
     * @param CarbonImmutable $now
     * @return float
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getCoinBonusRate(
        string $mstQuestId,
        Collection $mstUnitIds,
        CarbonImmutable $now
    ): float {
        $mstQuestBonusUnits = $this->mstQuestBonusUnitRepository->getListByMstQuestId($mstQuestId, $now);
        $sumRate = $mstQuestBonusUnits->filter(function (MstQuestBonusUnitEntity $mstQuestBonusUnit) use ($mstUnitIds) {
            return $mstUnitIds->contains($mstQuestBonusUnit->getMstUnitId());
        })->sum(function (MstQuestBonusUnitEntity $mstQuestBonusUnit) {
            return $mstQuestBonusUnit->getCoinBonusRate();
        });
        // 誤差を丸める
        return round($sumRate, 10);
    }
}
