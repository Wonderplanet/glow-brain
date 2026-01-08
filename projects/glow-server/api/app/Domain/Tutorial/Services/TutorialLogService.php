<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Services;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Entities\GachaResultData;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Repositories\LogGachaActionRepository;
use App\Domain\Gacha\Repositories\LogGachaRepository;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Domain\Resource\Mst\Repositories\OprGachaRepository;

/**
 * チュートリアルガシャログ送信サービスクラス
 */
class TutorialLogService
{
    public function __construct(
        // Repositories
        private LogGachaActionRepository $logGachaActionRepository,
        private LogGachaRepository $logGachaRepository,
        private OprGachaRepository $oprGachaRepository,
    ) {
    }

    /**
     * @param string          $usrUserId
     * @param GachaResultData $gachaResultData
     * @return void
     * @throws GameException
     */
    public function sendGachaConfirmLog(
        string $usrUserId,
        GachaResultData $gachaResultData,
    ): void {
        /** @var OprGachaEntity $oprGacha */
        $oprGacha = $this->oprGachaRepository->getById($gachaResultData->getOprGachaId());

        $this->logGachaActionRepository->create(
            $usrUserId,
            $oprGacha->getId(),
            CostType::DIAMOND->value,
            $oprGacha->getMultiDrawCount(),
            0,
            0,
        );

        $this->logGachaRepository->create(
            $usrUserId,
            $oprGacha->getId(),
            $gachaResultData->formatToLog(),
            CostType::DIAMOND->value,
            $oprGacha->getMultiDrawCount(),
        );
    }
}
