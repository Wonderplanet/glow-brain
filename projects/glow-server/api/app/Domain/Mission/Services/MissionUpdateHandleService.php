<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use Carbon\CarbonImmutable;

class MissionUpdateHandleService
{
    public function __construct(
        private MissionUpdateService $missionUpdateService,
    ) {
    }

    /**
     * トリガーされたミッションの判定と進捗更新を全ミッションタイプに対して行う
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return void
     */
    public function handleAllUpdateTriggeredMissions(
        string $usrUserId,
        CarbonImmutable $now,
    ): void {
        $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
    }
}
