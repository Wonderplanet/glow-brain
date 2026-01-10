<?php

namespace Tests\Feature\Domain\Stage\Services;

use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\OprCampaign;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageInterface;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\Services\StageStartNormalQuestService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StageStartNormalQuestServiceTest extends TestCase
{
    private StageStartNormalQuestService $stageStartNormalQuestService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stageStartNormalQuestService = app(StageStartNormalQuestService::class);
    }

    #[DataProvider('params_test_startSession_ステージを開始したステータスに更新できる')]
    public function test_startSession_ステージを開始したステータスに更新できる(int $lapCount)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        $mstStage = MstStage::factory()->create([
            'id' => 'stage1',
        ])->toEntity();
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
        ]);
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => 'Normal',
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ])->toEntity();
        $oprCampaigns = OprCampaign::factory()->createMany([
            [
                'id' => 'campaign1',
                'campaign_type' => CampaignType::EXP->value,
                'target_type' => 'NormalQuest',
                'effect_value' => 150,
            ],
            [
                'id' => 'campaign2',
                'campaign_type' => CampaignType::COIN_DROP->value,
                'target_type' => 'NormalQuest',
                'effect_value' => 20,
            ],
        ])->map(fn($campaign) => $campaign->toEntity());

        // Exercise
        $this->stageStartNormalQuestService->startSession(
            $usrUserId,
            $now,
            $mstStage,
            $mstQuest,
            5,
            $oprCampaigns,
            false,
            $lapCount,
        );
        $this->saveAll();

        // Verify
        $usrStageSession = UsrStageSession::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrStageSession);
        $this->assertEquals('stage1', $usrStageSession->getMstStageId());
        $this->assertEquals(5, $usrStageSession->getPartyNo());
        $oprCampaignIds = $oprCampaigns->map(fn($entity) => $entity->getId());
        $this->assertEquals($oprCampaignIds, $usrStageSession->getOprCampaignIds());
    }

    public static function params_test_startSession_ステージを開始したステータスに更新できる()
    {
        return [
            'スタミナブーストなし' => ['lapCount' => 1],
            'スタミナブーストあり' => ['lapCount' => 3],
        ];
    }

    /**
     * @test
     */
    public function unlockStage_未開放ステージが開放されることを確認する()
    {
        // Setup
        $now = $this->fixTime();
        $mstStageId = '6';
        $prevMstStageId = '5';

        $usrUser = $this->createUsrUser();
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
            'prev_mst_stage_id' => $prevMstStageId,
        ])->toEntity();
        $prevMstStage = MstStage::factory()->create([
            'id' => $prevMstStageId,
            'prev_mst_stage_id' => null,
        ])->toEntity();
        $prevUsrStage = UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $prevMstStageId,
            'clear_count' => 5,

        ]);

        // Exercise
        $usrStage = $this->stageStartNormalQuestService->unlockStage($usrUser->getId(), $mstStage, $now);
        $this->saveAll();

        // Verify
        $usrStage = UsrStage::find($usrStage->getId());
        $this->assertNotNull($usrStage);
        $this->assertEquals(0, $usrStage->clear_count);
    }

    /**
     * @test
     */
    public function unlockStage_開放済みであれば開放されないことを確認()
    {
        // Setup
        $now = $this->fixTime();
        $mstStageId = fake()->uuid();
        $prevMstStageId = fake()->uuid();

        $usrUser = $this->createUsrUser();
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
            'prev_mst_stage_id' => $prevMstStageId,
        ])->toEntity();
        $prevMstStage = MstStage::factory()->create([
            'id' => $prevMstStageId,
        ])->toEntity();
        $prevUsrStage = UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $prevMstStageId,
            'clear_count' => 1,
        ]);
        $usrStage = UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStageId,
            'clear_count' => 0,
        ]);
        $beforeRecordCount = UsrStage::where('usr_user_id', $usrUser->getId())->count();

        // Exercise
        $result = $this->stageStartNormalQuestService->unlockStage($usrUser->getId(), $mstStage, $now);

        // Verify
        $this->assertInstanceOf(UsrStageInterface::class, $result);

        $afterRecordCount = UsrStage::where('usr_user_id', $usrUser->getId())->count();
        $this->assertEquals($beforeRecordCount, $afterRecordCount);
    }

    /**
     * @test
     */
    public function unlockStage_初期開放ステージが開放されることを確認する()
    {
        // Setup
        $now = $this->fixTime();
        $mstStageId = '7';

        $usrUser = $this->createUsrUser();
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
            'prev_mst_stage_id' => null,
        ])->toEntity();

        // Exercise
        $usrStage = $this->stageStartNormalQuestService->unlockStage($usrUser->getId(), $mstStage, $now);
        $this->saveAll();

        // Verify
        $usrStage = UsrStage::find($usrStage->getId());
        $this->assertNotNull($usrStage);
        $this->assertEquals(0, $usrStage->clear_count);
    }

    public function test_unlockStage_クエスト最終ステージクリア時ハードステージと次クエスト最初ステージが開放されること()
    {
        // 使用するパラメータの決定
        $now = $this->fixTime();
        $lastMstStageId = fake()->uuid();
        $nextDifficultyMstStageId = fake()->uuid();
        $nextQuestMstStageId = fake()->uuid();
        $usrUser = $this->createUsrUser();

        // 前提マスター作成
        MstStage::factory()->create([
            'id' => $lastMstStageId,
            'prev_mst_stage_id' => null,
        ]);
        $nextDifficultyMstStage = MstStage::factory()->create([
            'id' => $nextDifficultyMstStageId,
            'prev_mst_stage_id' => $lastMstStageId,
        ])->toEntity();
        $nextQuestMstStage = MstStage::factory()->create([
            'id' => $nextQuestMstStageId,
            'prev_mst_stage_id' => $lastMstStageId,
        ])->toEntity();

        // 前提トランザクションデータ作成
        UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $lastMstStageId,
            'clear_count' => 1,
        ]);

        // 対象メソッド実行
        $nextDifficultyUsrStage = $this->stageStartNormalQuestService->unlockStage($usrUser->getId(), $nextDifficultyMstStage, $now);
        $nextQuestUsrStage = $this->stageStartNormalQuestService->unlockStage($usrUser->getId(), $nextQuestMstStage, $now);
        $this->saveAll();

        // ハードステージが開放されることを検証
        $usrStage = UsrStage::find($nextDifficultyUsrStage->getId());
        $this->assertNotNull($usrStage);
        $this->assertEquals(0, $usrStage->clear_count);

        // 次クエスト最初ステージが開放されることを検証
        $usrStage = UsrStage::find($nextQuestUsrStage->getId());
        $this->assertNotNull($usrStage);
        $this->assertEquals(0, $usrStage->clear_count);
    }
}
