<?php

declare(strict_types=1);

namespace App\Domain\DailyBonus\Services;

use App\Domain\DailyBonus\Models\UsrComebackBonusProgressInterface;
use App\Domain\DailyBonus\Repositories\UsrComebackBonusProgressRepository;
use App\Domain\Resource\Mst\Repositories\MstComebackBonusScheduleRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ComebackBonusFetchService
{
    public function __construct(
        // Repository
        private MstComebackBonusScheduleRepository $mstComebackBonusScheduleRepository,
        private UsrComebackBonusProgressRepository $usrComebackBonusProgressRepository,
    ) {
    }

    /**
     * イベントデイリーボーナスの進捗情報を取得
     *
     * @return Collection<\App\Domain\DailyBonus\Models\UsrComebackBonusProgressInterface>
     */
    public function fetchProgresses(
        string $usrUserId,
        CarbonImmutable $now,
    ): Collection {
        $mstScheduleIds = $this->mstComebackBonusScheduleRepository->getActiveMapAll($now)->keys();
        if ($mstScheduleIds->isEmpty()) {
            return collect();
        }
        // 期限内のイベントログボの進捗
        return $this->usrComebackBonusProgressRepository->getByMstScheduleIds($usrUserId, $mstScheduleIds)
            ->keyBy(function (UsrComebackBonusProgressInterface $usrMission) {
                return $usrMission->getMstScheduleId();
            });
    }
}
