<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp\Repositories;

use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Repositories\SysPvpSeasonRepository;
use App\Domain\Pvp\Services\PvpService;
use Tests\TestCase;

class SysPvpSeasonRepositoryTest extends TestCase
{
    private SysPvpSeasonRepository $repository;
    private PvpService $pvpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(SysPvpSeasonRepository::class);
        $this->pvpService = app(PvpService::class);
    }

    public function test_getClosing(): void
    {
        //privateメソッドのgetClosingをテストする
        $now = $this->fixTime();
        $week1 = $now->setISODate(2025, 1, 1)->setTime(0, 0, 0);
        $week2 = $now->setISODate(2025, 2, 1)->setTime(0, 0, 0);
        SysPvpSeason::factory()->createMany([
            [
                'id' => $this->pvpService->getCurrentSeasonId($week1),
                'start_at' => $week1->setTime(3, 0, 0),
                'end_at' => $week1->addDays(6)->setTime(14, 59, 59),
                'closed_at' => $week1->addDays(7)->setTime(2, 59, 59),
            ],
            [
                'id' => $this->pvpService->getCurrentSeasonId($week2),
                'start_at' => $week2->setTime(3, 0, 0),
                'end_at' => $week2->addDays(6)->setTime(14, 59, 59),
                'closed_at' => $week2->addDays(7)->setTime(2, 59, 59),
            ],
        ]);
        $result = $this->execPrivateMethod(
            $this->repository,
            'getClosing',
            [$week2]
        );

        // 期待される結果を確認
        $this->assertNotNull($result);
        $this->assertEquals($this->pvpService->getCurrentSeasonId($week1), $result->getId());
    }
}
