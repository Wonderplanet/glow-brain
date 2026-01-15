<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Enums\PvpRewardCategory;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\UseCases\PvpEndUseCase;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\Resource\Mst\Models\MstPvpReward;
use App\Domain\Resource\Mst\Models\MstPvpRewardGroup;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\ResultData\PvpEndResultData;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class PvpEndUseCaseTest extends TestCase
{
    public function test_exec_success_勝利して正常にリザルトデータを取得できる(): void
    {
        // 実データ作成
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 1000,
        ]);
        $this->createDiamond($usrUserId, freeDiamond: 2000);
        $currentUser = new CurrentUser($usrUserId);
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );
        $isWin = true;

        // シーズン
        $sysPvpSeason = SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
        ]);
        // MstPvp
        $mstPvp = MstPvp::factory()->create([
            'item_challenge_cost_amount' => 1,
        ]);
        // UsrPvpSession
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => true,
        ]);
        // UsrPvp
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 1000,
            'max_received_score_reward' => 0,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
            'daily_remaining_challenge_count' => 3,
            'daily_remaining_item_challenge_count' => 2,
            'latest_reset_at' => $now->subMinutes(10)->toDateTimeString(),
        ]);
        // UsrUserProfile
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'name' => 'Test User',
        ]);
        // ランクマスタ
        MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 1,
            'required_lower_score' => 0,
            'win_add_point' => 100,
            'lose_sub_point' => 100,
            'asset_key' => 'bronze_asset',
        ]);
        MstPvpRewardGroup::factory()->createMany([
            [
                'id' => 'test_group_1',
                'mst_pvp_id' => $usrPvpSession->getSysPvpSeasonId(),
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 120,
            ],
            [
                'id' => 'test_group_2',
                'mst_pvp_id' => $usrPvpSession->getSysPvpSeasonId(),
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 180,
            ],
        ]);
        MstPvpReward::factory()->createMany([
            [
                'id' => 'test_reward_1',
                'mst_pvp_reward_group_id' => 'test_group_1',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 300,
            ],
            [
                'id' => 'test_reward_2',
                'mst_pvp_reward_group_id' => 'test_group_2',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 100,
            ],
            [
                'id' => 'test_reward_3',
                'mst_pvp_reward_group_id' => 'test_group_2',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 100,
            ],
        ]);

        $inGameBattleLog = [
            'clearTimeMs' => 12345,
            'partyStatus' => [],
            'maxDamage' => 1000,
        ];

        // UseCase生成（リポジトリやサービスはDIで解決）
        $useCase = app(PvpEndUseCase::class);

        $result = $useCase->exec(
            $currentUser,
            $platform,
            $sysPvpSeasonId,
            $inGameBattleLog,
            $isWin
        );

        $this->assertInstanceOf(PvpEndResultData::class, $result);
        $this->assertEquals(1100, $result->usrPvpStatus->getScore());
        $this->assertEquals(1100, $result->usrPvpStatus->getMaxReceivedScoreReward());
        $this->assertEquals(PvpRankClassType::BRONZE, $result->usrPvpStatus->getPvpRankClassType());
        $this->assertEquals(1, $result->usrPvpStatus->getPvpRankClassLevel());
        $this->assertEquals(2, $result->usrPvpStatus->getDailyRemainingChallengeCount());
        $this->assertEquals(2, $result->usrPvpStatus->getDailyRemainingItemChallengeCount());
        $this->assertEquals(3, $result->pvpTotalScoreRewards->count());
        $this->assertEquals(RewardType::COIN->value, $result->pvpTotalScoreRewards->get(0)->getType());
        $this->assertEquals(300, $result->pvpTotalScoreRewards->get(0)->getAmount());
        $this->assertEquals(RewardType::COIN->value, $result->pvpTotalScoreRewards->get(1)->getType());
        $this->assertEquals(100, $result->pvpTotalScoreRewards->get(1)->getAmount());
        $this->assertEquals(RewardType::FREE_DIAMOND->value, $result->pvpTotalScoreRewards->get(2)->getType());
        $this->assertEquals(100, $result->pvpTotalScoreRewards->get(2)->getAmount());

        $usrPvp->refresh();
        $this->assertEquals(1100, $usrPvp->getMaxReceivedScoreReward());
        $usrUserParameter->refresh();
        $this->assertEquals(1400, $usrUserParameter->getCoin());
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(2100, $diamond->getFreeAmount());
    }

    public function test_exec_敗北でスコアがマイナスになる場合(): void
    {
        // 実データ作成
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);
        $this->createDiamond($usrUserId, freeDiamond: 100);
        $currentUser = new CurrentUser($usrUserId);
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );
        $isWin = false;

        // シーズン
        $sysPvpSeason = SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
        ]);
        // MstPvp
        $mstPvp = MstPvp::factory()->create([
            'item_challenge_cost_amount' => 1,
        ]);
        // UsrPvpSession
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => true,
        ]);
        // UsrPvp（低いスコア設定）
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 50, // 低いスコア
            'max_received_score_reward' => 100,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
            'daily_remaining_challenge_count' => 3,
            'daily_remaining_item_challenge_count' => 2,
            'latest_reset_at' => $now->subMinutes(10)->toDateTimeString(),
        ]);
        // UsrUserProfile
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'name' => 'Test User',
        ]);
        // ランクマスタ（敗北時減算ポイント > 現在スコア）
        MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 1,
            'required_lower_score' => 0,
            'win_add_point' => 100,
            'lose_sub_point' => 100, // 現在スコア(50)より大きい減算値
            'asset_key' => 'bronze_asset',
        ]);
        MstPvpRewardGroup::factory()->createMany([
            [
                'id' => 'test_group_1',
                'mst_pvp_id' => $usrPvpSession->getSysPvpSeasonId(),
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 20,
            ],
            [
                'id' => 'test_group_2',
                'mst_pvp_id' => $usrPvpSession->getSysPvpSeasonId(),
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 80,
            ],
        ]);
        MstPvpReward::factory()->createMany([
            [
                'mst_pvp_reward_group_id' => 'test_group_1',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 300,
            ],
            [
                'mst_pvp_reward_group_id' => 'test_group_2',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 100,
            ],
            [
                'mst_pvp_reward_group_id' => 'test_group_2',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 100,
            ],
        ]);

        $inGameBattleLog = [
            'clearTimeMs' => 12345,
            'partyStatus' => [],
            'maxDamage' => 1000,
        ];

        // UseCase生成（リポジトリやサービスはDIで解決）
        $useCase = app(PvpEndUseCase::class);

        $result = $useCase->exec(
            $currentUser,
            $platform,
            $sysPvpSeasonId,
            $inGameBattleLog,
            $isWin
        );

        $this->assertInstanceOf(PvpEndResultData::class, $result);
        // スコアがマイナスになるところを下限0で制限されることを確認 (50 - 100 = -50 → 0)
        $this->assertEquals(0, $result->usrPvpStatus->getScore());
        $this->assertEquals(PvpRankClassType::BRONZE, $result->usrPvpStatus->getPvpRankClassType());
        $this->assertEquals(1, $result->usrPvpStatus->getPvpRankClassLevel());
        $this->assertEquals(2, $result->usrPvpStatus->getDailyRemainingChallengeCount());
        $this->assertEquals(2, $result->usrPvpStatus->getDailyRemainingItemChallengeCount());
        $this->assertEquals(0, $result->pvpTotalScoreRewards->count());

        $usrPvp->refresh();
        $this->assertEquals(100, $usrPvp->getMaxReceivedScoreReward());
        $usrUserParameter->refresh();
        $this->assertEquals(100, $usrUserParameter->getCoin());
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(100, $diamond->getFreeAmount());
    }

    public function test_exec_success_前回の挑戦回数リセット日次が昨日より前であればリセットされる(): void
    {
        // 実データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);
        $this->createDiamond($usrUserId, freeDiamond: 100);
        $currentUser = new CurrentUser($usrUserId);
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );
        $isWin = true;

        // シーズン
        $sysPvpSeason = SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
        ]);
        // MstPvp
        $mstPvp = MstPvp::factory()->create([
            'id' => 'default_pvp',
            'item_challenge_cost_amount' => 1,
            'max_daily_challenge_count' => 5,
            'max_daily_item_challenge_count' => 5,
        ]);
        // UsrPvpSession
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => true,
        ]);
        // UsrPvp
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 1000,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
            'daily_remaining_challenge_count' => 3,
            'daily_remaining_item_challenge_count' => 2,
            'latest_reset_at' => $now->subDay()->toDateTimeString(), // 昨日の日付
        ]);
        // UsrUserProfile
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'name' => 'Test User',
        ]);
        // ランクマスタ
        MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 1,
            'required_lower_score' => 0,
            'win_add_point' => 100,
            'lose_sub_point' => 100,
            'asset_key' => 'bronze_asset',
        ]);

        $inGameBattleLog = [
            'clearTimeMs' => 12345,
            'partyStatus' => [],
            'maxDamage' => 1000,
        ];

        // UseCase生成（リポジトリやサービスはDIで解決）
        $useCase = app(PvpEndUseCase::class);

        $result = $useCase->exec(
            $currentUser,
            $platform,
            $sysPvpSeasonId,
            $inGameBattleLog,
            $isWin
        );

        $this->assertInstanceOf(PvpEndResultData::class, $result);
        $this->assertEquals(1100, $result->usrPvpStatus->getScore());
        $this->assertEquals(PvpRankClassType::BRONZE, $result->usrPvpStatus->getPvpRankClassType());
        $this->assertEquals(1, $result->usrPvpStatus->getPvpRankClassLevel());
        $this->assertEquals(5, $result->usrPvpStatus->getDailyRemainingChallengeCount());
        $this->assertEquals(5, $result->usrPvpStatus->getDailyRemainingItemChallengeCount());
    }
}
