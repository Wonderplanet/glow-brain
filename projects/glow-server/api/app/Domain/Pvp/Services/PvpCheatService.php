<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Cheat\Delegators\CheatDelegator;
use App\Domain\Cheat\Enums\CheatContentType;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use Carbon\CarbonImmutable;

class PvpCheatService
{
    public function __construct(
        protected readonly UsrPvpRepository $usrPvpRepository,
        protected readonly CheatDelegator $cheatDelegator,
    ) {
    }

    /**
     * チートチェック処理
     *
     * @param UsrPvpInterface $usrPvp
     * @param SysPvpSeasonEntity $sysPvpSeason
     * @param CheatContentType $cheatContentType
     * @param CarbonImmutable $now
     * @param callable $checkFunction
     * @param mixed ...$params
     * @return void
     */
    protected function handleCheatCheck(
        UsrPvpInterface $usrPvp,
        SysPvpSeasonEntity $sysPvpSeason,
        CheatContentType $cheatContentType,
        CarbonImmutable $now,
        callable $checkFunction,
        mixed ...$params
    ): void {
        /** @var \App\Domain\Resource\Mst\Entities\MstCheatSettingEntity|null $mstCheatSetting */
        $mstCheatSetting = $checkFunction(
            $usrPvp->getUsrUserId(),
            $sysPvpSeason->getId(),
            $cheatContentType->value,
            $now,
            ...$params
        );

        if (!is_null($mstCheatSetting) && $mstCheatSetting->isExcludedRanking()) {
            $usrPvp->setIsExcludedRanking(true);
            $this->usrPvpRepository->syncModel($usrPvp);
        }
    }
}
