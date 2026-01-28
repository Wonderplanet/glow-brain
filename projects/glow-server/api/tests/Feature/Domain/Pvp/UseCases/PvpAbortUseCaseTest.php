<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp;

use Tests\Support\Entities\CurrentUser;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\UseCases\PvpAbortUseCase;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\ResultData\PvpAbortResultData;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class PvpAbortUseCaseTest extends TestCase
{
    public function test_abort_success_敗北扱いで終了できる(): void
    {
        // 実データ作成
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
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
            'pvp_rank_class_level' => 2,
            'daily_remaining_challenge_count' => 3,
            'daily_remaining_item_challenge_count' => 2,
            'latest_reset_at' => $now->toDateTimeString(),
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
        MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 2,
            'required_lower_score' => 1000,
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
        $useCase = app(PvpAbortUseCase::class);

        $result = $useCase->exec(
            $currentUser,
            $sysPvpSeasonId,
            $inGameBattleLog,
            $isWin
        );

        $usrPvp = UsrPvp::query()
            ->where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $sysPvpSeasonId)
            ->first();

        $this->assertInstanceOf(PvpAbortResultData::class, $result);
        $this->assertEquals(900, $usrPvp->getScore());
        $this->assertEquals(PvpRankClassType::BRONZE->value, $usrPvp->getPvpRankClassType());
        $this->assertEquals(1, $result->usrPvpStatus->getPvpRankClassLevel());
        $this->assertEquals(2, $result->usrPvpStatus->getDailyRemainingChallengeCount());
        $this->assertEquals(2, $result->usrPvpStatus->getDailyRemainingItemChallengeCount());
    }

    public function test_abort_success_時間外の場合セッションを終了だけして処理を終える(): void
    {
        // 実データ作成
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
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
            'start_at' => $now->subDays(2)->toDateTimeString(), // 1日前に設定
            'end_at' => $now->subDays(1)->toDateTimeString(), // 1日前に設定
            'closed_at' => $now->subDays(1)->toDateTimeString(), // 1日前に設定
        ]);
        // MstPvp
        $mstPvp = MstPvp::factory()->create([
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
            'pvp_rank_class_level' => 2,
            'daily_remaining_challenge_count' => 3,
            'daily_remaining_item_challenge_count' => 2,
            'last_played_at' => null,
            'latest_reset_at' => $now->toDateTimeString(),
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
        MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 2,
            'required_lower_score' => 1000,
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
        $useCase = app(PvpAbortUseCase::class);

        $result = $useCase->exec(
            $currentUser,
            $sysPvpSeasonId,
            $inGameBattleLog,
            $isWin
        );

        $afterUsrPvp = UsrPvp::query()
            ->where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $sysPvpSeasonId)
            ->first();
        $afterUsrPvpSession = UsrPvpSession::query()
            ->where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $sysPvpSeasonId)
            ->first();

        $this->assertInstanceOf(PvpAbortResultData::class, $result);
        // ユーザーのスコアなどが変わらないことを確認
        $this->assertEquals($usrPvp->getScore(), $afterUsrPvp->getScore());
        $this->assertEquals($usrPvp->getPvpRankClassType(), $afterUsrPvp->getPvpRankClassType());
        // 最終プレイ日時が更新されていないことを確認
        $this->assertNull($afterUsrPvp->getLastPlayedAt());
        // セッションが終了していることを確認
        $this->assertTrue($afterUsrPvpSession->isClosed());
    }

    public function test_abort_success_挑戦回数が回復できる場合は回復する(): void
    {
        // 実データ作成
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
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
            'pvp_rank_class_level' => 2,
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
        MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 2,
            'required_lower_score' => 1000,
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
        $useCase = app(PvpAbortUseCase::class);

        $result = $useCase->exec(
            $currentUser,
            $sysPvpSeasonId,
            $inGameBattleLog,
            $isWin
        );

        $usrPvp = UsrPvp::query()
            ->where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $sysPvpSeasonId)
            ->first();

        $this->assertInstanceOf(PvpAbortResultData::class, $result);
        $this->assertEquals(900, $usrPvp->getScore());
        $this->assertEquals(PvpRankClassType::BRONZE->value, $usrPvp->getPvpRankClassType());
        $this->assertEquals(1, $result->usrPvpStatus->getPvpRankClassLevel());
        $this->assertEquals(5, $result->usrPvpStatus->getDailyRemainingChallengeCount());
        $this->assertEquals(5, $result->usrPvpStatus->getDailyRemainingItemChallengeCount());
    }
}
