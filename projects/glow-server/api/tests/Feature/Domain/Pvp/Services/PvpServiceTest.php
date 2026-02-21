<?php

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Services\PvpService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class PvpServiceTest extends TestCase
{
    private PvpService $pvpService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pvpService = $this->app->make(PvpService::class);
    }

    public function test_get_current_season_id_2025年の週番号１の月曜日で想定通りのIDが取得できる(): void
    {
        $now = CarbonImmutable::now()->setISODate(2025,1,1);

        // 現在のシーズンIDを取得
        $currentSeasonId = $this->pvpService->getCurrentSeasonId($now);

        // 期待されるシーズンIDと一致することを確認
        $this->assertEquals('2025001', $currentSeasonId);
    }

    public function test_get_current_season_id_日跨ぎで週番号が同じ場合でも問題なく取得できる(): void
    {
        // 2026/01/04は2026年の週番号1の最終日
        $now = CarbonImmutable::parse('2025-12-29 23:59:59', 'JST')->setTimezone('UTC');
        $currentSeasonId1 = $this->pvpService->getCurrentSeasonId($now);
        $currentSeasonId2 = $this->pvpService->getCurrentSeasonId($now->addSecond());
        $this->assertTrue($currentSeasonId1 === $currentSeasonId2);
    }

    public function test_get_current_season_id_日跨ぎで週番号が変わる場合でも問題なく取得できる(): void
    {
        // 2026/01/04は2026年の週番号1の最終日
        $now = CarbonImmutable::parse('2026-01-04 23:59:59', 'JST')->setTimezone('UTC');
        $currentSeasonId1 = $this->pvpService->getCurrentSeasonId($now);
        $currentSeasonId2 = $this->pvpService->getCurrentSeasonId($now->addSecond());
        $this->assertEquals('2026001', $currentSeasonId1);
        $this->assertEquals('2026002', $currentSeasonId2);
    }

    public function test_get_current_season_id_年またぎでも問題なく取得できる(): void
    {
        // 2025/12/28は2025年の週番号52の最終日
        $now = CarbonImmutable::parse('2025-12-28 00:00:00', 'JST')->setTimezone('UTC');
        $currentSeasonId = $this->pvpService->getCurrentSeasonId($now);
        $this->assertEquals('2025052', $currentSeasonId);

        // 2025/12/29 ~ 2026/01/04 の週は2026年の週番号1でシーズンIDが取得される
        for( $i = 1; $i <= 7; $i++) {
            // 現在のシーズンIDを取得
            $currentSeasonId = $this->pvpService->getCurrentSeasonId($now->addDays($i));
            $this->assertEquals('2026001', $currentSeasonId);
        }
    }

    public function test_get_prev_sys_season_前シーズン情報が取得できる(): void
    {
        $now = $this->fixTime('2026-1-6');

        // 今週のデータとして作成
        $monday = $now->setISODate(2026, 2, 1);
        SysPvpSeason::factory()->create([
            'id' => '2026002',
            'start_at' => $monday->setTime(3, 0, 0),
            'end_at' => $monday->addDays(6)->setTime(14, 59, 59),
            'closed_at' => $monday->addDays(7)->setTime(2, 59, 59),
        ]);
        // 先週のデータとして作成
        $monday = $now->setISODate(2026, 1, 1);
        SysPvpSeason::factory()->create([
            'id' => '2026001',
            'start_at' => $monday->subWeek()->setTime(3, 0, 0),
            'end_at' => $monday->subWeek()->addDays(6)->setTime(14, 59, 59),
            'closed_at' => $monday->subWeek()->addDays(7)->setTime(2, 59, 59),
        ]);
        $prevSeason = $this->pvpService->getPreviousSysPvpSeason($now);
        $this->assertEquals('2026001', $prevSeason->getId());
    }


    public function test_get_prev_sys_season_前シーズン情報がない場合はエラー(): void
    {
        $now = $this->fixTime('2026-1-6');

        // 今週のデータとして作成
        $monday = $now->setISODate(2026, 2, 1);
        SysPvpSeason::factory()->create([
            'id' => '2026002',
            'start_at' => $monday->setTime(3, 0, 0),
            'end_at' => $monday->addDays(6)->setTime(14, 59, 59),
            'closed_at' => $monday->addDays(7)->setTime(2, 59, 59),
        ]);
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::PVP_SESSION_NOT_FOUND);
        $prevSeason = $this->pvpService->getPreviousSysPvpSeason($now);
    }

    public function test_reset_dairy_challenge_counts_正常系(): void
    {
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $seasonId = $this->pvpService->getCurrentSeasonId($now);

        // マスタPVP作成
        $mstPvp = \App\Domain\Resource\Mst\Models\MstPvp::factory()->create([
            'id' => $seasonId,
            'max_daily_challenge_count' => 5,
            'max_daily_item_challenge_count' => 3,
        ])->toEntity();
        // ユーザPVP作成
        $usrPvp = \App\Domain\Pvp\Models\UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $seasonId,
            'daily_remaining_challenge_count' => 0,
            'daily_remaining_item_challenge_count' => 0,
            'latest_reset_at' => $now->subDays(2)->toDateTimeString(),
        ]);
        // 例外やエラーが起きないことを確認
        $this->pvpService->resetUsrPvp($usrPvp, $mstPvp, $now);
        $this->saveAll();
        $usrPvp = \App\Domain\Pvp\Models\UsrPvp::query()
            ->where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $seasonId)
            ->first();
        // ユーザPVPのチャレンジカウントがリセットされていることを確認
        $this->assertEquals($mstPvp->getMaxDailyChallengeCount(), $usrPvp->getDailyRemainingChallengeCount());
        $this->assertEquals($mstPvp->getMaxDailyItemChallengeCount(), $usrPvp->getDailyRemainingItemChallengeCount());
        // 正常に処理が完了したことを確認
        $this->assertTrue(true);
    }
}
