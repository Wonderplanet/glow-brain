<?php

declare(strict_types=1);

namespace Feature\Domain\Tutorial\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Enums\StageSessionStatus;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Tutorial\UseCases\TutorialStageStartUseCase;
use App\Domain\User\Constants\UserConstant;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TutorialStageStartUseCaseTest extends TestCase
{
    private TutorialStageStartUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(TutorialStageStartUseCase::class);
    }

    public static function params_test_exec_チュートリアルステージに挑戦する()
    {
        return [
            '1つ前のチュートリアルステータス' => [
                'beforeStage1Start',
            ],
            '同じチュートリアルステータス' => [
                'Stage1Start',
            ],
        ];
    }

    #[DataProvider('params_test_exec_チュートリアルステージに挑戦する')]
    public function test_exec_チュートリアルステージに挑戦する(string $currentTutorialStatus)
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'tutorial_status' => $currentTutorialStatus,
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // mst
        MstTutorial::factory()->createMany([
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 1,
                'function_name' => 'beforeStage1Start',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => 'Stage1Start',
                'condition_value' => 'tutorial_stage_1',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 3,
                'function_name' => 'Stage1End',
                'condition_value' => 'tutorial_stage_1',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 4,
                'function_name' => TutorialFunctionName::GACHA_CONFIRMED->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 4,
                'function_name' => TutorialFunctionName::START_MAIN_PART3->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ]);
        MstStage::factory()->create([
            'id' => 'tutorial_stage_1',
            'mst_quest_id' => 'tutorial_quest_1',
        ]);
        MstQuest::factory()->create([
            'id' => 'tutorial_quest_1',
            'quest_type' => QuestType::TUTORIAL,
        ]);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'Stage1Start',
            1,
            UserConstant::PLATFORM_IOS,
        );

        // Verify

        // resultData確認
        $this->assertEquals('Stage1Start', $resultData->tutorialStatus);

        $actual = $resultData->usrStageStatus;
        $this->assertEquals(true, $actual->getIsStartedSession());
        $this->assertEquals(InGameContentType::STAGE->value, $actual->getInGameContentType());
        $this->assertEquals('tutorial_stage_1', $actual->getTargetMstId());
        $this->assertEquals(1, $actual->getPartyNo());
        $this->assertEquals(0, $actual->getContinueCount());
        $this->assertEquals(0, $actual->getContinueAdCount());

        // DB確認
        $actual = UsrStage::where('usr_user_id', $usrUserId)->get();
        $this->assertCount(1, $actual);
        $actual = $actual->first();
        $this->assertEquals('tutorial_stage_1', $actual->getMstStageId());
        $this->assertEquals(0, $actual->getClearCount());

        $actual = UsrStageSession::where('usr_user_id', $usrUserId)->get();
        $this->assertCount(1, $actual);
        $actual = $actual->first();
        $this->assertEquals('tutorial_stage_1', $actual->getMstStageId());
        $this->assertEquals(1, $actual->getPartyNo());
        $this->assertEquals(StageSessionStatus::STARTED, $actual->getIsValid());
    }

    public static function params_test_exec_チュートリアルステージに挑戦できない()
    {
        // 成功するステージマスタ設定
        $correctPrepareStageMaster = function () {
            MstStage::factory()->create([
                'id' => 'tutorial_stage_1',
                'mst_quest_id' => 'tutorial_quest_1',
            ]);
            MstQuest::factory()->create([
                'id' => 'tutorial_quest_1',
                'quest_type' => QuestType::TUTORIAL,
            ]);
        };

        return [
            'クエストタイプがTutorialではない' => [
                ErrorCode::MST_NOT_FOUND,
                function () {
                    MstStage::factory()->create([
                        'id' => 'tutorial_stage_1',
                        'mst_quest_id' => 'tutorial_quest_1',
                    ]);
                    MstQuest::factory()->create([
                        'id' => 'tutorial_quest_1',
                        'quest_type' => QuestType::NORMAL,
                    ]);
                },
                'beforeStage1Start',
            ],
            'チュートリアルステージが存在しない' => [
                ErrorCode::MST_NOT_FOUND,
                function () {
                    MstQuest::factory()->create([
                        'id' => 'tutorial_quest_1',
                        'quest_type' => QuestType::TUTORIAL,
                    ]);
                },
                'beforeStage1Start',
            ],
            'チュートリアルクエストが存在しない' => [
                ErrorCode::MST_NOT_FOUND,
                function () {
                    MstStage::factory()->create([
                        'id' => 'tutorial_stage_1',
                        'mst_quest_id' => 'tutorial_quest_1',
                    ]);
                },
                'beforeStage1Start',
            ],
            'チュートリアルステータスが想定外 2つ前' => [
                ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
                $correctPrepareStageMaster,
                'before2Stage1Start',
            ],
            'チュートリアルステータスが想定外 1つ後' => [
                ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
                $correctPrepareStageMaster,
                'Stage1End',
            ],
        ];
    }

    #[DataProvider('params_test_exec_チュートリアルステージに挑戦できない')]
    public function test_exec_チュートリアルステージに挑戦できない(
        int $errorCode,
        callable $prepareStageMaster,
        string $currentTutorialStatus,
    ) {
        // Setup
        $usrUser = $this->createUsrUser([
            'tutorial_status' => $currentTutorialStatus,
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // mst
        MstTutorial::factory()->createMany([
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 1,
                'function_name' => TutorialFunctionName::GACHA_CONFIRMED->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => 'before2Stage1Start',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 3,
                'function_name' => 'beforeStage1Start',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 4,
                'function_name' => 'Stage1Start',
                'condition_value' => 'tutorial_stage_1',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 5,
                'function_name' => 'Stage1End',
                'condition_value' => 'tutorial_stage_1',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 6,
                'function_name' => TutorialFunctionName::START_MAIN_PART3->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ]);
        $prepareStageMaster();

        // error
        $this->expectException(GameException::class);
        $this->expectExceptionCode($errorCode);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'Stage1Start',
            1,
            UserConstant::PLATFORM_IOS,
        );

        // Verify
    }
}
