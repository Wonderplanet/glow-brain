<?php

declare(strict_types=1);

namespace App\Domain\Mission\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Mission\Services\MissionFetchService;
use App\Http\Responses\ResultData\MissionEventUpdateAndFetchResultData;

/**
 * 実装当時は即時達成処理があったため、updateAndFetchという名前にしたが、
 * イベントミッションに即時達成判定は不要なので、
 * GETメソッドと同じように、processWithoutUserTransactionChangesを使用しています。
 */
class MissionEventUpdateAndFetchUseCase
{
    use UseCaseTrait;

    public function __construct(
        private MissionFetchService $missionFetchService,
        //other
        private Clock $clock,
    ) {
    }

    public function exec(CurrentUser $user): MissionEventUpdateAndFetchResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        // ミッション進捗データ取得
        // ここでは、リセットなどのモデル値のDB更新は行わない
        $eventCategoryFetchStatusData = $this->missionFetchService
            ->getMissionEventFetchStatusWhenFetchAll($usrUserId, $now);
        $eventFetchStatusData = $eventCategoryFetchStatusData->getEventMissionFetchStatusData();
        $eventDailyFetchStatusData = $eventCategoryFetchStatusData->getEventDailyMissionFetchStatusData();

        $this->processWithoutUserTransactionChanges();

        return new MissionEventUpdateAndFetchResultData(
            $eventFetchStatusData->getUsrMissionStatusDataList(),
            $eventDailyFetchStatusData->getUsrMissionStatusDataList(),
        );
    }
}
