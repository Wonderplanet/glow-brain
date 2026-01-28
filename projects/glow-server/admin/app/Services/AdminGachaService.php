<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Entities\GachaLotteryBox;
use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Services\GachaService;
use App\Entities\GachaSimulator\GachaSimulateResultEntity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AdminGachaService extends GachaService
{
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
            $drawnGachaBoxes->push($this->lottery($gachaBoxes));

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
            GachaPrizeType::PICKUP => $this->generatePickupBox($gachaLotteryBoxData->getRegularLotteryBox()),
            GachaPrizeType::MAX_RARITY => $this->generateMaxRarityBox($gachaLotteryBoxData->getRegularLotteryBox()),
            GachaPrizeType::FIXED => $gachaLotteryBoxData->getFixedLotteryBox() ?? collect(),
            default => $gachaLotteryBoxData->getRegularLotteryBox(),
        };
    }
}
