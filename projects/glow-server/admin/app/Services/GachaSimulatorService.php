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
use App\Models\Mst\OprStepupGachaStep;

class GachaSimulatorService
{
    use RewardInfoGetTrait;

    public function __construct(
        private GachaService $gachaService,
        private AdminGachaService $adminGachaService,
    ) {
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
                $drawnNum,
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
     *
     * ステップアップガシャの場合は、全ステップのPrizesを含めてハッシュ化する
     *
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
     * 対象ガシャに紐づく全てのOprGachaPrizesを取得して、ガシャIDごとにまとめて返す
     *
     * @param Collection<OprGacha> $oprGachas
     * @return Collection<string, Collection<OprGachaPrize>>
     *  key: opr_gachas.id, value: Collection<OprGachaPrize>
     */
    public function getOprGachaPrizesByOprGachas(Collection $oprGachas): Collection
    {
        [$standardOprGachaPrizesByOprGachaId, $stepupOprGachaPrizesByOprGachaId] = $oprGachas
            ->partition(function (OprGacha $oprGacha) {
                return !$oprGacha->toEntity()->isStepup();
            });

        $oprGachaPrizesByOprGachaId = collect();

        $targetOprGachaPrizes = $this->getStandardOprGachaPrizesByOprGachas($standardOprGachaPrizesByOprGachaId);
        $oprGachaPrizesByOprGachaId = $oprGachaPrizesByOprGachaId->union($targetOprGachaPrizes);

        $targetOprGachaPrizes = $this->getStepupOprGachaPrizesByOprGachas($stepupOprGachaPrizesByOprGachaId);
        $oprGachaPrizesByOprGachaId = $oprGachaPrizesByOprGachaId->union(
            $targetOprGachaPrizes->map(function (Collection $oprGachaPrizesByStepNumber) {
                return $oprGachaPrizesByStepNumber->flatten();
            }),
        );

        return $oprGachaPrizesByOprGachaId;
    }

    /**
     * getOprGachaPrizesByOprGachasの単体版
     *
     * @param OprGacha $oprGacha
     * @param ?int $stepNumber ステップアップガシャでステップ番号が指定された場合、そのステップのPrizesのみを返す。未指定の場合は全ステップのPrizesを返す
     * @return Collection<OprGachaPrize>
     */
    public function getOprGachaPrizesByOprGacha(OprGacha $oprGacha, ?int $stepNumber = null): Collection
    {
        if ($oprGacha->toEntity()->isStepup()) {
            $oprGachaPrizesByOprGachaId = $this->getStepupOprGachaPrizesByOprGachas(collect([$oprGacha]));
            // ステップ番号が指定されていればそのステップのPrizesのみを返す
            if (!is_null($stepNumber)) {
                return $oprGachaPrizesByOprGachaId
                    ->get($oprGacha->id, collect())
                    ->get($stepNumber, collect());
            }
            return $oprGachaPrizesByOprGachaId->get($oprGacha->id, collect())->flatten();
        } else {
            $oprGachaPrizesByOprGachaId = $this->getStandardOprGachaPrizesByOprGachas(collect([$oprGacha]));
            return $oprGachaPrizesByOprGachaId->get($oprGacha->id, collect());
        }
    }

    /**
     * @param Collection<OprGacha> $oprGachas
     * @return Collection<string, Collection<OprGachaPrize>>
     *  key: opr_gachas.id, value: Collection<OprGachaPrize>
     */
    private function getStandardOprGachaPrizesByOprGachas(Collection $oprGachas): Collection
    {
        $prizeGroupIds = $oprGachas->mapWithKeys(function (OprGacha $oprGacha) {
            /** @var \App\Models\Mst\OprGacha $oprGacha */
            $addPrizeGroupIds = $oprGacha->getPrizeGroupIds();
            return $addPrizeGroupIds
                ->filter(fn($id) => !is_null($id))  // nullを除外
                ->mapWithKeys(function ($id) {
                    return [$id => $id];
                });
        });

        $oprGachaPrizesByGroupId = OprGachaPrize::query()
            ->whereIn('group_id', $prizeGroupIds)
            ->get()
            ->groupBy('group_id')
            ->toBase();

        $oprGachaPrizesByOprGachaId = collect();
        foreach ($oprGachas as $oprGacha) {
            $targetPrizeGroupIds = $oprGacha->getPrizeGroupIds();
            $oprGachaPrizes = $oprGachaPrizesByGroupId->only($targetPrizeGroupIds->values()->toArray())->flatten();
            $oprGachaPrizesByOprGachaId->put($oprGacha->id, $oprGachaPrizes);
        }

        return $oprGachaPrizesByOprGachaId;
    }

    /**
     * ガシャIDごとに各ステップのOprGachaPrizesを取得する
     *
     * @param Collection<OprGacha> $oprGachas
     * @return Collection<string, Collection<int, Collection<OprGachaPrize>>>
     *  key: opr_gachas.id, value: Collection<step_number, Collection<OprGachaPrize>>
     */
    private function getStepupOprGachaPrizesByOprGachas(Collection $oprGachas): Collection
    {
        if ($oprGachas->isEmpty()) {
            return collect();
        }

        $oprGachas = $oprGachas->keyBy(function (OprGacha $oprGacha) {
            return $oprGacha->id;
        });

        $oprGachaIds = $oprGachas->keys();

        $oprStepupGachaSteps = OprStepupGachaStep::query()
            ->whereIn('opr_gacha_id', $oprGachaIds)
            ->get();

        $prizeGroupIds = collect();
        foreach ($oprStepupGachaSteps as $oprStepupGachaStep) {
            /** @var \App\Models\Mst\OprStepupGachaStep $oprStepupGachaStep */
            $oprGacha = $oprGachas->get($oprStepupGachaStep->opr_gacha_id);
            if (is_null($oprGacha)) {
                continue;
            }
            $prizeGroupIds = $prizeGroupIds->merge(
                $this->adminGachaService->getPrizeGroupIds(
                    $oprGacha,
                    $oprStepupGachaStep,
                ),
            );
        }
        $prizeGroupIds = $prizeGroupIds->unique();

        $oprStepupGachaStepsByOprGachaId = $oprStepupGachaSteps
            ->groupBy(function (OprStepupGachaStep $oprStepupGachaStep) {
                return $oprStepupGachaStep->opr_gacha_id;
            })->toBase();

        $oprGachaPrizesByGroupId = OprGachaPrize::query()
            ->whereIn('group_id', $prizeGroupIds)
            ->get()
            ->groupBy('group_id')
            ->toBase();

        $oprGachaPrizesByOprGachaIdAndStepNumber = collect();
        foreach ($oprGachas as $oprGacha) {
            $oprStepupGachaSteps = $oprStepupGachaStepsByOprGachaId->get($oprGacha->id, collect());
            $oprGachaPrizesByStepNumber = collect();
            foreach ($oprStepupGachaSteps as $oprStepupGachaStep) {
                $targetPrizeGroupIds = $this->adminGachaService->getPrizeGroupIds(
                    $oprGacha,
                    $oprStepupGachaStep,
                );
                if ($targetPrizeGroupIds->isEmpty()) {
                    continue;
                }

                $oprGachaPrizesByStepNumber = $oprGachaPrizesByStepNumber->put(
                    $oprStepupGachaStep->step_number,
                    $oprGachaPrizesByGroupId->only($targetPrizeGroupIds->values()->toArray())->flatten(),
                );
            }
            $oprGachaPrizesByOprGachaIdAndStepNumber->put($oprGacha->id, $oprGachaPrizesByStepNumber);
        }

        return $oprGachaPrizesByOprGachaIdAndStepNumber;
    }
}
