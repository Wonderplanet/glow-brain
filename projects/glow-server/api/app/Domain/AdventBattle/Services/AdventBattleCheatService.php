<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\Cheat\Delegators\CheatDelegator;
use App\Domain\Cheat\Enums\CheatContentType;
use Carbon\CarbonImmutable;

/**
 * 降臨バトル開始時のチート制御サービス
 */
abstract class AdventBattleCheatService
{
    public function __construct(
        // Repositories
        protected readonly UsrAdventBattleRepository $usrAdventBattleRepository,
        // Delegator
        protected readonly CheatDelegator $cheatDelegator,
    ) {
    }

    /**
     * チートチェック処理
     *
     * @param UsrAdventBattleInterface $usrAdventBattle
     * @param CheatContentType $cheatContentType
     * @param CarbonImmutable $now
     * @param callable $checkFunction
     * @param mixed ...$params
     * @return void
     */
    protected function handleCheatCheck(
        UsrAdventBattleInterface $usrAdventBattle,
        CheatContentType $cheatContentType,
        CarbonImmutable $now,
        callable $checkFunction,
        mixed ...$params
    ): void {
        /** @var \App\Domain\Resource\Mst\Entities\MstCheatSettingEntity|null $mstCheatSetting */
        $mstCheatSetting = $checkFunction(
            $usrAdventBattle->getUsrUserId(),
            $usrAdventBattle->getMstAdventBattleId(),
            $cheatContentType->value,
            $now,
            ...$params
        );

        if (!is_null($mstCheatSetting) && $mstCheatSetting->isExcludedRanking()) {
            $usrAdventBattle->setIsExcludedRanking(true);
            $this->usrAdventBattleRepository->syncModel($usrAdventBattle);
        }
    }
}
