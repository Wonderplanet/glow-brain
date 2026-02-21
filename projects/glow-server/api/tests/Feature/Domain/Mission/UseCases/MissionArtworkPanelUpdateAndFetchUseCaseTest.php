<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Mission\UseCases;

use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Mission\UseCases\MissionArtworkPanelUpdateAndFetchUseCase;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Domain\Resource\Mst\Models\MstArtworkPanelMission;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use App\Http\Responses\Data\UsrMissionStatusData;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class MissionArtworkPanelUpdateAndFetchUseCaseTest extends TestCase
{
    private MissionArtworkPanelUpdateAndFetchUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = $this->app->make(MissionArtworkPanelUpdateAndFetchUseCase::class);
    }

    public function test_exec_原画パネルミッション進捗を取得できる(): void
    {
        // Setup
        $now = $this->fixTime('2025-01-15 12:00:00');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);

        // マスターデータ作成
        MstArtwork::factory()->create(['id' => 'artwork_001']);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001_1', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_001_2', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_001_3', 'mst_artwork_id' => 'artwork_001'],
        ]);

        // 原画パネルミッション作成（初期開放かけらあり）
        $mstArtworkPanelMission = MstArtworkPanelMission::factory()->create([
            'id' => 'panel_mission_001',
            'mst_artwork_id' => 'artwork_001',
            'initial_open_mst_artwork_fragment_id' => 'fragment_001_1',
            'start_at' => '2025-01-01 00:00:00',
            'end_at' => '2025-12-31 23:59:59',
        ]);

        MstMissionLimitedTerm::factory()->create([
            'id' => 'mission_1',
            'progress_group_key' => 'artwork_panel_mission_1',
            'criterion_type' => MissionCriterionType::COIN_COLLECT->value,
            'criterion_value' => null,
            'criterion_count' => 100,
            'mission_category' => MissionLimitedTermCategory::ARTWORK_PANEL->value,
            'mst_mission_reward_group_id' => 'reward_group_1',
            'start_at' => '2025-01-01 00:00:00',
            'end_at' => '2025-01-31 23:59:59',
        ]);

        UsrMissionLimitedTerm::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_mission_limited_term_id' => 'mission_1',
            'status' => MissionStatus::UNCLEAR->value,
            'is_open' => MissionUnlockStatus::OPEN->value,
            'progress' => 50,
            'cleared_at' => null,
            'received_reward_at' => null,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        $resultData = $this->useCase->exec($currentUser);
        $this->saveAll();

        // Verify - resultDataの型確認
        $this->assertNotNull($resultData);

        // Verify - usrMissionLimitedTermsの構造確認
        $this->assertNotNull($resultData->usrMissionLimitedTerms);
        $actual = $resultData->usrMissionLimitedTerms->first();
        $this->assertInstanceOf(UsrMissionStatusData::class, $actual);
        $this->assertEquals('mission_1', $actual->getMstMissionId(), 'ミッションIDが一致すべき');
        $this->assertEquals(50, $actual->getProgress(), '進捗が一致すべき');
        $this->assertFalse($actual->getIsCleared(), 'クリア状態が一致すべき');
        $this->assertFalse($actual->getIsReceivedReward(), '報酬受取状態が一致すべき');

        // Verify - usrArtworkFragmentsの詳細確認
        $this->assertNotNull($resultData->usrArtworkFragments);
        $this->assertCount(1, $resultData->usrArtworkFragments, '初期開放かけらが1つ含まれるべき');
        $fragment = $resultData->usrArtworkFragments->first();
        $this->assertInstanceOf(UsrArtworkFragment::class, $fragment);
        $this->assertEquals('fragment_001_1', $fragment->mst_artwork_fragment_id, 'かけらIDが一致すべき');
        $this->assertEquals('artwork_001', $fragment->mst_artwork_id, '原画IDが一致すべき');

        // Verify - データベースに初期開放かけらが保存されている
        $usrFragment = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_001_1')
            ->first();
        $this->assertNotNull($usrFragment, '初期開放かけらがデータベースに保存されているべき');
        $this->assertEquals('artwork_001', $usrFragment->getMstArtworkId(), '保存された原画IDが一致すべき');
    }

    /**
     * 初期開放かけらが付与されることを確認するテスト
     */
    public function test_exec_初期開放かけらが付与される(): void
    {
        // Setup
        $now = $this->fixTime('2025-01-15 12:00:00');
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);

        // マスターデータ作成
        MstArtwork::factory()->create(['id' => 'artwork_001']);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment_001_1', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_001_2', 'mst_artwork_id' => 'artwork_001'],
            ['id' => 'fragment_001_3', 'mst_artwork_id' => 'artwork_001'],
        ]);

        MstArtworkPanelMission::factory()->create([
            'id' => 'panel_mission_001',
            'mst_artwork_id' => 'artwork_001',
            'initial_open_mst_artwork_fragment_id' => 'fragment_001_1', // 初期開放かけらあり
            'start_at' => '2025-01-01 00:00:00',
            'end_at' => '2025-12-31 23:59:59',
        ]);

        // Exercise
        $resultData = $this->useCase->exec($currentUser);
        $this->saveAll();

        // Verify - 初期開放かけらが付与されている
        $usrFragment = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_fragment_id', 'fragment_001_1')
            ->first();
        $this->assertNotNull($usrFragment, '初期開放かけらが付与されていません');

        // Verify - ResultDataに初期開放かけらが含まれている
        $this->assertCount(1, $resultData->usrArtworkFragments, 'ResultDataに1つのかけらが含まれるべき');
        $this->assertEquals('fragment_001_1', $resultData->usrArtworkFragments->first()->getMstArtworkFragmentId());
    }

}
