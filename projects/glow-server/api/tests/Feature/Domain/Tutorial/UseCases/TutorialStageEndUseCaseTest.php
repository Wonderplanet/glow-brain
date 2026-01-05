<?php

declare(strict_types=1);

namespace Feature\Domain\Tutorial\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageReward;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Enums\StageRewardCategory;
use App\Domain\Stage\Enums\StageSessionStatus;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Tutorial\UseCases\TutorialStageEndUseCase;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class TutorialStageEndUseCaseTest extends TestCase
{
    private TutorialStageEndUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(TutorialStageEndUseCase::class);
    }

    public function test_exec_チュートリアルステージをクリアする()
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'tutorial_status' => 'Stage1Start',
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
            'level' => 1,
            'exp' => 0,
        ]);
        UsrCurrencySummary::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

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
                'function_name' => 'afterStage1End',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ]);
        MstStage::factory()->create([
            'id' => 'tutorial_stage_1',
            'mst_quest_id' => 'tutorial_quest_1',
            'coin' => 100,
            'exp' => 0,
        ]);
        MstQuest::factory()->create([
            'id' => 'tutorial_quest_1',
            'quest_type' => QuestType::TUTORIAL,
        ]);
        MstStageReward::factory()->createMany([
            [
                'mst_stage_id' => 'tutorial_stage_1',
                'reward_category' => StageRewardCategory::FIRST_CLEAR,
                'resource_type' => RewardType::COIN,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'mst_stage_id' => 'tutorial_stage_1',
                'reward_category' => StageRewardCategory::FIRST_CLEAR,
                'resource_type' => RewardType::EXP,
                'resource_id' => null,
                'resource_amount' => 100,
                'percentage' => 100,
            ],
            [
                'mst_stage_id' => 'tutorial_stage_1',
                'reward_category' => StageRewardCategory::FIRST_CLEAR,
                'resource_type' => RewardType::ITEM,
                'resource_id' => 'item_1',
                'resource_amount' => 1,
                'percentage' => 100,
            ],
            [
                'mst_stage_id' => 'tutorial_stage_1',
                'reward_category' => StageRewardCategory::FIRST_CLEAR,
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_1',
                'resource_amount' => 1,
                'percentage' => 100,
            ],
            [
                'mst_stage_id' => 'tutorial_stage_1',
                'reward_category' => StageRewardCategory::FIRST_CLEAR,
                'resource_type' => RewardType::EMBLEM,
                'resource_id' => 'emblem_1',
                'resource_amount' => 1,
                'percentage' => 100,
            ],
        ]);
        MstItem::factory()->create(['id' => 'item_1']);
        MstUnit::factory()->create(['id' => 'unit_1']);
        MstEmblem::factory()->create(['id' => 'emblem_1']);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
            ['level' => 3, 'stamina' => 10, 'exp' => 1000],
        ]);
        $mstUserLevelBonus = MstUserLevelBonus::factory()->create(['level' => 2])->toEntity();
        MstUserLevelBonusGroup::factory()->create([
            'mst_user_level_bonus_group_id' => $mstUserLevelBonus->getMstUserLevelBonusGroupId(),
            'resource_type' => RewardType::COIN->value,
            'resource_id' => NULL,
            'resource_amount' => 1
        ]);

        // usr
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'tutorial_stage_1',
            'clear_count' => 0,
        ]);
        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'tutorial_stage_1',
            'party_no' => 1,
            'is_valid' => StageSessionStatus::STARTED,
        ]);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'Stage1End',
            UserConstant::PLATFORM_IOS,
        );

        // Verify

        // resultData確認
        $this->assertEquals('Stage1End', $resultData->tutorialStatus);
        $this->assertEquals(100 + 10 + 1, $resultData->usrParameterData->getCoin());
        $this->assertEquals(100, $resultData->usrParameterData->getExp());
        $this->assertEquals(2, $resultData->usrParameterData->getLevel());
        $this->assertCount(1, $resultData->userLevelUpData->levelUpRewards);
        $this->assertCount(1, $resultData->usrItems);
        $this->assertCount(1, $resultData->usrUnits);
        $this->assertCount(1, $resultData->usrEmblems);
        $this->assertCount(5, $resultData->stageFirstClearRewards);

        // DB確認
        $usrUser->refresh();
        $this->assertEquals('Stage1End', $usrUser->getTutorialStatus());

        $actual = UsrStage::where('usr_user_id', $usrUserId)->get();
        $this->assertCount(1, $actual);
        $actual = $actual->first();
        $this->assertEquals('tutorial_stage_1', $actual->getMstStageId());
        $this->assertEquals(1, $actual->getClearCount());

        $actual = UsrStageSession::where('usr_user_id', $usrUserId)->get();
        $this->assertCount(1, $actual);
        $actual = $actual->first();
        $this->assertEquals('tutorial_stage_1', $actual->getMstStageId());
        $this->assertEquals(1, $actual->getPartyNo());
        $this->assertEquals(StageSessionStatus::CLOSED, $actual->getIsValid());

        $usrUserParameter->refresh();
        $this->assertEquals(100 + 10 + 1, $usrUserParameter->getCoin());
    }

    public static function params_test_exec_チュートリアルステージをクリアできない()
    {
        // 成功するステージマスタ設定
        $correctPrepareStageMaster = function () {
            MstStage::factory()->create([
                'id' => 'tutorial_stage_1',
                'mst_quest_id' => 'tutorial_quest_1',
                'exp' => 0, // テストとは関係ないレベルアップをさせないため
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
                        'exp' => 0, // テストとは関係ないレベルアップをさせないため
                    ]);
                    MstQuest::factory()->create([
                        'id' => 'tutorial_quest_1',
                        'quest_type' => QuestType::NORMAL,
                    ]);
                },
                'Stage1Start',
            ],
            'チュートリアルステージが存在しない' => [
                ErrorCode::MST_NOT_FOUND,
                function () {
                    MstQuest::factory()->create([
                        'id' => 'tutorial_quest_1',
                        'quest_type' => QuestType::TUTORIAL,
                    ]);
                },
                'Stage1Start',
            ],
            'チュートリアルクエストが存在しない' => [
                ErrorCode::MST_NOT_FOUND,
                function () {
                    MstStage::factory()->create([
                        'id' => 'tutorial_stage_1',
                        'mst_quest_id' => 'tutorial_quest_1',
                        'exp' => 0, // テストとは関係ないレベルアップをさせないため
                    ]);
                },
                'Stage1Start',
            ],
            'チュートリアルステータスが想定外 2つ前' => [
                ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
                $correctPrepareStageMaster,
                'beforeStage1Start',
            ],
            'チュートリアルステータスが想定外 同じ' => [
                ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
                $correctPrepareStageMaster,
                'Stage1End',
            ],
            'チュートリアルステータスが想定外 1つ後' => [
                ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
                $correctPrepareStageMaster,
                'afterStage1End',
            ],
        ];
    }

    #[DataProvider('params_test_exec_チュートリアルステージをクリアできない')]
    public function test_exec_チュートリアルステージをクリアできない(
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

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

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
                'function_name' => 'afterStage1End',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ]);
        $prepareStageMaster();

        // usr
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'tutorial_stage_1',
            'clear_count' => 0,
        ]);
        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'tutorial_stage_1',
            'party_no' => 1,
            'is_valid' => StageSessionStatus::STARTED,
        ]);

        // error
        $this->expectException(GameException::class);
        $this->expectExceptionCode($errorCode);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'Stage1End',
            UserConstant::PLATFORM_IOS,
        );

        // Verify
    }
}
