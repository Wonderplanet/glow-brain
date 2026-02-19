<?php

namespace Feature\Domain\Stage\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Enums\InGameSpecialRuleType;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstInGameSpecialRule;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\Services\StageContinueService;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Mst\Models\MstConfig;
use Tests\TestCase;

class StageContinueServiceTest extends TestCase
{
    private StageContinueService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StageContinueService::class);
    }

    public function test_checkStageEventSettingContinue_コンティニュー可能()
    {
        // Setup
        $now = $this->fixTime();

        // Exercise
        $this->service->checkStageEventContinue(QuestType::EVENT, 'stage1', $now);

        // Verify
        $this->assertTrue(true);
    }

    public function test_checkStageEventSettingContinue_スピードアタック不可()
    {
        // Setup
        $now = $this->fixTime();

        MstInGameSpecialRule::factory()->create([
            'content_type' => InGameContentType::STAGE,
            'target_id' => '1',
            'rule_type' => InGameSpecialRuleType::SPEED_ATTACK,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_CAN_NOT_CONTINUE);
        $this->expectExceptionMessage('stage can not continue (mst_stage_id: 1)');

        // Exercise
        $this->service->checkStageEventContinue(QuestType::EVENT, '1', $now);
    }

    public function test_checkStageContinueLimit_コンティニュー可能()
    {
        // Setup
        $usrStageSession = UsrStageSession::factory()->create([
            'continue_count' => 0,
        ]);

        // Exercise
        $this->service->checkStageContinueLimit($usrStageSession);

        // Verify
        $this->assertTrue(true);
    }

    public function test_checkStageContinueLimit_コンティニュー不可()
    {
        // Setup
        $usrStageSession = UsrStageSession::factory()->create([
            'continue_count' => 1,
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_CONTINUE_LIMIT);

        // Exercise
        $this->service->checkStageContinueLimit($usrStageSession);
    }

    public function test_checkDailyStageContinueAdLimit_コンティニュー可能()
    {
        // Setup
        $now = $this->fixTime();
        $usrStageSession = UsrStageSession::factory()->create([
            'daily_continue_ad_count' => 0,
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::AD_CONTINUE_MAX_COUNT,
            'value' => '1',
        ]);

        // Exercise
        $this->service->checkDailyStageContinueAdLimit($usrStageSession);

        // Verify
        $this->assertTrue(true);
    }

    public function test_checkDailyStageContinueAdLimit_コンティニュー不可()
    {
        // Setup
        $now = $this->fixTime();
        $usrStageSession = UsrStageSession::factory()->create([
            'daily_continue_ad_count' => 1,
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::AD_CONTINUE_MAX_COUNT,
            'value' => '1',
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::STAGE_CONTINUE_LIMIT);

        // Exercise
        $this->service->checkDailyStageContinueAdLimit($usrStageSession);
    }

    public function test_continue_正常実行()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'continue_count' => 0,
        ]);

        // Exercise
        $this->service->continue($usrStageSession);
        $this->saveAll();

        // Verify
        $usrStageSession->refresh();
        $this->assertEquals(1, $usrStageSession->continue_count);
    }


    public function test_continueAd_正常実行()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'continue_count' => 0,
            'daily_continue_ad_count' => 0,
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::AD_CONTINUE_MAX_COUNT,
            'value' => '1',
        ]);

        // Exercise
        $this->service->continueAd($usrStageSession);
        $this->saveAll();

        // Verify
        $usrStageSession->refresh();
        $this->assertEquals(1, $usrStageSession->continue_count);
        $this->assertEquals(1, $usrStageSession->daily_continue_ad_count);
    }
}
