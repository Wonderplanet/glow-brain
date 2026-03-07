<?php

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Services\PvpRankingService;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\Data\PvpRankingData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class PvpRankingServiceTest extends TestCase
{
    private PvpRankingService $pvpRankingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pvpRankingService = $this->app->make(PvpRankingService::class);
    }

    public function testGetRanking_ランキング情報が取得できる()
    {
        // Setup
        $now = $this->fixTime();
        $sysPvpSeason = SysPvpSeason::factory()->create([
            'start_at' => $now->subDays(1),
            'end_at' => $now->addDays(1),
        ])->toEntity();
        $sysPvpSeasonId = $sysPvpSeason->getId();

        $usrUserId = $this->createUsrUser()->getId();
        $usrUserIds = UsrUser::factory(5)->create()->map(fn($usrUser) => $usrUser->getId());
        $usrUserIds->push($usrUserId);
        $key = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $usrUserIds->each(function ($userId, $i) use ($key) {
            UsrUserProfile::factory()->create([
                'usr_user_id' => $userId,
            ]);
            Redis::connection()->zadd($key, [$userId => $i * 100]);
        });
        $score = Redis::connection()->zscore($key, $usrUserId);
        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => $score,
            'last_played_at' => $now,
        ]);

        // Exercise
        $actual = $this->pvpRankingService->getRanking($usrUserId, $sysPvpSeason, $now);

        // Verify
        $this->assertInstanceOf(PvpRankingData::class, $actual);
        $actualArray = $actual->formatToResponse();
        $this->assertArrayHasKey('ranking', $actualArray);
        $this->assertArrayHasKey('myRanking', $actualArray);
        $this->assertCount(6, $actualArray['ranking']);
        $this->assertEquals(1, $actualArray['myRanking']['rank']);

        // keyが存在することを確認
        $ranking = $actualArray['ranking'][0];
        $this->assertArrayHasKey('myId', $ranking);
        $this->assertArrayHasKey('rank', $ranking);
        $this->assertArrayHasKey('name', $ranking);
        $this->assertArrayHasKey('mstUnitId', $ranking);
        $this->assertArrayHasKey('mstEmblemId', $ranking);
        $this->assertArrayHasKey('score', $ranking);

        // キャッシュが保存されていること
        $cache = Redis::connection()->get(CacheKeyUtil::getPvpRankingCacheKey($sysPvpSeasonId));
        $this->assertNotNull($cache);
    }

    public function testGetRanking_シーズン情報がnullの場合空ランキング情報が取得できる()
    {
        // Setup
        $now = $this->fixTime();

        $usrUserId = $this->createUsrUser()->getId();
        $usrUserIds = UsrUser::factory(5)->create()->map(fn($usrUser) => $usrUser->getId());
        $usrUserIds->push($usrUserId);
        $usrUserIds->each(function ($userId) {
            UsrUserProfile::factory()->create([
                'usr_user_id' => $userId,
            ]);
        });

        // Exercise
        $actual = $this->pvpRankingService->getRanking($usrUserId, null, $now);

        // Verify
        $this->assertInstanceOf(PvpRankingData::class, $actual);
        $actualArray = $actual->formatToResponse();
        $this->assertArrayHasKey('ranking', $actualArray);
        $this->assertArrayHasKey('myRanking', $actualArray);
        $this->assertCount(0, $actualArray['ranking']);
        $this->assertEquals(0, $actualArray['myRanking']['rank']);
        $this->assertEquals(0, $actualArray['myRanking']['score']);
        $this->assertFalse($actualArray['myRanking']['isExcludeRanking']);
    }
}
