<?php

namespace Tests\Feature\Domain\Stage;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Enums\InGameSpecialRuleType;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstInGameSpecialRule;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageEventRule;
use App\Domain\Resource\Mst\Models\MstStageEventSetting;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Stage\Constants\StageConstant;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Enums\StageRuleType;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\UseCases\StageContinueAdUseCase;
use Tests\TestCase;

class StageContinueAdUseCaseTest extends TestCase
{
    private StageContinueAdUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(StageContinueAdUseCase::class);
    }

    public function test_exec_広告視聴でコンティニューができる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        $now = $this->fixTime();
        $mstStageId = "10";

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
            'party_no' => 1,
            'continue_count' => StageConstant::CONTINUE_MAX_COUNT - 1,
            'latest_reset_at' => $now->subDay()->toDateTimeString(),
        ]);

        MstConfig::factory()->createMany([
            [
                'key' => MstConfigConstant::AD_CONTINUE_MAX_COUNT,
                'value' => 1,
            ],
            [
                'key' => MstConfigConstant::STAGE_CONTINUE_DIAMOND_AMOUNT,
                'value' => 20,
            ],
        ]);

        $this->createTestData($usrUserId);

        // Exercise
        $results = $this->useCase->exec($currentUser, $mstStageId);
        $this->saveAll();

        // Verify
        // ResultData確認
        $usrStageSession->refresh();
        $this->assertEquals($usrStageSession->getContinueCount(), $results->usrStageStatusData->getContinueCount());
        $this->assertEquals($usrStageSession->getDailyContinueAdCount(), $results->usrStageStatusData->getContinueAdCount());

        // DB確認
        $this->assertEquals(1, $usrStageSession->getContinueCount());
        $this->assertEquals(1, $usrStageSession->getDailyContinueAdCount());
    }

    public function test_exec_一日の回数制限で広告視聴でコンティニューができない()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        $now = $this->fixTime();
        $mstStageId = "10";

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
            'party_no' => 1,
            'continue_count' => StageConstant::CONTINUE_MAX_COUNT - 1,
            'daily_continue_ad_count' => 5,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        MstConfig::factory()->createMany([
            [
                'key' => MstConfigConstant::AD_CONTINUE_MAX_COUNT,
                'value' => 1,
            ],
            [
                'key' => MstConfigConstant::STAGE_CONTINUE_DIAMOND_AMOUNT,
                'value' => 20,
            ],
        ]);
        $this->createTestData($usrUserId);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_CONTINUE_LIMIT);
        $this->expectExceptionMessage('continue ad count limit (daily_continue_ad_count: 5)');

        // Exercise
        $results = $this->useCase->exec($currentUser, $mstStageId);
    }

    public function test_exec_クエストが期限切れの場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        $now = $this->fixTime();
        $mstStageId = "11";

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
            'party_no' => 1,
            'continue_count' => StageConstant::CONTINUE_MAX_COUNT - 1,
            'latest_reset_at' => $now->subDay()->toDateTimeString(),
        ]);

        MstConfig::factory()->create([
            'key' => MstConfigConstant::STAGE_CONTINUE_DIAMOND_AMOUNT,
            'value' => 20,
        ])->toEntity();
        $this->createTestData($usrUserId);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::QUEST_PERIOD_OUTSIDE);
        $this->expectExceptionMessage('mst_quests for the period record is not found. (mst_quest_id: 11)');

        // Exercise
        $results = $this->useCase->exec($currentUser, $mstStageId);
    }

    public function test_exec_スピードアタックはコンティニューできない()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        MstQuest::factory()->create([
            'id' => 'mstQuestId',
            'quest_type' => 'event',
            'start_date' => $now->subDays(1)->toDateTimeString(),
            'end_date' => $now->addDays(1)->toDateTimeString(),
        ]);
        MstStage::factory()->create([
            'id' => 'mstStageId',
            'mst_quest_id' => 'mstQuestId',
        ]);
        MstStageEventSetting::factory()->create([
            'mst_stage_id' => 'mstStageId',
            'clearable_count' => 10,
            'ad_challenge_count' => 10,
        ]);
        MstInGameSpecialRule::factory()->createMany([
            [
                'content_type' => InGameContentType::STAGE,
                'target_id' => 'mstStageId',
                'rule_type' => InGameSpecialRuleType::SPEED_ATTACK,
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->addDays(1)->toDateTimeString(),
            ],
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_CAN_NOT_CONTINUE);
        $this->expectExceptionMessage('stage can not continue');

        // Exercise
        $this->useCase->exec($currentUser, 'mstStageId');
    }

    public function testExec_リミテッドバトルでNoContinueルールがある場合はコンティニューできない()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        $mstQuestId = 'quest1';
        $mstStageId = 'stage1';
        $mstStageRuleGroupId = 'ruleGroup1';
        MstQuest::factory()->create([
            'id' => $mstQuestId,
            'quest_type' => QuestType::EVENT->value,
            'start_date' => $now->subDays(1)->toDateTimeString(),
            'end_date' => $now->addDays(1)->toDateTimeString(),
        ]);
        MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => $mstQuestId,
        ]);
        MstStageEventSetting::factory()->create([
            'mst_stage_id' => $mstStageId,
            'mst_stage_rule_group_id' => $mstStageRuleGroupId,
        ]);
        MstInGameSpecialRule::factory()->create([
            'content_type' => InGameContentType::STAGE->value,
            'target_id' => $mstStageId,
            'rule_type' => StageRuleType::NO_CONTINUE->value,
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_CAN_NOT_CONTINUE);

        // Exercise
        $this->useCase->exec($currentUser, $mstStageId);
    }

    private function createTestData(string $usrUserId): void
    {
        UsrStage::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '10',
                'clear_count' => 0,
                
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => '11',
                'clear_count' => 0,
                
            ],
        ]);

        MstQuest::factory()->createMany([
            [
                'id' => '10',
                'quest_type' => 'normal',
                'start_date' => '2023-01-01 00:00:00',
                'end_date' => '2037-01-01 00:00:00',
            ],
            [
                'id' => '11',
                'quest_type' => 'normal',
                'start_date' => '2023-01-01 00:00:00',
                'end_date' => '2024-01-01 00:00:00',
            ],
        ]);

        MstStage::factory()->createMany([
            [
                'id' => '10',
                'mst_quest_id' => '10',
            ],
            [
                'id' => '11',
                'mst_quest_id' => '11',
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
            ['level' => 3, 'stamina' => 10, 'exp' => 1000],
        ]);
    }

}
