<?php

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Outpost\Models\UsrOutpost;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Entities\PvpInGameBattleLog;
use App\Domain\Pvp\Enums\PvpBonusType;
use App\Domain\Pvp\Enums\PvpMatchingType;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Enums\PvpRewardCategory;
use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Domain\Pvp\Services\PvpEndService;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstPvpBonusPoint;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\Resource\Mst\Models\MstPvpReward;
use App\Domain\Resource\Mst\Models\MstPvpRewardGroup;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaEffect;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Models\UsrUnitSummary;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\Data\OpponentPvpStatusData;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class PvpEndServiceTest extends TestCase
{
    private PvpEndService $pvpEndService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pvpEndService = $this->app->make(PvpEndService::class);
    }

    public function test_update_score_勝利時スコア更新(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'score' => 100,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '202501',
            'is_valid' => PvpSessionStatus::STARTED,
        ]);
        MstPvpRank::factory()->create([
            'id' => PvpRankClassType::BRONZE->value . '_1',
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 1,
            'required_lower_score' => 0,
            'win_add_point' => 100,
            'lose_sub_point' => 100,
            'asset_key' => 'bronze_asset',
        ]);
        $pvpInGameBattleLog = new PvpInGameBattleLog(
            12345, // clearTimeMs
            1000, // maxDamage
            collect([]), // partyStatus
            collect([]), // partyStatus
        );
        // スコアを200に更新
        $this->pvpEndService->updateScore(
            $usrPvp,
            $usrPvpSession,
            $pvpInGameBattleLog,
            true,
            null,
            PvpMatchingType::Same
        );

        // 更新されたスコアを確認
        $this->assertEquals(200, $usrPvp->getScore());
    }

    public function test_update_score_敗北時スコア更新(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'score' => 100,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '202501',
            'is_valid' => PvpSessionStatus::STARTED,
        ]);
        MstPvpRank::factory()->create([
            'id' => PvpRankClassType::BRONZE->value . '_1',
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 1,
            'required_lower_score' => 0,
            'win_add_point' => 100,
            'lose_sub_point' => 100,
            'asset_key' => 'bronze_asset',
        ]);
        $pvpInGameBattleLog = new PvpInGameBattleLog(
            12345, // clearTimeMs
            1000, // maxDamage
            collect([]), // partyStatus
            collect([]), // partyStatus
        );
        // スコアを0に更新
        $this->pvpEndService->updateScore(
            $usrPvp,
            $usrPvpSession,
            $pvpInGameBattleLog,
            false,
            null,
            PvpMatchingType::Same
        );

        // 更新されたスコアを確認
        $this->assertEquals(0, $usrPvp->getScore());
    }


    public function test_update_score_チート判定時はスコアがマイナス１になる(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'score' => 100,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
            'is_excluded_ranking'  => true, // チート判定
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '202501',
            'is_valid' => PvpSessionStatus::STARTED,
        ]);
        MstPvpRank::factory()->create([
            'id' => PvpRankClassType::BRONZE->value . '_1',
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 1,
            'required_lower_score' => 0,
            'win_add_point' => 100,
            'lose_sub_point' => 100,
            'asset_key' => 'bronze_asset',
        ]);
        $pvpInGameBattleLog = new PvpInGameBattleLog(
            12345, // clearTimeMs
            1000, // maxDamage
            collect([]), // partyStatus
            collect([]), // partyStatus
        );
        // スコアを200に更新
        $this->pvpEndService->updateScore(
            $usrPvp,
            $usrPvpSession,
            $pvpInGameBattleLog,
            true,
            null,
            PvpMatchingType::Same
        );

        // 更新されたスコアを確認
        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $score = $pvpCacheService->getRankingScore(
            $usrPvpSession->sys_pvp_season_id,
            $usrUserId
        );
        $this->assertEquals(PvpConstant::RANKING_CHEATER_SCORE, $score);
    }

    public function test_add_total_score_rewards_累計ポイント報酬(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);
        $this->createDiamond($usrUserId, freeDiamond: 100);
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '202501',
            'score' => 1100,
            'max_received_score_reward' => 100,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
        ]);
        MstPvpRewardGroup::factory()->createMany([
            // test_group_0は、受け取り済み報酬をスルーしてるか確認する為の設定
            [
                'id' => 'test_group_0',
                'mst_pvp_id' => $usrPvp->getSysPvpSeasonId(),
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 100,
            ],
            [
                'id' => 'test_group_1',
                'mst_pvp_id' => $usrPvp->getSysPvpSeasonId(),
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 101,
            ],
            [
                'id' => 'test_group_2',
                'mst_pvp_id' => $usrPvp->getSysPvpSeasonId(),
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 500,
            ],
            [
                'id' => 'test_group_3',
                'mst_pvp_id' => $usrPvp->getSysPvpSeasonId(),
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 1101,
            ],
        ]);
        MstPvpReward::factory()->createMany([
            [
                'mst_pvp_reward_group_id' => 'test_group_0',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 10000,
            ],
            [
                'mst_pvp_reward_group_id' => 'test_group_1',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 100,
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
            [
                'mst_pvp_reward_group_id' => 'test_group_3',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 20000,
            ],
        ]);

        // スコアを1100に更新
        $this->pvpEndService->addTotalScoreRewards($usrPvp);
        $rewardDelegator = $this->app->make(RewardDelegator::class);
        $rewardDelegator->sendRewards($usrUserId, 1, CarbonImmutable::now());
        $this->saveAll();

        // 付与された報酬を確認
        $usrPvp->refresh();
        $this->assertEquals(1100, $usrPvp->getMaxReceivedScoreReward());
        $usrUserParameter->refresh();
        $this->assertEquals(300, $usrUserParameter->getCoin());
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(200, $diamond->getFreeAmount());
    }

    public function test_add_total_score_rewards_デフォルト設定累計ポイント報酬(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);
        $this->createDiamond($usrUserId, freeDiamond: 100);
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '202501',
            'score' => 1100,
            'max_received_score_reward' => 100,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
        ]);
        MstPvpRewardGroup::factory()->createMany([
            // test_group_0は、受け取り済み報酬をスルーしてるか確認する為の設定
            [
                'id' => 'test_group_0',
                'mst_pvp_id' => 'default_pvp',
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 100,
            ],
            [
                'id' => 'test_group_1',
                'mst_pvp_id' => 'default_pvp',
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 101,
            ],
            [
                'id' => 'test_group_2',
                'mst_pvp_id' => 'default_pvp',
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 500,
            ],
            [
                'id' => 'test_group_3',
                'mst_pvp_id' => 'default_pvp',
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 1101,
            ],
            [
                'id' => 'test_group_10',
                'mst_pvp_id' => '20250101',
                'reward_category' => PvpRewardCategory::TOTAL_SCORE->value,
                'condition_value' => 100,
            ],
        ]);
        MstPvpReward::factory()->createMany([
            [
                'mst_pvp_reward_group_id' => 'test_group_0',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 10000,
            ],
            [
                'mst_pvp_reward_group_id' => 'test_group_1',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 100,
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
            [
                'mst_pvp_reward_group_id' => 'test_group_3',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 20000,
            ],
            [
                'mst_pvp_reward_group_id' => 'test_group_10',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 100,
            ],
        ]);

        // スコアを1100に更新
        $this->pvpEndService->addTotalScoreRewards($usrPvp);
        $rewardDelegator = $this->app->make(RewardDelegator::class);
        $rewardDelegator->sendRewards($usrUserId, 1, CarbonImmutable::now());
        $this->saveAll();

        // 付与された報酬を確認
        $usrPvp->refresh();
        $this->assertEquals(1100, $usrPvp->getMaxReceivedScoreReward());
        $usrUserParameter->refresh();
        $this->assertEquals(300, $usrUserParameter->getCoin());
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(200, $diamond->getFreeAmount());
    }

    public function test_get_opponent_bonus_対戦相手ボーナス(): void
    {
        MstPvpBonusPoint::factory()->create([
            'id' => 'bonus_1',
            'bonus_type' => PvpBonusType::WinUpperBonus->value,
            'bonus_point' => 100,
            'condition_value' => PvpRankClassType::BRONZE->value,
        ]);
        MstPvpBonusPoint::factory()->create([
            'id' => 'bonus_2',
            'bonus_type' => PvpBonusType::WinSameBonus->value,
            'bonus_point' => 50,
            'condition_value' => PvpRankClassType::BRONZE->value,
        ]);
        MstPvpBonusPoint::factory()->create([
            'id' => 'bonus_3',
            'bonus_type' => PvpBonusType::WinLowerBonus->value,
            'bonus_point' => 20,
            'condition_value' => PvpRankClassType::BRONZE->value,
        ]);

        // 格上
        $over = $this->pvpEndService->getOpponentBonus(
            PvpRankClassType::BRONZE->value,
            PvpMatchingType::Upper
        );
        // 同格
        $normal = $this->pvpEndService->getOpponentBonus(
            PvpRankClassType::BRONZE->value,
            PvpMatchingType::Same
        );
        // 格下
        $under = $this->pvpEndService->getOpponentBonus(
            PvpRankClassType::BRONZE->value,
            PvpMatchingType::Lower
        );

        $this->assertEquals(100, $over);
        $this->assertEquals(50, $normal);
        $this->assertEquals(20, $under);
    }

    public function test_get_clear_time_bonus_クリア時間ボーナス(): void
    {
        MstPvpBonusPoint::factory()->create([
            'id' => 'bonus_1',
            'bonus_type' => PvpBonusType::ClearTime->value,
            'bonus_point' => 100,
            'condition_value' => 60, // 60秒以下でボーナス
        ]);
        MstPvpBonusPoint::factory()->create([
            'id' => 'bonus_2',
            'bonus_type' => PvpBonusType::ClearTime->value,
            'bonus_point' => 50,
            'condition_value' => 120, // 120秒以下でボーナス
        ]);

        // 30秒でクリア
        $bonus = $this->pvpEndService->getClearTimeBonus(30);
        $this->assertEquals(100, $bonus);

        // 90秒でクリア
        $bonus = $this->pvpEndService->getClearTimeBonus(90);
        $this->assertEquals(50, $bonus);

        // 150秒でクリア
        $bonus = $this->pvpEndService->getClearTimeBonus(150);
        $this->assertEquals(0, $bonus);
    }

    public function test_get_result_point_勝敗ポイント(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'score' => 100,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
        ]);

        MstPvpRank::factory()->create([
            'id' => PvpRankClassType::BRONZE->value . '_1',
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 1,
            'required_lower_score' => 0,
            'win_add_point' => 100,
            'lose_sub_point' => 100,
            'asset_key' => 'bronze_asset',
        ]);
        MstPvpRank::factory()->create([
            'id' => PvpRankClassType::BRONZE->value . '_2',
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 3,
            'required_lower_score' => 0,
            'win_add_point' => 200,
            'lose_sub_point' => 200,
            'asset_key' => 'bronze_asset',
        ]);

        $resultPoint = $this->pvpEndService->getResultPoint($usrPvp, true);
        $this->assertEquals(100, $resultPoint);

        $resultPoint = $this->pvpEndService->getResultPoint($usrPvp, false);
        $this->assertEquals(-100, $resultPoint);
    }

    public function test_consume_challenge_attempt_無料対戦回数消費(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_remaining_challenge_count' => 3,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'is_use_item' => 0,
        ]);

        // 対戦回数を1回消費
        $this->pvpEndService->consumeChallengeAttempt($usrPvp, $usrPvpSession, 1, '');

        // 残りの対戦回数が2回になっていることを確認
        $this->assertEquals(2, $usrPvp->getDailyRemainingChallengeCount());
    }


    public function test_consume_challenge_attempt_アイテム消費対戦回数消費(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_remaining_challenge_count' => 0,
            'daily_remaining_item_challenge_count' => 3,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'is_use_item' => 1, // ランクマッチチケット使用に変更
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::PVP_CHALLENGE_ITEM_ID,
            'value' => 'pvp_challenge_item',
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'pvp_challenge_item',
            'amount' => 5,
        ]);

        // 対戦回数を1回消費
        $this->pvpEndService->consumeChallengeAttempt($usrPvp, $usrPvpSession, 1, 'test');
        $this->saveAll();
        $usrItem = UsrItem::where('usr_user_id', $usrUserId)
            ->where('mst_item_id', 'pvp_challenge_item')
            ->first();

        // 残りの対戦回数が2回になっていることを確認
        $this->assertEquals(2, $usrPvp->getDailyRemainingItemChallengeCount());
        // アイテムの所持数が1つ減っていることを確認
        $this->assertNotNull($usrItem);
        $this->assertEquals(4, $usrItem->getAmount());
    }

    public function test_consume_challenge_attempt_アイテム消費対戦回数消費時にチケットアイテムIDが取得できない(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_remaining_challenge_count' => 0,
            'daily_remaining_item_challenge_count' => 3,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'is_use_item' => 1, // ランクマッチチケット使用に変更
        ]);

        // 対戦回数を1回消費
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);
        $this->pvpEndService->consumeChallengeAttempt($usrPvp, $usrPvpSession, 1, '');
    }

    public function test_consume_challenge_attempt_アイテム消費対戦回数消費時にチケットが足りない(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_remaining_challenge_count' => 0,
            'daily_remaining_item_challenge_count' => 3,
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::PVP_CHALLENGE_ITEM_ID,
            'value' => 'pvp_challenge_item',
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'pvp_challenge_item',
            'amount' => 1, // アイテムがない状態
        ]);

        // 対戦回数を1回消費
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'is_use_item' => 1, // ランクマッチチケット使用に変更
        ]);

        $this->pvpEndService->consumeChallengeAttempt($usrPvp, $usrPvpSession, 2, 'test');
    }


    public function test_consume_challenge_attempt_アイテム消費対戦回数消費時にチケットを入手してない(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_remaining_challenge_count' => 0,
            'daily_remaining_item_challenge_count' => 3,
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::PVP_CHALLENGE_ITEM_ID,
            'value' => 'pvp_challenge_item',
        ]);

        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'is_use_item' => 1, // ランクマッチチケット使用に変更
        ]);

        // 対戦回数を1回消費
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_NOT_OWNED);
        $this->pvpEndService->consumeChallengeAttempt($usrPvp, $usrPvpSession, 1, 'test');
    }

    public function test_consume_challenge_attempt_挑戦権がない(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_remaining_challenge_count' => 0,
            'daily_remaining_item_challenge_count' => 0,
        ]);

        // 対戦回数を1回消費
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::PVP_NO_CHALLENGE_RIGHT);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'is_use_item' => 0,
        ]);

        $this->pvpEndService->consumeChallengeAttempt($usrPvp, $usrPvpSession, 1, 'test');
    }

    public function test_consume_challenge_attempt_ランクマッチチケット優先消費(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_remaining_challenge_count' => 3, // 無料回数もある状態
            'daily_remaining_item_challenge_count' => 2,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'is_use_item' => 1, // ランクマッチチケット使用
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::PVP_CHALLENGE_ITEM_ID,
            'value' => 'pvp_challenge_item',
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'pvp_challenge_item',
            'amount' => 5,
        ]);

        // 対戦回数を1回消費
        $this->pvpEndService->consumeChallengeAttempt($usrPvp, $usrPvpSession, 1, 'test');
        $this->saveAll();
        $usrItem = UsrItem::where('usr_user_id', $usrUserId)
            ->where('mst_item_id', 'pvp_challenge_item')
            ->first();

        // 無料回数は消費されず、アイテム回数が消費される
        $this->assertEquals(3, $usrPvp->getDailyRemainingChallengeCount());
        $this->assertEquals(1, $usrPvp->getDailyRemainingItemChallengeCount());
        $this->assertNotNull($usrItem);
        $this->assertEquals(4, $usrItem->getAmount());
    }

    public function test_consume_challenge_attempt_ランクマッチチケット使用時にアイテム回数がない(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_remaining_challenge_count' => 3, // 無料回数はある状態
            'daily_remaining_item_challenge_count' => 0, // アイテム回数はない
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'is_use_item' => 1, // ランクマッチチケット使用
        ]);

        // 対戦回数を1回消費
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::PVP_NO_CHALLENGE_RIGHT);
        $this->pvpEndService->consumeChallengeAttempt($usrPvp, $usrPvpSession, 1, 'test');
    }

    public function test_consume_challenge_attempt_通常対戦で無料回数がない場合(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_remaining_challenge_count' => 0, // 無料回数なし
            'daily_remaining_item_challenge_count' => 3, // アイテム回数はある
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'is_use_item' => 0, // 通常対戦
        ]);

        // 通常対戦で無料回数がない場合はエラー
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::PVP_NO_CHALLENGE_RIGHT);
        $this->pvpEndService->consumeChallengeAttempt($usrPvp, $usrPvpSession, 1, 'test');
    }


    public function test_end_session_対戦セッション終了(): void
    {
        $now = CarbonImmutable::now();
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
            'score' => 100,
            'last_played_at' => null,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '202501',
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        // 対戦セッションを終了
        $this->pvpEndService->endSession($usrPvp, $usrPvpSession, $now);

        // セッションが終了状態になっていることを確認
        $this->assertTrue($usrPvpSession->isClosed());
        $this->assertNotNull($usrPvp->getLastPlayedAt());
    }

    public function test_update_opponent_candidate_自身の対戦相手候補の更新してスコアが更新されることを確認(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
            'score' => 100,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '202501',
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        // 自分の対戦相手候補を登録
        $this->callMethod(
            $this->pvpEndService,
            'updateOpponentCandidate',
            [
                $usrPvp->getUsrUserId(),
                $usrPvp,
                $usrPvpSession,
                PvpRankClassType::BRONZE->value,
                1, // レベル
            ]
        );

        // キャッシュに対戦相手候補が追加されていることを確認
        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $candidates = $pvpCacheService->getOpponentCandidateRangeList(
            $usrPvpSession->getSysPvpSeasonId(),
            $usrPvp->pvp_rank_class_type,
            $usrPvp->pvp_rank_class_level,
            0,
            $usrPvp->score,
        );
        $this->assertNotEmpty($candidates);
        $this->assertCount(1, $candidates);

        // スコアを更新して自分の対戦相手候補を再登録
        $usrPvp->score = 200;
        $this->callMethod(
            $this->pvpEndService,
            'updateOpponentCandidate',
            [
                $usrPvp->getUsrUserId(),
                $usrPvp,
                $usrPvpSession,
                PvpRankClassType::BRONZE->value,
                1, // レベル
            ]
        );

        // キャッシュに対戦相手候補が追加されていることを確認
        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $candidates = $pvpCacheService->getOpponentCandidateRangeList(
            $usrPvpSession->getSysPvpSeasonId(),
            $usrPvp->pvp_rank_class_type,
            $usrPvp->pvp_rank_class_level,
            101, // 更新前以上のスコアでのみ取得する
            $usrPvp->score,
        );
        $this->assertNotEmpty($candidates);
        $this->assertCount(1, $candidates);
    }


    public function test_update_opponent_candidate_自身の対戦相手候補の更新して新しいランク区分に登録されることを確認(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $beforePvpRankClassType = PvpRankClassType::BRONZE->value;
        $beforePvpRankClassLevel = 1;
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'pvp_rank_class_type' => $beforePvpRankClassType,
            'pvp_rank_class_level' => $beforePvpRankClassLevel,
            'score' => 100,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '202501',
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        // 自分の対戦相手候補を登録
        $this->callMethod(
            $this->pvpEndService,
            'updateOpponentCandidate',
            [
                $usrPvp->getUsrUserId(),
                $usrPvp,
                $usrPvpSession,
                $beforePvpRankClassType,
                $beforePvpRankClassLevel,
            ]
        );

        // キャッシュに対戦相手候補が追加されていることを確認
        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $candidates = $pvpCacheService->getOpponentCandidateRangeList(
            $usrPvpSession->getSysPvpSeasonId(),
            $usrPvp->pvp_rank_class_type,
            $usrPvp->pvp_rank_class_level,
            0,
            $usrPvp->score,
        );

        $this->assertNotEmpty($candidates);
        $this->assertCount(1, $candidates);

        // 状態を更新して自分の対戦相手候補を再登録
        $afterPvpRankClassType = PvpRankClassType::BRONZE->value;
        $afterPvpRankClassLevel = 2;
        $usrPvp->pvp_rank_class_type = $afterPvpRankClassType;
        $usrPvp->pvp_rank_class_level = $afterPvpRankClassLevel;
        $usrPvp->score = 200;
        $this->callMethod(
            $this->pvpEndService,
            'updateOpponentCandidate',
            [
                $usrPvp->getUsrUserId(),
                $usrPvp,
                $usrPvpSession,
                $beforePvpRankClassType,
                $beforePvpRankClassLevel,
            ]
        );

        // 前の区分のキャッシュに対戦相手候補が存在しないことを確認
        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $candidates = $pvpCacheService->getOpponentCandidateRangeList(
            $usrPvpSession->getSysPvpSeasonId(),
            $beforePvpRankClassType,
            $beforePvpRankClassLevel,
            0,
            $usrPvp->score,
        );
        $this->assertCount(0, $candidates);

        // 新しい区分のキャッシュに対戦相手候補が追加されていることを確認
        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $candidates = $pvpCacheService->getOpponentCandidateRangeList(
            $usrPvpSession->getSysPvpSeasonId(),
            $usrPvp->pvp_rank_class_type,
            $usrPvp->pvp_rank_class_level,
            0,
            $usrPvp->score,
        );
        $this->assertNotEmpty($candidates);
        $this->assertCount(1, $candidates);
    }

    public function test_get_opponent_pvp_status_正常に生成できる(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 1,
            'score' => 100,
        ]);
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '202501',
            'is_valid' => PvpSessionStatus::STARTED,
            'party_no' => 1,
        ]);

        $profile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'name' => 'Test User',
        ]);

        $unit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        $usrParty = UsrParty::factory()->create([
            'party_no' => $usrPvpSession->party_no,
            'usr_user_id' => $usrUserId,
            'usr_unit_id_1' => $unit->id,
        ]);

        MstUnit::factory()->create([
            'id' => $unit->mst_unit_id,
        ]);

        UsrOutpost::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_outpost_id' => 'mst_outpost',
            'mst_artwork_id' => 'mst_artwork',
            'is_used' => 1,
        ]);
        $usrOutpostEnhancement = UsrOutpostEnhancement::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_outpost_id' => 'mst_outpost',
        ]);

        // キャラ図鑑ランク効果
        $mstUnitEncyclopediaEffect = MstUnitEncyclopediaEffect::factory()->create([
            'mst_unit_encyclopedia_reward_id' => 'mst_unit_encyclopedia_reward',
        ]);
        $mstUnitEncyclopediaReward = MstUnitEncyclopediaReward::factory()->create([
            'id' => 'mst_unit_encyclopedia_reward',
            'unit_encyclopedia_rank' => 5,
        ]);
        UsrUnitSummary::factory()->create([
            'usr_user_id' => $usrUserId,
            'grade_level_total_count' => 5,
        ]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => 'mst_artwork',
        ]);

        // 対戦相手のステータスを取得
        /** @var OpponentPvpStatusData $result */
        $result = $this->callMethod(
            $this->pvpEndService,
            'getOpponentPvpStatus',
            [
                $usrPvp,
                $usrPvpSession,
            ]
        );

        $result = $result->formatToResponse();

        // $this->assertJsonを使用してJSON形式での確認も可能
        $check = [
            'pvpUnits' => [
                [
                    'mstUnitId' => $unit->mst_unit_id,
                    'level' => $unit->level,
                    'rank' => $unit->rank,
                    'gradeLevel' => $unit->grade_level,
                ],
            ],
            'usrOutpostEnhancements' => [
                [
                    'mstOutpostId' => $usrOutpostEnhancement->mst_outpost_id,
                    'mstOutpostEnhancementId' => $usrOutpostEnhancement->mst_outpost_enhancement_id,
                    'level' => $usrOutpostEnhancement->level,
                ],
            ],
            'usrEncyclopediaEffects' => [
                [
                    'mstEncyclopediaEffectId' => $mstUnitEncyclopediaEffect->id,
                ],
            ],
            'mstArtworkIds' => [
                'mst_artwork',
            ],
        ];

        $this->assertJson(json_encode($result));
        $this->assertJsonStringEqualsJsonString(json_encode($check), json_encode($result));
    }
}
