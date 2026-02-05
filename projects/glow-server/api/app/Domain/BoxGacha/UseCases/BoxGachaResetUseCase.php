<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\UseCases;

use App\Domain\BoxGacha\Repositories\LogBoxGachaActionRepository;
use App\Domain\BoxGacha\Repositories\UsrBoxGachaRepository;
use App\Domain\BoxGacha\Services\BoxGachaService;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Mst\Repositories\MstBoxGachaRepository;
use App\Http\Responses\ResultData\BoxGachaResetResultData;

class BoxGachaResetUseCase
{
    use UseCaseTrait;

    public function __construct(
        private BoxGachaService $boxGachaService,
        private MstBoxGachaRepository $mstBoxGachaRepository,
        private UsrBoxGachaRepository $usrBoxGachaRepository,
        private LogBoxGachaActionRepository $logBoxGachaActionRepository,
        private Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $usr
     * @param string $mstBoxGachaId
     * @param int $currentBoxLevel
     * @return BoxGachaResetResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $usr,
        string $mstBoxGachaId,
        int $currentBoxLevel
    ): BoxGachaResetResultData {
        $now = $this->clock->now();

        // マスターデータ取得
        $mstBoxGacha = $this->mstBoxGachaRepository->getById($mstBoxGachaId, true);

        // 期間チェック
        $this->boxGachaService->validateBoxGachaPeriod($mstBoxGacha, $now);

        // ユーザーデータ取得
        // 任意のタイミングでリセット可能で、１箱目を１度も引かずにリセットするケースもある為ない場合は新規作成
        $usrBoxGacha = $this->usrBoxGachaRepository->getOrCreate($usr->getUsrUserId(), $mstBoxGachaId);

        // 箱レベルの整合性チェック
        $this->boxGachaService->validateCurrentBoxLevel($currentBoxLevel, $usrBoxGacha);

        // リセット実行（条件無しでリセット可能）
        $this->boxGachaService->resetBox($usrBoxGacha, $mstBoxGacha);

        // ユーザーデータ保存
        $this->usrBoxGachaRepository->syncModel($usrBoxGacha);

        // ログ作成
        $this->logBoxGachaActionRepository->createResetLog(
            $usr->getUsrUserId(),
            $mstBoxGachaId,
        );

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new BoxGachaResetResultData($usrBoxGacha);
    }
}
