<?php

namespace Tests\Feature\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\Services\AdventBattleService;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class AdventBattleServiceTest extends TestCase
{
    private AdventBattleService $adventBattleService;
    private UsrUserParameterRepository $usrUserParameterRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adventBattleService = app(AdventBattleService::class);
    }

    public function test_getActiveUsrAdventBattles_正常実行()
    {
        // Setup
        $now = $this->fixTime();
        $activeMstAdventBattleId = 'advent_battle_1';
        $inactiveMstAdventBattleId = 'advent_battle_2';
        MstAdventBattle::factory()->createMany([
            [
                'id' => $activeMstAdventBattleId,
                'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
                'start_at' => now()->subDay()->toDateTimeString(),
                'end_at' => now()->addDay()->toDateTimeString(),
            ],
            [
                'id' => $inactiveMstAdventBattleId,
                'advent_battle_type' => AdventBattleType::RAID->value,
                'start_at' => now()->subDays(2)->toDateTimeString(),
                'end_at' => now()->subDay()->toDateTimeString(),
            ],
        ]);

        $usrUser = $this->createUsrUser();
        $usrAdventBattles = UsrAdventBattle::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mst_advent_battle_id' => $activeMstAdventBattleId,
                'max_score' => 10000,
                'total_score' => 20000,
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mst_advent_battle_id' => $inactiveMstAdventBattleId,
                'max_score' => 20000,
                'total_score' => 30000,
            ],
        ]);

        // Exercise
        $actuals = $this->adventBattleService->getActiveUsrAdventBattles($usrUser->getUsrUserId(), $now);

        // Verify
        /** @var UsrAdventBattleInterface $actual */
        /** @var UsrAdventBattleInterface[] $usrAdventBattles */
        $actual = $actuals->get($activeMstAdventBattleId);
        $this->assertEquals($usrAdventBattles[0]->getMstAdventBattleId(), $actual->getMstAdventBattleId());
        $this->assertEquals($usrAdventBattles[0]->getMaxScore(), $actual->getMaxScore());
        $this->assertEquals($usrAdventBattles[0]->getTotalScore(), $actual->getTotalScore());

        $actual = $actuals->get($inactiveMstAdventBattleId);
        $this->assertNull($actual);
    }

    public function test_fetchAndResetAdventBattleByAdventBattleId_生成確認()
    {
        // Setup
        $now = $this->fixTime();
        $mstAdventBattleId = 'advent_battle_1';
        $usrUser = $this->createUsrUser();

        // Exercise
        $this->adventBattleService->fetchAndResetAdventBattleByAdventBattleId(
            $usrUser->getUsrUserId(),
            $mstAdventBattleId,
            $now,
        );
        $this->saveAll();

        // Verify
        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $usrUser->getId())
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertNotNull($usrAdventBattle);
        $this->assertEquals(0, $usrAdventBattle->getResetChallengeCount());
        $this->assertEquals(0, $usrAdventBattle->getResetAdChallengeCount());
        $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
    }

    /**
     * @dataProvider params_fetchAndResetAdventBattleByAdventBattleId_リセット確認
     */
    public function test_fetchAndResetAdventBattleByAdventBattleId_リセット確認(
        string $latestResetAt,
        string $now,
        int $beforeCount,
        int $afterCount,
    )
    {
        // Setup
        $now = CarbonImmutable::parse($now, 'Asia/Tokyo');
        $latestResetAt = CarbonImmutable::parse($latestResetAt, 'Asia/Tokyo');
        CarbonImmutable::setTestNow($now);

        $mstAdventBattleId = 'advent_battle_1';
        $usrUser = $this->createUsrUser();
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_advent_battle_id' => $mstAdventBattleId,
            'reset_challenge_count' => $beforeCount,
            'reset_ad_challenge_count' => $beforeCount,
            'latest_reset_at' => $latestResetAt,
        ]);

        // Exercise
        $this->adventBattleService->fetchAndResetAdventBattleByAdventBattleId(
            $usrUser->getUsrUserId(),
            $mstAdventBattleId,
            $now,
        );
        $this->saveAll();

        // Verify
        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $usrUser->getId())
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($afterCount, $usrAdventBattle->getResetChallengeCount());
        $this->assertEquals($afterCount, $usrAdventBattle->getResetAdChallengeCount());
        if ($afterCount === 0) {
            $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        } else {
            $this->assertEquals($latestResetAt->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        }
    }

    public static function params_fetchAndResetAdventBattleByAdventBattleId_リセット確認()
    {
        return [
            '日付を跨ぐ場合' => ['latestResetAt' => '2025-12-21 12:34:55', 'now' => '2025-12-22 10:34:55', 'beforeCount' => 3, 'afterCount' => 0],
            '日付をまたがない場合' => ['latestResetAt' => '2025-12-21 12:34:55', 'now' => '2025-12-21 23:59:59', 'beforeCount' => 3, 'afterCount' => 3],
        ];
    }

    /**
     * @dataProvider params_fetchUsrAdventBattleList_リセット確認
     */
    public function test_fetchUsrAdventBattleList_リセット確認(
        string $latestResetAt,
        string $now,
        int $beforeCount,
        int $afterCount,
    )
    {
        // Setup
        $now = CarbonImmutable::parse($now, 'Asia/Tokyo');
        $latestResetAt = CarbonImmutable::parse($latestResetAt, 'Asia/Tokyo');
        CarbonImmutable::setTestNow($now);

        $mstAdventBattleId = 'advent_battle_1';
        MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $usrUser = $this->createUsrUser();
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_advent_battle_id' => $mstAdventBattleId,
            'reset_challenge_count' => $beforeCount,
            'reset_ad_challenge_count' => $beforeCount,
            'latest_reset_at' => $latestResetAt,
        ]);

        // Exercise
        $usrAdventBattles = $this->adventBattleService->fetchUsrAdventBattleList($usrUser->getUsrUserId(), $now);

        // Verify
        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = $usrAdventBattles->first();
        $this->assertEquals($afterCount, $usrAdventBattle->getResetChallengeCount());
        $this->assertEquals($afterCount, $usrAdventBattle->getResetAdChallengeCount());
        if ($afterCount === 0) {
            $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        } else {
            $this->assertEquals($latestResetAt->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        }

        // DBがリセットされていないか確認
        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($beforeCount, $usrAdventBattle->getResetChallengeCount());
        $this->assertEquals($beforeCount, $usrAdventBattle->getResetAdChallengeCount());
        $this->assertEquals($latestResetAt->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
    }

    public static function params_fetchUsrAdventBattleList_リセット確認()
    {
        return [
            '日付を跨ぐ場合' => ['latestResetAt' => '2025-12-21 12:34:55', 'now' => '2025-12-22 10:34:55', 'beforeCount' => 3, 'afterCount' => 0],
            '日付をまたがない場合' => ['latestResetAt' => '2025-12-21 12:34:55', 'now' => '2025-12-21 23:59:59', 'beforeCount' => 3, 'afterCount' => 3],
        ];
    }

    public function testMakeUsrAdventBattleStatusData_正常取得() {
        // Setup
        $usrUser = $this->createUsrUser();
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_advent_battle_id' => "20",
            'is_valid' => AdventBattleSessionStatus::STARTED->value,
            'party_no' => 5,
        ]);

        // Exercise
        $actual = $this->adventBattleService->makeUsrAdventBattleStatusData($usrUser->getUsrUserId());

        // Verify
        $this->assertTrue($actual->getIsStartedSession());
        $this->assertEquals(InGameContentType::ADVENT_BATTLE->value, $actual->getInGameContentType());
        $this->assertEquals("20", $actual->getTargetMstId());
        $this->assertEquals(5, $actual->getPartyNo());
        $this->assertEquals(0, $actual->getContinueCount());
        $this->assertEquals(0, $actual->getContinueAdCount());
    }
}
