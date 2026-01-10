<?php

declare(strict_types=1);

namespace App\Entities\GachaSimulator;

use App\Domain\Gacha\Entities\GachaBoxInterface;
use App\Domain\Gacha\Enums\GachaPrizeType;
use Illuminate\Support\Collection;

/**
 * ガシャシミュレーションするのに必要なデータと結果をまとめたエンティティ
 */
class GachaSimulateResultEntity
{
    /**
     * ガシャ抽選物ごとの排出回数
     * @var Collection<string, int> string opr_gacha_prizes.id
     */
    private Collection $drawnNums;

    /**
     * @param Collection<GachaBoxInterface> $candidateGachaBoxes 抽選対象全てが入ったガシャ景品コレクション
     * @param Collection<GachaBoxInterface> $drawnGachaBoxes  実際に抽選されたガシャ景品コレクション
     */
    public function __construct(
        private Collection $candidateGachaBoxes,
        private Collection $drawnGachaBoxes,
        private GachaPrizeType $prizeType,
        private int $playNum = 0,
    ) {
        $this->calcAndSetDrawnNums();
    }

    /**
     * @return Collection<GachaBoxInterface>
     */
    public function getCandidateGachaBoxes(): Collection
    {
        return $this->candidateGachaBoxes;
    }


    /**
     * @return Collection<GachaBoxInterface>
     */
    public function getDrawnGachaBoxes(): Collection
    {
        return $this->drawnGachaBoxes;
    }

    public function getPrizeType(): GachaPrizeType
    {
        return $this->prizeType;
    }

    public function getPlayNum(): int
    {
        return $this->playNum;
    }

    public function calcTotalWeight(): int
    {
        return $this->candidateGachaBoxes->sum(function (GachaBoxInterface $gachaBox) {
            return $gachaBox->getWeight();
        });
    }

    private function calcAndSetDrawnNums(): void
    {
        $this->drawnNums = $this->drawnGachaBoxes->countBy(function (GachaBoxInterface $gachaBox) {
            return $gachaBox->getId();
        });
    }

    public function getDrawnNumByOprGachaPrizeId(string $oprGachaPrizeId): int
    {
        return $this->drawnNums->get($oprGachaPrizeId, 0);
    }

    public function getTotalDrawnNum(): int
    {
        return $this->drawnGachaBoxes->count();
    }
}
