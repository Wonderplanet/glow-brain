<?php

namespace Tests\Feature\Domain\AdventBattle\Repositories;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRepository;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class MstAdventBattleRepositoryTest extends TestCase
{
    private MstAdventBattleRepository $mstAdventBattleRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mstAdventBattleRepository = app(MstAdventBattleRepository::class);
    }

    public function testGetWithinRewardReceivePeriod_報酬受け取り期間内のデータのみ取得できる()
    {
        // Setup
        $now = $this->fixTime();
        $aggregateHours = 48;
        $rewardReceivableHours = AdventBattleConstant::SEASON_REWARD_LIMIT_DAYS * 24 + $aggregateHours;
        MstAdventBattle::factory()->createMany([
            // 開催中(対象外)
            ['id' => '1', 'start_at' => $now->subHours(), 'end_at' => $now->addHours()],
            // 報酬受け取り期日前(対象)
            ['id' => '2', 'start_at' => $now->subHours($rewardReceivableHours), 'end_at' => $now->subHours($rewardReceivableHours - 1)],
            // 報酬受け取り期日を過ぎている(対象外)
            ['id' => '3', 'start_at' => $now->subHours($rewardReceivableHours + 2), 'end_at' => $now->subHours($rewardReceivableHours + 1)],
        ]);

        // Exercise
        $actual = $this->mstAdventBattleRepository->getWithinRewardReceivePeriod($now, $aggregateHours);

        // Verify
        $this->assertCount(1, $actual);
        $this->assertEquals('2', $actual->first()->getId());
    }

    public function testGetPreviousMstAdventBattleId_前回のアドベントバトルIDを取得できる()
    {
        // Setup
        $now = $this->fixTime();
        MstAdventBattle::factory()->createMany([
            ['id' => '1', 'start_at' => $now->subDays(10), 'end_at' => $now->subDays(5)],
            ['id' => '2', 'start_at' => $now->subDays(4), 'end_at' => $now->subDays(2)],
            ['id' => '3', 'start_at' => $now->subDays(1), 'end_at' => $now],
        ]);

        // Exercise
        $actual = $this->mstAdventBattleRepository->getPreviousMstAdventBattle('3');

        // Verify
        $this->assertNotNull($actual);
        $this->assertEquals('2', $actual->getId());
    }
}
