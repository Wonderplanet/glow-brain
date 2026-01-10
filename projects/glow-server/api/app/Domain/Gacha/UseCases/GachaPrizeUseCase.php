<?php

declare(strict_types=1);

namespace App\Domain\Gacha\UseCases;

use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Gacha\Services\GachaService;
use App\Http\Responses\ResultData\GachaPrizeResultData;

class GachaPrizeUseCase
{
    use UseCaseTrait;

    public function __construct(
        private GachaService $gachaService,
    ) {
    }

    /**
     * @param string $oprGachaId
     * @return GachaPrizeResultData
     * @throws \Throwable
     */
    public function exec(string $oprGachaId): GachaPrizeResultData
    {
        $gachaProbabilityData = $this->gachaService->generateGachaProbability($oprGachaId);
        $this->processWithoutUserTransactionChanges();
        return new GachaPrizeResultData($gachaProbabilityData);
    }
}
