<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Entities\GachaLotteryBox;
use App\Domain\Gacha\Entities\GachaRarityProbability;
use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Gacha\Services\StepupGachaService;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Entities\GachaSimulator\GachaSimulateResultEntity;
use App\Http\Responses\Data\StepUpGachaPrizeInfoData;
use App\Models\Mst\OprGacha;
use App\Models\Mst\OprStepupGachaStep;
use App\Traits\NotificationTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AdminGachaService
{
    use NotificationTrait;

    public function __construct(
        private readonly GachaService $gachaService,
        private readonly StepupGachaService $stepupGachaService,
    ) {
    }

    /**
     * ガシャ抽選
     *
     * @param GachaLotteryBox                    $gachaLotteryBoxData
     * @param int                                $drawCount
     * @param GachaPrizeType                     $prizeType
     * @throws GameException
     */
    public function simulateDraw(
        string $oprGachaId,
        GachaLotteryBox $gachaLotteryBoxData,
        int $drawCount,
        GachaPrizeType $prizeType = GachaPrizeType::REGULAR
    ): GachaSimulateResultEntity {
        $drawnGachaBoxes = collect();

        $gachaBoxes = $this->getGachaBoxesByPrizeType($gachaLotteryBoxData, $prizeType);

        // ログ出力の間隔を設定（10%毎、最小100,000回毎）
        $logInterval = max(100000, (int)($drawCount * 0.1));

        Log::info("ガチャシミュレーション開始", [
            'total_count' => $drawCount,
            'prize_type' => $prizeType->value,
            'log_interval' => $logInterval
        ]);

        for ($i = 0; $i < $drawCount; $i++) {
            $drawnGachaBoxes->push($this->gachaService->lottery($gachaBoxes));

            // 一定間隔で進捗をログ出力
            if (($i + 1) % $logInterval === 0) {
                $progress = round((($i + 1) / $drawCount) * 100, 1);
                Log::info("ガチャシミュレーション進行中", [
                    'current_count' => $i + 1,
                    'total_count' => $drawCount,
                    'progress_percent' => $progress,
                    'prize_type' => $prizeType->value
                ]);
            }
        }

        Log::info("ガチャシミュレーション完了", [
            'total_count' => $drawCount,
            'prize_type' => $prizeType->value
        ]);

        return new GachaSimulateResultEntity($gachaBoxes, $drawnGachaBoxes, $prizeType, $drawCount);
    }

    /**
     * 抽選枠タイプを指定して、抽選物候補コレクションを取得
     *
     * @return Collection<\App\Domain\Gacha\Entities\GachaBoxInterface>
     */
    public function getGachaBoxesByPrizeType(
        GachaLotteryBox $gachaLotteryBoxData,
        GachaPrizeType $prizeType = GachaPrizeType::REGULAR,
    ): Collection {
        return match ($prizeType) {
            GachaPrizeType::PICKUP => $this->gachaService->generatePickupBox($gachaLotteryBoxData->getRegularLotteryBox()),
            GachaPrizeType::MAX_RARITY => $this->gachaService->generateMaxRarityBox($gachaLotteryBoxData->getRegularLotteryBox()),
            GachaPrizeType::FIXED => $gachaLotteryBoxData->getFixedLotteryBox() ?? collect(),
            default => $gachaLotteryBoxData->getRegularLotteryBox(),
        };
    }

    public function getGachaLotteryBox(
        OprGachaEntity $oprGacha,
        ?int $stepNumber = null,
    ): GachaLotteryBox {
        if ($oprGacha->isStepup()) {
            $oprStepupGachaStep = OprStepupGachaStep::query()
                ->where('opr_gacha_id', $oprGacha->getId())
                ->where('step_number', $stepNumber ?? 1)
                ->first();
            if (is_null($oprStepupGachaStep)) {
                $this->sendDangerNotification(
                    "ステップアップガシャのステップ情報が見つかりません。",
                    sprintf("ガシャID: %s, ステップ: %d", $oprGacha->getId(), $stepNumber),
                );
                throw new \RuntimeException(
                    sprintf("ステップアップガシャのステップ情報が見つかりません。ガシャID: %s, ステップ: %d", $oprGacha->getId(), $stepNumber)
                );
            }
            return $this->stepupGachaService->getLotteryBox($oprGacha, $oprStepupGachaStep->toEntity());
        } else {
            return $this->gachaService->getGachaLotteryBox($oprGacha);
        }
    }

    /**
     * @return array<mixed> GachaRarityProbabilityのformatToResponse配列
     */
    public function getGachaRarityProbabilitiesArray(
        OprGacha $oprGacha,
        int $stepNumber,
    ): array {
        if ($oprGacha->toEntity()->isStepup()) {
            // StepUpガシャの場合
            $stepUpGachaPrizeInfoData = $this->stepupGachaService->getPrizes($oprGacha->toEntity())
                ->filter(function (StepUpGachaPrizeInfoData $data) use ($stepNumber) {
                    return $data->getStepNumber() === $stepNumber;
                })->first();
            if (is_null($stepUpGachaPrizeInfoData)) {
                $this->sendDangerNotification(
                    "ステップアップガシャのステップ排出率情報が見つかりません。",
                    sprintf("ガシャID: %s, ステップ: %d", $oprGacha->id, $stepNumber),
                );
                throw new \RuntimeException(
                    sprintf("ステップアップガシャのステップ排出率情報が見つかりません。ガシャID: %s, ステップ: %d", $oprGacha->id, $stepNumber)
                );
            }

            return $stepUpGachaPrizeInfoData->getRarityProbabilities()
                ->map(function (GachaRarityProbability $probability) {
                    return $probability->formatToResponse();
                })
                ->toArray();
        }
        // Standardガシャの場合
        return $this->gachaService->generateGachaProbability($oprGacha->id)->formatToResponse()['rarityProbabilities'];
    }

    /**
     * @param OprGacha $oprGacha
     * @param OprStepupGachaStep $oprStepupGachaStep
     * @return Collection<string>
     */
    public function getPrizeGroupIds(OprGacha $oprGacha, OprStepupGachaStep $oprStepupGachaStep): Collection
    {
        $prizeGroupIds = collect();

        if ($oprStepupGachaStep->useCommonPrizeGroupId()) {
            $prizeGroupIds = $prizeGroupIds->push($oprGacha->prize_group_id);
        } else {
            $prizeGroupIds = $prizeGroupIds->push($oprStepupGachaStep->prize_group_id);
        }

        if ($oprStepupGachaStep->hasFixedPrizeGroup()) {
            $prizeGroupIds = $prizeGroupIds->push($oprStepupGachaStep->fixed_prize_group_id);
        }

        return $prizeGroupIds;
    }
}
