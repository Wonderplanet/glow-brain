<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\RarityType;
use App\Domain\Common\Utils\MathUtil;
use App\Domain\Gacha\Entities\GachaBoxInterface;
use App\Dtos\RewardDto;
use App\Entities\GachaSimulator\GachaPrizeSimulationResultEntity;
use App\Entities\GachaSimulator\GachaSimulateResultEntity;
use App\Traits\RewardInfoGetTrait;
use Illuminate\Support\Collection;
use App\Domain\Gacha\Services\GachaService;
use App\Models\Adm\AdmGachaSimulationLog;
use App\Models\Mst\OprGacha;
use App\Models\Mst\OprGachaPrize;

class GachaSimulatorService
{
    use RewardInfoGetTrait;

    protected GachaService $gachaService;

    public function __construct(
    ) {
        $this->gachaService = app()->make(GachaService::class);
    }

    /**
     * 最低排出率からシミュレーション数を判定し返す
     * @param float $minRate 最低排出率
     *
     * @return int シミュレーション数
     */
    public function getSimulationNum(float $minRate): int
    {
        switch (true) {
            case ($minRate >= 0.001 && $minRate <= 0.005):
                return 4300000;
            case ($minRate >= 0.005 && $minRate <= 0.01):
                return 2200000;
            case ($minRate >= 0.01 && $minRate <= 0.05):
                return 430000;
            case ($minRate >= 0.05 && $minRate <= 0.1):
                return 230000;
            case ($minRate >= 0.1 && $minRate <= 0.5):
                return 43000;
            case ($minRate >= 0.5):
                return 23000;
            default:
                return 23000;
        }
    }

    /**
     * @param Collection<GachaBoxInterface> $gachaBoxes
     * @return Collection<string, \App\Entities\RewardInfo>
     */
    private function getRewardInfosByGachaBoxes(Collection $gachaBoxes): Collection
    {
        $rewardDtos = collect();
        foreach ($gachaBoxes as $gachaBox) {
            $rewardDtos->push(
                new RewardDto(
                    $gachaBox->getId(),
                    $gachaBox->getResourceType()->value,
                    $gachaBox->getResourceId(),
                    $gachaBox->getResourceAmount(),
                ),
            );
        }

        return $this->getRewardInfos($rewardDtos);
    }

    /**
     * @return Collection<string, GachaPrizeSimulationResultEntity> key: opr_gacha_prizes.id
     */
    public function createGachaPrizeSimulationResultEntities(
        GachaSimulateResultEntity $gachaSimulateResultEntity,
    ) : Collection {
        $candidateGachaBoxes = $gachaSimulateResultEntity->getCandidateGachaBoxes();
        $totalWeight = $gachaSimulateResultEntity->calcTotalWeight();

        $prizeResources = $this->getRewardInfosByGachaBoxes($candidateGachaBoxes);

        $isDrawn = $gachaSimulateResultEntity->getTotalDrawnNum() > 0;

        $gachaPrizeStatisticsEntities = collect();
        foreach ($candidateGachaBoxes as $candidateGachaBox) {
            $prizeResource = $prizeResources->get($candidateGachaBox->getId());
            if (is_null($prizeResource)) {
                continue;
            }
            $itemName = $prizeResource->getLabelWithAmount();
            $rarity = $prizeResource->getRarity();
            $rate = $this->gachaService->calcProbabilityPercent($candidateGachaBox->getWeight(), $totalWeight);
            $drawnNum = $gachaSimulateResultEntity->getDrawnNumByOprGachaPrizeId($candidateGachaBox->getId());
            $actualEmissionRate = $this->calculateDropRate(
                $drawnNum / $candidateGachaBox->getResourceAmount(),
                $gachaSimulateResultEntity->getPlayNum(),
            );
            $errorRate = $this->calcErrorRate($rate, $actualEmissionRate);
            // シミュレーション未実行の場合は常にfalse
            $rangeCheck = $isDrawn && $this->rangeCheck($actualEmissionRate, $rate);

            $gachaPrizeSimulationResultEntity = new GachaPrizeSimulationResultEntity(
                $candidateGachaBox->getId(),
                $candidateGachaBox->getResourceId(),
                $candidateGachaBox->getResourceType()->value,
                $itemName,
                $rarity,
                $rate,
                $actualEmissionRate,
                $errorRate,
                $rangeCheck,
                $drawnNum,
            );

            $gachaPrizeStatisticsEntities->put($candidateGachaBox->getId(), $gachaPrizeSimulationResultEntity);
        }

        // レアリティ順にソート
        $rarityOrder = RarityType::order()->reverse()->values()->all();
        $gachaPrizeStatisticsEntities = $gachaPrizeStatisticsEntities
            ->sortBy(function (GachaPrizeSimulationResultEntity $entity) use ($rarityOrder) {
            return array_search($entity->getRarity(), $rarityOrder);
        })->values();

        return $gachaPrizeStatisticsEntities;
    }

    public function calcErrorRate(float $rate, float $actualEmissionRate): float
    {
        $errorRate = (($actualEmissionRate - $rate) / $rate) * 100;
        return MathUtil::floorToPrecision($errorRate, 5);
    }

    /**
     * 誤差率の範囲チェック
     * @param float $actualEmissionRate 実際の排出率
     * @param float $rate 排出率
     * @return bool 誤差率が30%以内かどうか true: 異常と判断, false: 許容範囲内
     */
    public function rangeCheck(float $actualEmissionRate, float $rate) : bool
    {
        $errorRate = $this->calcErrorRate($rate, $actualEmissionRate);
        return (abs($errorRate) > 30);
    }

    /**
     * シミュレーション結果に誤差率エラーがあるかどうか
     * @param Collection<GachaPrizeSimulationResultEntity> $gachaPrizeStatisticsEntities
     * @return bool
     */
    public function hasRangeCheckError(Collection $gachaPrizeStatisticsEntities): bool
    {
        foreach ($gachaPrizeStatisticsEntities as $entity) {
            if ($entity->isRangeCheckError()) {
                return true;
            }
        }
        return false;
    }

    /**
     * 実際の排出率の計算
     * @param float $probability 排出率
     * @param int $playNum シミュレーション数
     * @return float 実際の排出率
     */
    private function calculateDropRate(float $probability, int $playNum): float
    {
        return MathUtil::floorToPrecision(($probability / $playNum) * 100, 3);
    }

    /**
     * ガシャのマスタデータの変動検知のためにハッシュを生成する
     * @param Collection<OprGachaPrize> $oprGachaPrizes
     * @return string
     */
    public function makeMstGachaDataHash(
        OprGacha $oprGacha,
        Collection $oprGachaPrizes,
    ): string {
        $data = [];

        $data['opr_gacha'] = $oprGacha->formatToSimulationCheckData();

        // idでソート
        $data['opr_gacha_prizes'] = $oprGachaPrizes->map(function (OprGachaPrize $oprGachaPrize) {
            return $oprGachaPrize->formatToSimulationCheckData();
        })->sortBy(function ($formattedOprGachaPrize) {
            return $formattedOprGachaPrize['id'];
        })->values()->all();

        return md5(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 前回のシミュレーション時のハッシュと比較して、ガシャデータに変更があったかチェックして、結果をセットする
     *
     * @param Collection<OprGachaPrize> $oprGachaPrizes
     * @return bool
     */
    public function checkAndSetChangedMstDataHash(
        OprGacha $oprGacha,
        Collection $oprGachaPrizes,
        ?AdmGachaSimulationLog $admGachaSimulationLog,
    ): void {
        $mstHash = $this->makeMstGachaDataHash($oprGacha, $oprGachaPrizes);

        $oprGacha->changed_mst_data_hash = ($admGachaSimulationLog?->mst_gacha_data_hash !== $mstHash);
    }

    /**
     * @param Collection<OprGacha> $oprGachas
     * @return Collection<string, Collection<OprGachaPrize>>
     *  key: opr_gachas.id, value: Collection<OprGachaPrize>
     */
    public function getOprGachaPrizesByOprGachas(Collection $oprGachas): Collection
    {
        $prizeGroupIds = $oprGachas->mapWithKeys(function (OprGacha $oprGacha) {
            /** @var \App\Models\Mst\OprGacha $oprGacha */
            $addPrizeGroupIds = $oprGacha->getPrizeGroupIds();
            return $addPrizeGroupIds->mapWithKeys(function ($id) {
                return [$id => $id];
            });
        });

        $oprGachaPrizesByGroupId = OprGachaPrize::query()
            ->whereIn('group_id', $prizeGroupIds)
            ->get()
            ->groupBy('group_id');

        $oprGachaPrizesByOprGachaId = collect();
        foreach ($oprGachas as $oprGacha) {
            $oprGachaPrizes = collect();
            $targetPrizeGroupIds = $oprGacha->getPrizeGroupIds();
            foreach ($targetPrizeGroupIds as $prizeGroupId) {
                $oprGachaPrizes = $oprGachaPrizes->concat(
                    $oprGachaPrizesByGroupId->get($prizeGroupId) ?? collect(),
                );
            }
            $oprGachaPrizesByOprGachaId->put($oprGacha->id, $oprGachaPrizes);
        }

        return $oprGachaPrizesByOprGachaId;
    }
}
