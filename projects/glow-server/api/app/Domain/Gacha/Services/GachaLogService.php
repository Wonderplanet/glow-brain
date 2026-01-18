<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services;

use App\Domain\Gacha\Entities\GachaResultData;
use App\Domain\Gacha\Repositories\LogGachaRepository;

class GachaLogService
{
    public function __construct(
        // Repository
        private LogGachaRepository $logGachaRepository,
    ) {
    }

    public function sendGachaLog(
        string $usrUserId,
        string $oprGachaId,
        GachaResultData $gachaResultData,
        string $costType,
        int $drawCount,
    ): void {
        $this->logGachaRepository->create(
            $usrUserId,
            $oprGachaId,
            $gachaResultData->formatToLog(),
            $costType,
            $drawCount,
        );
    }
}
