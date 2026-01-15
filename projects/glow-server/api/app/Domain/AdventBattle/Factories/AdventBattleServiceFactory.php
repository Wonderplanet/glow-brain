<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Factories;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Services\AdventBattleEndRaidService;
use App\Domain\AdventBattle\Services\AdventBattleEndScoreChallengeService;
use App\Domain\AdventBattle\Services\AdventBattleEndService;
use App\Domain\AdventBattle\Services\AdventBattleRewardMaxScoreService;
use App\Domain\AdventBattle\Services\AdventBattleRewardRaidTotalScoreService;
use App\Domain\AdventBattle\Services\AdventBattleRewardRankingService;
use App\Domain\AdventBattle\Services\AdventBattleRewardRankService;
use App\Domain\AdventBattle\Services\AdventBattleRewardService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;

class AdventBattleServiceFactory
{
    public function __construct()
    {
    }

    /**
     * @param string $adventBattleType
     * @return AdventBattleEndService
     * @throws GameException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getAdventBattleEndService(string $adventBattleType): AdventBattleEndService
    {
        return match ($adventBattleType) {
            AdventBattleType::SCORE_CHALLENGE->value => app()->make(AdventBattleEndScoreChallengeService::class),
            AdventBattleType::RAID->value => app()->make(AdventBattleEndRaidService::class),
            default => throw new GameException(
                ErrorCode::ADVENT_BATTLE_TYPE_NOT_FOUND,
                sprintf('AdventBattleType not found. (advent_battle_type: %s)', $adventBattleType),
            ),
        };
    }

    /**
     * @param string $adventBattleRewardCategory
     * @return AdventBattleRewardService
     * @throws GameException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getAdventBattleRewardService(string $adventBattleRewardCategory): AdventBattleRewardService
    {
        return match ($adventBattleRewardCategory) {
            AdventBattleRewardCategory::MAX_SCORE->value => app()->make(
                AdventBattleRewardMaxScoreService::class
            ),
            AdventBattleRewardCategory::RANKING->value => app()->make(
                AdventBattleRewardRankingService::class
            ),
            AdventBattleRewardCategory::RANK->value => app()->make(
                AdventBattleRewardRankService::class
            ),
            AdventBattleRewardCategory::RAID_TOTAL_SCORE->value => app()->make(
                AdventBattleRewardRaidTotalScoreService::class
            ),
            default => throw new GameException(
                ErrorCode::ADVENT_BATTLE_REWARD_CATEGORY_NOT_FOUND,
                sprintf(
                    'AdventBattleRewardCategory not found. (advent_battle_reward_category: %s)',
                    $adventBattleRewardCategory,
                ),
            ),
        };
    }
}
