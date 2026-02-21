<?php

declare(strict_types=1);

namespace App\Domain\Gacha\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Gacha\Services\StepupGachaService;
use App\Http\Responses\ResultData\GachaPrizeResultData;

class GachaPrizeUseCase
{
    use UseCaseTrait;

    public function __construct(
        private GachaService $gachaService,
        private StepupGachaService $stepupGachaService,
    ) {
    }

    /**
     * @param string $oprGachaId
     * @param CurrentUser|null $user
     * @return GachaPrizeResultData
     * @throws \Throwable
     */
    public function exec(string $oprGachaId, ?CurrentUser $user = null): GachaPrizeResultData
    {
        $oprGacha = $this->gachaService->getOprGacha($oprGachaId);

        // 通常の排出率を取得（opr_gachas.prize_group_idを使用）
        $gachaProbabilityData = $this->gachaService->generateGachaProbability($oprGachaId);

        // ステップアップガシャの場合、各ステップの排出率を追加
        $stepupGachaPrizes = $this->stepupGachaService->getPrizes($oprGacha);

        $this->processWithoutUserTransactionChanges();
        return new GachaPrizeResultData($gachaProbabilityData, $stepupGachaPrizes);
    }
}
