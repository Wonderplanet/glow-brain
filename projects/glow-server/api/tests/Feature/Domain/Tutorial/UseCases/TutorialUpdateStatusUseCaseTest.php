<?php

declare(strict_types=1);

namespace Feature\Domain\Tutorial\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Enums\GachaUnlockConditionType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Tutorial\Models\UsrTutorial;
use App\Domain\Tutorial\UseCases\TutorialUpdateStatusUseCase;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TutorialUpdateStatusUseCaseTest extends TestCase
{
    private TutorialUpdateStatusUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(TutorialUpdateStatusUseCase::class);
    }

    public static function params_test_exec_正常実行_メインパート()
    {
        return [
            '成功 チュートリアル未プレイ状態から、1つ目完了' => [
                'beforeTutorialStatus' => '',
                'afterTutorialStatus' => 'tutorialContent1',
                'errorCode' => null,
            ],
            '成功 1つ目完了状態から、2つ目完了' => [
                'beforeTutorialStatus' => 'tutorialContent1',
                'afterTutorialStatus' => 'tutorialContent2',
                'errorCode' => null,
            ],
            '成功 GachaConfirmed前から新チュートリアルへの更新' => [
                'beforeTutorialStatus' => 'tutorialContent1',
                'afterTutorialStatus' => 'newTutorialContent1',
                'errorCode' => null,
            ],
            '成功 旧チュートリアルのGachaConfirmedから完了への更新' => [
                'beforeTutorialStatus' => TutorialFunctionName::GACHA_CONFIRMED->value,
                'afterTutorialStatus' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
                'errorCode' => null,
            ],
            '失敗 GachaConfirmed前からMainPartCompletedへの更新は禁止' => [
                'beforeTutorialStatus' => 'tutorialContent1',
                'afterTutorialStatus' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
                'errorCode' => ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
            ],
            '失敗 新チュートリアル範囲ではsort_order+1制限あり' => [
                'beforeTutorialStatus' => 'newTutorialContent1',
                'afterTutorialStatus' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
                'errorCode' => ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
            ],
            '失敗 2つ目完了状態から、無効な値' => [
                'beforeTutorialStatus' => 'tutorialContent2',
                'afterTutorialStatus' => 'invalid',
                'errorCode' => ErrorCode::MST_NOT_FOUND,
            ],
            '失敗 現在のカレントがそもそも無効' => [
                'beforeTutorialStatus' => 'invalid',
                'afterTutorialStatus' => 'tutorialContent1',
                'errorCode' => ErrorCode::MST_NOT_FOUND,
            ],
        ];
    }

    #[DataProvider('params_test_exec_正常実行_メインパート')]
    public function test_exec_正常実行_メインパート(string $beforeTutorialStatus, string $afterTutorialStatus, ?int $errorCode)
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'tutorial_status' => $beforeTutorialStatus,
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // UsrUserParameterを作成
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // ダイヤ情報を作成
        $this->createDiamond($usrUserId);

        // mst
        MstTutorial::factory()->createMany([
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 1,
                'function_name' => 'tutorialContent1',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => 'tutorialContent2',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 3,
                'function_name' => TutorialFunctionName::GACHA_CONFIRMED->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 4,
                'function_name' => 'tutorialContent3',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                // 旧チュートリアルの完了の1つ前
                'type' => TutorialType::MAIN,
                'sort_order' => 5,
                'function_name' => TutorialFunctionName::START_MAIN_PART3->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                // 新チュートリアル範囲 (sort_order > StartMainPart3)
                'type' => TutorialType::MAIN,
                'sort_order' => 6,
                'function_name' => 'newTutorialContent1',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 7,
                'function_name' => 'newTutorialContent2',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 8,
                'function_name' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ]);

        if ($errorCode !== null) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $afterTutorialStatus,
            UserConstant::PLATFORM_IOS,
        );

        // Verify
        // return確認
        if ($afterTutorialStatus !== TutorialFunctionName::MAIN_PART_COMPLETED->value) {
            $this->assertNull($result->usrIdleIncentive);
        }
        $this->assertCount(0, $result->usrGachas);
        $this->assertCount(0, $result->missionDailyBonusRewards);
        $this->assertCount(0, $result->missionEventDailyBonusRewards);
        $this->assertCount(0, $result->usrMissionEventDailyBonusProgresses);
        $this->assertNotNull($result->usrParameterData);
        $this->assertNotNull($result->userLevelUpData);
        $this->assertCount(0, $result->usrUnits);
        $this->assertCount(0, $result->usrItems);
        $this->assertCount(0, $result->usrEmblems);
        $this->assertCount(0, $result->usrConditionPacks);

        // DB確認
        $usrUser->refresh();
        $this->assertEquals($afterTutorialStatus, $usrUser->tutorial_status);

        // log_tutorial_actions テーブルのレコード存在確認
        $this->assertDatabaseHas('log_tutorial_actions', [
            'usr_user_id' => $usrUserId,
            'tutorial_name' => $afterTutorialStatus,
        ]);
    }

    public function test_exec_メインパート完了でガシャが開放される()
    {
        // Setup
        $beforeTutorialStatus = TutorialFunctionName::START_MAIN_PART3->value;
        $afterTutorialStatus = TutorialFunctionName::MAIN_PART_COMPLETED->value;
        $usrUser = $this->createUsrUser([
            'tutorial_status' => $beforeTutorialStatus,
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // UsrUserParameterを作成
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // ダイヤ情報を作成
        $this->createDiamond($usrUserId);

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
                'sort_order' => 1,
                'function_name' => $beforeTutorialStatus,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => $afterTutorialStatus,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ]);

        $durationHours = 10;
        $oprGacha = OprGacha::factory()->create([
            'unlock_condition_type' => GachaUnlockConditionType::MAIN_PART_TUTORIAL_COMPLETE->value,
            'unlock_duration_hours' => $durationHours
        ])->toEntity();

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $afterTutorialStatus,
            UserConstant::PLATFORM_IOS,
        );

        // Verify
        // return確認
        $this->assertCount(1, $result->usrGachas);
        $this->assertCount(0, $result->missionDailyBonusRewards);
        $this->assertCount(0, $result->missionEventDailyBonusRewards);
        $this->assertCount(0, $result->usrMissionEventDailyBonusProgresses);
        $this->assertNotNull($result->usrParameterData);
        $this->assertNotNull($result->userLevelUpData);
        $this->assertCount(0, $result->usrUnits);
        $this->assertCount(0, $result->usrItems);
        $this->assertCount(0, $result->usrEmblems);
        $this->assertCount(0, $result->usrConditionPacks);

        // DB確認
        $usrUser->refresh();
        $this->assertEquals($afterTutorialStatus, $usrUser->tutorial_status);

        $usrGacha = UsrGacha::query()->where('usr_user_id', $usrUserId)->where('opr_gacha_id', $oprGacha->getId())->first();
        $this->assertNotNull($usrGacha);
        $this->assertEquals($now->addHours($durationHours)->toDateTimeString(), $usrGacha->expires_at);

        // log_tutorial_actions テーブルのレコード存在確認
        $this->assertDatabaseHas('log_tutorial_actions', [
            'usr_user_id' => $usrUserId,
            'tutorial_name' => $afterTutorialStatus,
        ]);
    }

    /**
     * 09:50にアカウント新規登録をして、チュートリアルメインパートを10分程度プレイした後に、
     * 10:00にメインパート完了を実行した場合のテスト
     */
    public function test_exec_メインパート完了した時間が探索の放置開始日時となる()
    {
        // Setup
        $beforeTutorialStatus = TutorialFunctionName::START_MAIN_PART3->value;
        $afterTutorialStatus = TutorialFunctionName::MAIN_PART_COMPLETED->value;
        $usrUser = $this->createUsrUser([
            'tutorial_status' => $beforeTutorialStatus,
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime('2025-04-03 10:00:00');
        $currentUser = new CurrentUser($usrUserId);

        // UsrUserParameterを作成
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // ダイヤ情報を作成
        $this->createDiamond($usrUserId);

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
                'sort_order' => 1,
                'function_name' => TutorialFunctionName::START_MAIN_PART3->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => $afterTutorialStatus,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ]);

        $usrIdleIncentive = UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUserId,
            'idle_started_at' => '2025-04-03 09:50:00',
        ]);

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $afterTutorialStatus,
            UserConstant::PLATFORM_IOS,
        );

        // Verify
        // return確認
        $this->assertNotNull($result->usrIdleIncentive);
        $this->assertEquals($now->toDateTimeString(), $result->usrIdleIncentive->getIdleStartedAt());
        $this->assertCount(0, $result->usrGachas);
        $this->assertCount(0, $result->missionDailyBonusRewards);
        $this->assertCount(0, $result->missionEventDailyBonusRewards);
        $this->assertCount(0, $result->usrMissionEventDailyBonusProgresses);
        $this->assertNotNull($result->usrParameterData);
        $this->assertNotNull($result->userLevelUpData);
        $this->assertCount(0, $result->usrUnits);
        $this->assertCount(0, $result->usrItems);
        $this->assertCount(0, $result->usrEmblems);
        $this->assertCount(0, $result->usrConditionPacks);

        // DB確認
        $usrUser->refresh();
        $this->assertEquals($afterTutorialStatus, $usrUser->tutorial_status);

        $usrIdleIncentive->refresh();
        $this->assertEquals($now->toDateTimeString(), $usrIdleIncentive->idle_started_at);

        // log_tutorial_actions テーブルのレコード存在確認
        $this->assertDatabaseHas('log_tutorial_actions', [
            'usr_user_id' => $usrUserId,
            'tutorial_name' => $afterTutorialStatus,
        ]);
    }

    public static function params_test_exec_正常実行_フリーパート()
    {
        return [
            '成功 初回フリーパート' => [
                'completeMstTutorialIds' => [],
                'addFunctionName' => 'free1',
                'errorCode' => null,
            ],
            '成功 2回目フリーパート' => [
                'completeMstTutorialIds' => ['id_free1'],
                'addFunctionName' => 'free2',
                'errorCode' => null,
            ],
            '成功 3回目フリーパート' => [
                'completeMstTutorialIds' => ['id_free1', 'id_free2'],
                'addFunctionName' => 'free3',
                'errorCode' => null,
            ],
            '失敗 3回目フリーパート ステータス不変でエラーは出さない' => [
                'completeMstTutorialIds' => ['id_free1', 'id_free2', 'id_free3'],
                'addFunctionName' => 'free3',
                'errorCode' => null,
            ],
            '失敗 無効な値でリクエスト' => [
                'completeMstTutorialIds' => [],
                'addFunctionName' => 'invalid',
                'errorCode' => ErrorCode::MST_NOT_FOUND,
            ],
        ];
    }

    #[DataProvider('params_test_exec_正常実行_フリーパート')]
    public function test_exec_正常実行_フリーパート(array $completeMstTutorialIds, string $addFunctionName, ?int $errorCode)
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // UsrUserParameterを作成
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // ダイヤ情報を作成
        $this->createDiamond($usrUserId);

        // mst
        $mstTutorials = MstTutorial::factory()->createMany([
            [
                'id' => 'id_free1',
                'type' => TutorialType::FREE,
                'function_name' => 'free1',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'id' => 'id_free2',
                'type' => TutorialType::FREE,
                'function_name' => 'free2',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'id' => 'id_free3',
                'type' => TutorialType::FREE,
                'function_name' => 'free3',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ])->keyBy('function_name');

        // usr
        UsrTutorial::factory()->createMany(array_map(function ($mstTutorialId) use ($usrUserId) {
            return [
                'usr_user_id' => $usrUserId,
                'mst_tutorial_id' => $mstTutorialId,
            ];
        }, $completeMstTutorialIds));

        // error
        if ($errorCode !== null) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $result = $this->useCase->exec(
            $currentUser,
            $addFunctionName,
            UserConstant::PLATFORM_IOS,
        );

        // Verify
        // return確認
        $this->assertCount(0, $result->usrGachas);
        $this->assertCount(0, $result->missionDailyBonusRewards);
        $this->assertCount(0, $result->missionEventDailyBonusRewards);
        $this->assertCount(0, $result->usrMissionEventDailyBonusProgresses);
        $this->assertNotNull($result->usrParameterData);
        $this->assertNotNull($result->userLevelUpData);
        $this->assertCount(0, $result->usrUnits);
        $this->assertCount(0, $result->usrItems);
        $this->assertCount(0, $result->usrEmblems);
        $this->assertCount(0, $result->usrConditionPacks);

        // DB確認
        $usrTutorials = UsrTutorial::query()
            ->where('usr_user_id', $usrUserId)
            ->get()
            ->map->getMstTutorialId();

        $this->assertEqualsCanonicalizing(
            array_unique(
                array_merge(
                    $completeMstTutorialIds,
                    [$mstTutorials[$addFunctionName]->id],
                )
            ),
            $usrTutorials->values()->toArray(),
        );

        // log_tutorial_actions テーブルのレコード存在確認
        $this->assertDatabaseHas('log_tutorial_actions', [
            'usr_user_id' => $usrUserId,
            'tutorial_name' => $addFunctionName,
        ]);
    }
}
