<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Repositories;

use App\Domain\BoxGacha\Entities\BoxGachaDrawPrizeLog;
use App\Domain\BoxGacha\Enums\BoxGachaActionType;
use App\Domain\BoxGacha\Models\LogBoxGachaAction;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use Illuminate\Support\Collection;

class LogBoxGachaActionRepository extends LogModelRepository
{
    protected string $modelClass = LogBoxGachaAction::class;

    /**
     * 抽選ログを作成
     *
     * @param string $usrUserId
     * @param string $mstBoxGachaId
     * @param Collection<int, BoxGachaDrawPrizeLog> $drawPrizeLogs
     * @param int $totalDrawCount
     * @return LogBoxGachaAction
     */
    public function createDrawLog(
        string $usrUserId,
        string $mstBoxGachaId,
        Collection $drawPrizeLogs,
        int $totalDrawCount,
    ): LogBoxGachaAction {
        // BoxGachaDrawPrizeLogをログ保存用の配列形式に変換
        $drawPrizes = $drawPrizeLogs->map(
            fn(BoxGachaDrawPrizeLog $log) => $log->toArray()
        );

        $model = new LogBoxGachaAction();
        $model->setUsrUserId($usrUserId);
        $model->setLogType(BoxGachaActionType::DRAW->value);
        $model->setMstBoxGachaId($mstBoxGachaId);
        $model->setDrawPrizes($drawPrizes);
        $model->setTotalDrawCount($totalDrawCount);
        $this->addModel($model);
        return $model;
    }

    /**
     * リセットログを作成
     *
     * @param string $usrUserId
     * @param string $mstBoxGachaId
     * @return LogBoxGachaAction
     */
    public function createResetLog(
        string $usrUserId,
        string $mstBoxGachaId,
    ): LogBoxGachaAction {
        $model = new LogBoxGachaAction();
        $model->setUsrUserId($usrUserId);
        $model->setLogType(BoxGachaActionType::RESET->value);
        $model->setMstBoxGachaId($mstBoxGachaId);
        $model->setDrawPrizes(collect([]));
        $model->setTotalDrawCount(null);
        $this->addModel($model);
        return $model;
    }
}
