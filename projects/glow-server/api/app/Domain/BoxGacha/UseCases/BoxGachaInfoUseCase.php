<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\UseCases;

use App\Domain\BoxGacha\Repositories\UsrBoxGachaRepository;
use App\Domain\BoxGacha\Services\BoxGachaService;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Mst\Repositories\MstBoxGachaRepository;
use App\Http\Responses\ResultData\BoxGachaInfoResultData;

class BoxGachaInfoUseCase
{
    use UseCaseTrait;

    public function __construct(
        private BoxGachaService $boxGachaService,
        private MstBoxGachaRepository $mstBoxGachaRepository,
        private UsrBoxGachaRepository $usrBoxGachaRepository,
        private Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $usr
     * @param string $mstBoxGachaId
     * @return BoxGachaInfoResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $usr,
        string $mstBoxGachaId
    ): BoxGachaInfoResultData {
        $now = $this->clock->now();

        // マスターデータ取得
        $mstBoxGacha = $this->mstBoxGachaRepository->getById($mstBoxGachaId, true);

        // 期間チェック
        $this->boxGachaService->validateBoxGachaPeriod($mstBoxGacha, $now);

        // ユーザーデータ取得（なければモデルのみ作成、DB保存しない）
        $usrBoxGacha = $this->usrBoxGachaRepository->getOrMake($usr->getUsrUserId(), $mstBoxGachaId);

        return new BoxGachaInfoResultData(
            $usrBoxGacha
        );
    }
}
