<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Mission\Services;

use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Mission\Services\MissionArtworkPanelService;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Domain\Resource\Mst\Models\MstArtworkPanelMission;
use Tests\TestCase;

/**
 * MissionArtworkPanelService テスト
 *
 * テスト対象：
 * - createInitialUsrArtworkFragmentsIfNeeded（初期開放かけら作成）
 */
class MissionArtworkPanelServiceTest extends TestCase
{
    private MissionArtworkPanelService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(MissionArtworkPanelService::class);
    }

    /**
     * テスト用のマスターデータを作成する
     */
    private function createMasterData(): void
    {
        // テスト用原画
        MstArtwork::factory()->createMany([
            ['id' => 'artwork_001'],
            ['id' => 'artwork_002'],
        ]);

        // テスト用原画のかけら
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001_1', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_001_2', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_002_1', 'mst_artwork_id' => 'artwork_002'],
        ]);
    }

    /**
     * 初期開放かけらがない場合、何もしないことを確認するテスト
     */
    public function test_createInitialUsrArtworkFragmentsIfNeeded_初期開放かけらがない場合何もしない(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();

        $this->createMasterData();

        // 初期開放かけらがないミッションエンティティを作成（factory + toEntity）
        $mstArtworkPanelMissions = collect([
            MstArtworkPanelMission::factory()->make([
                'id' => 'mission_001',
                'mst_artwork_id' => 'artwork_001',
                'mst_event_id' => 'event_001',
                'initial_open_mst_artwork_fragment_id' => null, // 初期開放かけらなし
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(7)->toDateTimeString(),
            ])->toEntity(),
        ]);

        // Exercise
        $this->service->createInitialUsrArtworkFragmentsIfNeeded($usrUserId, $mstArtworkPanelMissions);
        $this->saveAll();

        // Verify - かけらが作成されていないことを確認
        $usrFragments = UsrArtworkFragment::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(0, $usrFragments->count(), '初期開放かけらがない場合、かけらは作成されないべき');
    }

    /**
     * 初期開放かけらを未所持ユーザーに付与することを確認するテスト
     */
    public function test_createInitialUsrArtworkFragmentsIfNeeded_初期開放かけらを未所持ユーザーに付与する(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();

        $this->createMasterData();

        // 初期開放かけらありのミッションエンティティを作成（factory + toEntity）
        $mstArtworkPanelMissions = collect([
            MstArtworkPanelMission::factory()->make([
                'id' => 'mission_001',
                'mst_artwork_id' => 'artwork_001',
                'mst_event_id' => 'event_001',
                'initial_open_mst_artwork_fragment_id' => 'fragment_001_1', // 初期開放かけらあり
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(7)->toDateTimeString(),
            ])->toEntity(),
            MstArtworkPanelMission::factory()->make([
                'id' => 'mission_002',
                'mst_artwork_id' => 'artwork_002',
                'mst_event_id' => 'event_002',
                'initial_open_mst_artwork_fragment_id' => 'fragment_002_1', // 初期開放かけらあり
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(7)->toDateTimeString(),
            ])->toEntity(),
        ]);

        // Exercise
        $this->service->createInitialUsrArtworkFragmentsIfNeeded($usrUserId, $mstArtworkPanelMissions);
        $this->saveAll();

        // Verify - かけらが作成されていることを確認
        $usrFragments = UsrArtworkFragment::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(2, $usrFragments->count(), '2つのかけらが作成されるべき');

        $mstArtworkFragmentIds = $usrFragments->pluck('mst_artwork_fragment_id')->toArray();
        $this->assertContains('fragment_001_1', $mstArtworkFragmentIds, 'fragment_001_1が作成されていません');
        $this->assertContains('fragment_002_1', $mstArtworkFragmentIds, 'fragment_002_1が作成されていません');
    }

    /**
     * 既所持のかけらは重複作成しないことを確認するテスト
     */
    public function test_createInitialUsrArtworkFragmentsIfNeeded_既所持のかけらは重複作成しない(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();

        $this->createMasterData();

        // 既にfragment_001_1を所持している状態
        UsrArtworkFragment::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => 'artwork_001',
            'mst_artwork_fragment_id' => 'fragment_001_1',
        ]);

        // 初期開放かけらありのミッションエンティティを作成（既所持のかけらを含む）（factory + toEntity）
        $mstArtworkPanelMissions = collect([
            MstArtworkPanelMission::factory()->make([
                'id' => 'mission_001',
                'mst_artwork_id' => 'artwork_001',
                'mst_event_id' => 'event_001',
                'initial_open_mst_artwork_fragment_id' => 'fragment_001_1', // 既所持
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(7)->toDateTimeString(),
            ])->toEntity(),
            MstArtworkPanelMission::factory()->make([
                'id' => 'mission_002',
                'mst_artwork_id' => 'artwork_002',
                'mst_event_id' => 'event_002',
                'initial_open_mst_artwork_fragment_id' => 'fragment_002_1', // 未所持
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(7)->toDateTimeString(),
            ])->toEntity(),
        ]);

        // Exercise
        $this->service->createInitialUsrArtworkFragmentsIfNeeded($usrUserId, $mstArtworkPanelMissions);
        $this->saveAll();

        // Verify - かけらが重複作成されていないことを確認
        $usrFragments = UsrArtworkFragment::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(2, $usrFragments->count(), '重複を除いて2つのかけらが存在するべき');

        // fragment_001_1は1つのみ存在
        $fragment001Count = $usrFragments->where('mst_artwork_fragment_id', 'fragment_001_1')->count();
        $this->assertEquals(1, $fragment001Count, 'fragment_001_1は1つのみ存在するべき（重複作成されていない）');

        // fragment_002_1が新規作成されている
        $fragment002 = $usrFragments->firstWhere('mst_artwork_fragment_id', 'fragment_002_1');
        $this->assertNotNull($fragment002, 'fragment_002_1が新規作成されているべき');
    }
}
