# ミッションテスト実装ガイド

ミッション機能のテスト実装方法を解説します。

## 目次

1. [テストの種類](#テストの種類)
2. [TestMissionTraitの使い方](#testmissiontraitの使い方)
3. [Criterionテスト](#criterionテスト)
4. [トリガー送信テスト](#トリガー送信テスト)
5. [ミッション進捗更新テスト](#ミッション進捗更新テスト)
6. [UseCase E2Eテスト](#usecase-e2eテスト)
7. [テストパターン集](#テストパターン集)

## テストの種類

ミッション関連のテストは以下の階層で実装します：

```
1. Criterionテスト（単体）
   └ 達成判定ロジックのテスト

2. トリガー送信テスト
   └ 正しいトリガーが送信されるかのテスト

3. MissionUpdateServiceテスト
   └ 進捗更新ロジックのテスト

4. UseCase E2Eテスト
   └ 実際のAPIフロー全体のテスト
```

## TestMissionTraitの使い方

**ファイル**: `api/tests/Support/Traits/TestMissionTrait.php`

### 主要メソッド

#### 1. ミッション状態確認

```php
use Tests\Support\Traits\TestMissionTrait;

class MyMissionTest extends TestCase
{
    use TestMissionTrait;

    public function test_ミッション達成()
    {
        // ミッション進捗更新
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // ユーザーミッションデータ取得
        $usrMissions = $this->getUsrMissions($usrUserId, MissionType::ACHIEVEMENT);

        // 状態確認
        $this->checkUsrMissionStatus(
            $usrMissions,
            $mstMissionId,
            isExist: true,
            isClear: true,
            clearedAt: $now->toDateTimeString(),
            isReceiveReward: false,
            receivedRewardAt: null
        );
    }
}
```

#### 2. マスタデータ作成

```php
// ミッションマスタ作成
$mstMission = $this->createMstMission(
    MissionType::ACHIEVEMENT,
    $mstMissionId,
    MissionCriterionType::STAGE_CLEAR_COUNT,
    criterionValue: null,
    criterionCount: 10,
    rewardGroupId: 'reward_001',
);

// 報酬マスタ作成
$this->createMstReward(
    groupId: 'reward_001',
    resourceType: RewardType::COIN,
    resourceId: null,
    resourceAmount: 1000,
);
```

#### 3. ユーザーミッションデータ作成

```php
$this->createUsrMissionNormal(
    $usrUserId,
    MissionType::ACHIEVEMENT,
    $mstMissionId,
    MissionStatus::UNCLEAR,
    progress: 5,
    clearedAt: null,
    receivedRewardAt: null,
    latestResetAt: $now->toDateTimeString(),
);
```

#### 4. トリガー確認

```php
// トリガーが正しく送信されているか確認
$this->checkTriggerAndAggregatedProgress(
    MissionCriterionType::STAGE_CLEAR_COUNT,
    criterionValue: null,
    expectedProgress: 1,
    isExist: true,
);

// トリガーが存在しないことを確認
$this->checkExistMissionManagerTriggers(isExist: false);
```

## Criterionテスト

### テストファイル配置

**配置場所**: `api/tests/Feature/Domain/Mission/Criteria/`

**命名規則**: `{CriterionType}CriterionTest.php`

### テンプレート

```php
<?php

namespace Tests\Feature\Domain\Mission\Criteria;

use App\Domain\Mission\Entities\Criteria\StageClearCountCriterion;
use App\Domain\Mission\Enums\MissionCriterionType;
use Tests\TestCase;

class StageClearCountCriterionTest extends TestCase
{
    /**
     * @test
     * @dataProvider progressDataProvider
     */
    public function 進捗値による達成判定(
        int $criterionCount,
        int $initialProgress,
        int $addProgress,
        bool $expectedClear,
        int $expectedProgress,
    ): void {
        // Arrange
        $criterion = new StageClearCountCriterion(
            criterionValue: null,
            progress: $initialProgress,
        );
        $criterion->setCriterionCount($criterionCount);

        // Act
        $criterion->aggregateProgress($addProgress);

        // Assert
        $this->assertEquals($expectedClear, $criterion->isClear());
        $this->assertEquals($expectedProgress, $criterion->getProgress());
    }

    public static function progressDataProvider(): array
    {
        return [
            '未達成' => [
                'criterionCount' => 10,
                'initialProgress' => 5,
                'addProgress' => 3,
                'expectedClear' => false,
                'expectedProgress' => 8,
            ],
            '達成' => [
                'criterionCount' => 10,
                'initialProgress' => 5,
                'addProgress' => 5,
                'expectedClear' => true,
                'expectedProgress' => 10,
            ],
            '超過達成' => [
                'criterionCount' => 10,
                'initialProgress' => 5,
                'addProgress' => 10,
                'expectedClear' => true,
                'expectedProgress' => 15,
            ],
        ];
    }

    /** @test */
    public function criterionKeyが正しく生成される(): void
    {
        $criterion = new StageClearCountCriterion(null, 0);

        $this->assertEquals(
            MissionCriterionType::STAGE_CLEAR_COUNT->value,
            $criterion->getCriterionKey()
        );
    }
}
```

### criterionValue使用パターン

```php
class SpecificStageClearCountCriterionTest extends TestCase
{
    /** @test */
    public function criterionKeyが正しく生成される(): void
    {
        $criterion = new SpecificStageClearCountCriterion('stage_001', 0);

        $expectedKey = MissionUtil::makeCriterionKey(
            MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
            'stage_001'
        );

        $this->assertEquals($expectedKey, $criterion->getCriterionKey());
    }

    /** @test */
    public function 異なるcriterionValueは別々に集計される(): void
    {
        $criterion1 = new SpecificStageClearCountCriterion('stage_001', 0);
        $criterion2 = new SpecificStageClearCountCriterion('stage_002', 0);

        $this->assertNotEquals(
            $criterion1->getCriterionKey(),
            $criterion2->getCriterionKey()
        );
    }
}
```

### 最大値判定パターン（レベル系）

```php
class UnitLevelCriterionTest extends TestCase
{
    /** @test */
    public function 最大値が保持される(): void
    {
        $criterion = new UnitLevelCriterion(null, 10);
        $criterion->setCriterionCount(50);

        // より小さい値を集約
        $criterion->aggregateProgress(5);
        $this->assertEquals(10, $criterion->getProgress(), '小さい値は無視される');

        // より大きい値を集約
        $criterion->aggregateProgress(30);
        $this->assertEquals(30, $criterion->getProgress(), '大きい値で更新される');

        // さらに大きい値を集約
        $criterion->aggregateProgress(45);
        $this->assertEquals(45, $criterion->getProgress(), '最大値が保持される');
    }
}
```

## トリガー送信テスト

### テストファイル配置

**配置場所**: `api/tests/Feature/Domain/{Domain}/Services/{Domain}MissionTriggerServiceTest.php`

### 基本パターン

```php
<?php

namespace Tests\Feature\Domain\Stage\Services;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Stage\Services\StageMissionTriggerService;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class StageMissionTriggerServiceTest extends TestCase
{
    use TestMissionTrait;

    private StageMissionTriggerService $stageMissionTriggerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stageMissionTriggerService = $this->app->make(StageMissionTriggerService::class);
    }

    /** @test */
    public function ステージクリア時に正しいトリガーが送信される(): void
    {
        // Arrange
        $usrUserId = 'user_001';
        $mstStageId = 'stage_001';
        $lapCount = 1;

        // Act
        $this->stageMissionTriggerService->sendStageClearTriggers(
            $usrUserId,
            $mstStage,
            $usrStage,
            $inGameBattleLogData,
            $partyNo,
            $isQuestFirstClear = true,
            $lapCount,
        );

        // Assert - 全体カウントトリガー
        $this->checkTriggerAndAggregatedProgress(
            MissionCriterionType::STAGE_CLEAR_COUNT,
            criterionValue: null,
            expectedProgress: 1,
            isExist: true,
        );

        // Assert - 特定ステージトリガー
        $this->checkTriggerAndAggregatedProgress(
            MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT,
            criterionValue: $mstStageId,
            expectedProgress: 1,
            isExist: true,
        );
    }

    /** @test */
    public function 周回数に応じたトリガーが送信される(): void
    {
        $lapCount = 3;

        $this->stageMissionTriggerService->sendStageClearTriggers(
            /*...*/ $lapCount
        );

        // 3周回分のトリガー
        $this->checkTriggerAndAggregatedProgress(
            MissionCriterionType::STAGE_CLEAR_COUNT,
            criterionValue: null,
            expectedProgress: 3,
        );
    }
}
```

### 複数トリガーパターン

```php
/** @test */
public function 複数種類のトリガーが送信される(): void
{
    $this->stageMissionTriggerService->sendStageClearTriggers(/*...*/);

    // トリガー1: 全体カウント
    $this->checkTriggerAndAggregatedProgress(
        MissionCriterionType::STAGE_CLEAR_COUNT,
        null,
        1
    );

    // トリガー2: 特定ステージ
    $this->checkTriggerAndAggregatedProgress(
        MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT,
        'stage_001',
        1
    );

    // トリガー3: クエストクリア
    $this->checkTriggerAndAggregatedProgress(
        MissionCriterionType::QUEST_CLEAR_COUNT,
        null,
        1
    );
}
```

## ミッション進捗更新テスト

### テストファイル配置

**配置場所**: `api/tests/Feature/Domain/Mission/Services/MissionUpdateServiceTest.php`

### 基本パターン

```php
class MissionUpdateServiceTest extends TestCase
{
    use TestMissionTrait;

    private MissionUpdateService $missionUpdateService;
    private MissionManager $missionManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->missionUpdateService = $this->app->make(MissionUpdateService::class);
        $this->missionManager = $this->app->make(MissionManager::class);
    }

    /** @test */
    public function ミッション達成判定が正しく動作する(): void
    {
        // Arrange
        $usrUserId = 'user_001';
        $now = CarbonImmutable::now();

        // マスタデータ作成
        $mstMissionId = 'mission_001';
        $this->createMstMission(
            MissionType::ACHIEVEMENT,
            $mstMissionId,
            MissionCriterionType::STAGE_CLEAR_COUNT,
            criterionValue: null,
            criterionCount: 10,
        );

        // ユーザーデータ作成（進捗9）
        $this->createUsrMissionNormal(
            $usrUserId,
            MissionType::ACHIEVEMENT,
            $mstMissionId,
            MissionStatus::UNCLEAR,
            progress: 9,
            clearedAt: null,
            receivedRewardAt: null,
            latestResetAt: $now->toDateTimeString(),
        );

        // トリガー送信（+1）
        $this->missionManager->addTrigger(
            new MissionTrigger(
                MissionCriterionType::STAGE_CLEAR_COUNT->value,
                null,
                1
            )
        );

        // Act
        $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
        $this->saveAll();

        // Assert
        $usrMissions = $this->getUsrMissions($usrUserId, MissionType::ACHIEVEMENT);
        $this->checkUsrMissionStatus(
            $usrMissions,
            $mstMissionId,
            isExist: true,
            isClear: true,
            clearedAt: $now->toDateTimeString(),
            isReceiveReward: false,
            receivedRewardAt: null
        );

        $usrMission = $usrMissions->get($mstMissionId);
        $this->assertEquals(10, $usrMission->getProgress());
    }
}
```

### 新規レコード作成パターン

```php
/** @test */
public function ミッションレコードが存在しない場合は新規作成される(): void
{
    // マスタデータのみ作成（ユーザーデータなし）
    $mstMissionId = 'mission_001';
    $this->createMstMission(
        MissionType::ACHIEVEMENT,
        $mstMissionId,
        MissionCriterionType::STAGE_CLEAR_COUNT,
        null,
        10,
    );

    // トリガー送信
    $this->missionManager->addTrigger(
        new MissionTrigger(
            MissionCriterionType::STAGE_CLEAR_COUNT->value,
            null,
            5
        )
    );

    // Act
    $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
    $this->saveAll();

    // Assert - レコードが作成される
    $usrMissions = $this->getUsrMissions($usrUserId, MissionType::ACHIEVEMENT);
    $usrMission = $usrMissions->get($mstMissionId);

    $this->assertNotNull($usrMission);
    $this->assertEquals(5, $usrMission->getProgress());
    $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMission->getStatus());
}
```

### リセット機能テスト

```php
/** @test */
public function デイリーミッションがリセットされる(): void
{
    $yesterday = CarbonImmutable::parse('2024-01-01 00:00:00');
    $today = CarbonImmutable::parse('2024-01-02 00:00:00');

    // 昨日達成したミッション
    $this->createUsrMissionNormal(
        $usrUserId,
        MissionType::DAILY,
        $mstMissionId,
        MissionStatus::CLEAR,
        progress: 10,
        clearedAt: $yesterday->toDateTimeString(),
        receivedRewardAt: null,
        latestResetAt: $yesterday->toDateTimeString(),
    );

    // 今日のトリガー
    $this->missionManager->addTrigger(
        new MissionTrigger(
            MissionCriterionType::STAGE_CLEAR_COUNT->value,
            null,
            3
        )
    );

    // Act - 今日の日時で更新
    $this->missionUpdateService->updateTriggeredMissions($usrUserId, $today);
    $this->saveAll();

    // Assert - リセットされて新しい進捗
    $usrMissions = $this->getUsrMissions($usrUserId, MissionType::DAILY);
    $usrMission = $usrMissions->get($mstMissionId);

    $this->assertEquals(3, $usrMission->getProgress(), '進捗がリセットされて新しい値');
    $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMission->getStatus());
    $this->assertNull($usrMission->getClearedAt());
    $this->assertEquals($today->toDateTimeString(), $usrMission->getLatestResetAt());
}
```

## UseCase E2Eテスト

### テストファイル配置

**配置場所**: `api/tests/Feature/Http/Controllers/{Controller}Test.php`

### 基本パターン

```php
class StageControllerTest extends BaseControllerTestCase
{
    use TestMissionTrait;

    /** @test */
    public function ステージクリアでミッションが達成される(): void
    {
        // Arrange
        [$usrUserId, $platform, $now] = $this->setupUser();

        // ミッションマスタ
        $mstMissionId = 'mission_001';
        $this->createMstMission(
            MissionType::ACHIEVEMENT,
            $mstMissionId,
            MissionCriterionType::STAGE_CLEAR_COUNT,
            null,
            criterionCount: 1,  // 1回クリアで達成
        );

        // ステージ開始
        $startResponse = $this->sendRequest('stage/start_quest', [
            'mst_stage_id' => 'stage_001',
            'party_no' => 1,
        ]);

        // Act - ステージクリア
        $endResponse = $this->sendRequest('stage/end_quest', [
            'stage_key' => $startResponse['stage_key'],
            'is_win' => true,
        ]);

        // Assert - ミッション達成
        $this->assertResponseSuccess($endResponse);

        $usrMissions = $this->getUsrMissions($usrUserId, MissionType::ACHIEVEMENT);
        $this->checkUsrMissionStatus(
            $usrMissions,
            $mstMissionId,
            isExist: true,
            isClear: true,
            clearedAt: $now->toDateTimeString(),
            isReceiveReward: false,
            receivedRewardAt: null
        );
    }
}
```

### シナリオテスト（複数API連続実行）

```php
class TutorialScenarioTest extends BaseControllerTestCase
{
    use TestMissionTrait;
    use TestMultipleApiRequestsTrait;  // 複数リクエスト用

    /** @test */
    public function チュートリアル完了でミッション達成(): void
    {
        [$usrUserId, $platform, $now] = $this->setupUser();

        // 初心者ミッション作成
        $this->prepareUpdateBeginnerMission($usrUserId);
        $mstMissionId = 'beginner_001';
        $this->createMstMission(
            MissionType::BEGINNER,
            $mstMissionId,
            MissionCriterionType::TUTORIAL_COMPLETED,
            null,
            1,
            beginnerUnlockDay: 0,
        );

        // チュートリアルステップ1
        $response1 = $this->sendRequest('tutorial/update_status', ['step' => 1]);
        $this->resetAppForNextRequest($usrUserId);  // 必須

        // チュートリアルステップ2
        $response2 = $this->sendRequest('tutorial/update_status', ['step' => 2]);
        $this->resetAppForNextRequest($usrUserId);

        // チュートリアル完了
        $response3 = $this->sendRequest('tutorial/update_status', ['step' => 99]);

        // Assert
        $usrMissions = $this->getUsrMissions($usrUserId, MissionType::BEGINNER);
        $this->checkUsrMissionStatus(
            $usrMissions,
            $mstMissionId,
            isExist: true,
            isClear: true,
            clearedAt: $now->toDateTimeString(),
            isReceiveReward: false,
            receivedRewardAt: null
        );
    }
}
```

## テストパターン集

### パターン1: 進捗が累積されることを確認

```php
/** @test */
public function 複数回のトリガーで進捗が累積される(): void
{
    // 初期進捗5
    $this->createUsrMissionNormal(
        $usrUserId,
        MissionType::ACHIEVEMENT,
        $mstMissionId,
        MissionStatus::UNCLEAR,
        progress: 5,
        /*...*/
    );

    // トリガー1回目（+3）
    $this->missionManager->addTrigger(
        new MissionTrigger(
            MissionCriterionType::STAGE_CLEAR_COUNT->value,
            null,
            3
        )
    );
    $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
    $this->saveAll();

    // トリガー2回目（+2）
    $this->missionManager->addTrigger(
        new MissionTrigger(
            MissionCriterionType::STAGE_CLEAR_COUNT->value,
            null,
            2
        )
    );
    $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
    $this->saveAll();

    // 進捗10（5+3+2）
    $usrMissions = $this->getUsrMissions($usrUserId, MissionType::ACHIEVEMENT);
    $usrMission = $usrMissions->get($mstMissionId);
    $this->assertEquals(10, $usrMission->getProgress());
}
```

### パターン2: 達成済みミッションは更新されない

```php
/** @test */
public function 達成済みミッションは進捗が更新されない(): void
{
    // 既に達成済み
    $this->createUsrMissionNormal(
        $usrUserId,
        MissionType::ACHIEVEMENT,
        $mstMissionId,
        MissionStatus::CLEAR,
        progress: 10,
        clearedAt: $now->toDateTimeString(),
        /*...*/
    );

    // トリガー送信
    $this->missionManager->addTrigger(
        new MissionTrigger(
            MissionCriterionType::STAGE_CLEAR_COUNT->value,
            null,
            5
        )
    );

    $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
    $this->saveAll();

    // 進捗は変わらない
    $usrMissions = $this->getUsrMissions($usrUserId, MissionType::ACHIEVEMENT);
    $usrMission = $usrMissions->get($mstMissionId);
    $this->assertEquals(10, $usrMission->getProgress());
    $this->assertEquals(MissionStatus::CLEAR->value, $usrMission->getStatus());
}
```

### パターン3: MockでhandleAllUpdateTriggeredMissionsをスキップ

```php
/** @test */
public function トリガー送信だけをテストする(): void
{
    // ミッション進捗更新をモックしてスキップ
    $this->mockExecHandleAllUpdateTriggeredMissions();

    // UseCaseを実行
    $useCase->execute(/*...*/);

    // トリガーが正しく送信されたか確認
    $this->checkTriggerAndAggregatedProgress(
        MissionCriterionType::STAGE_CLEAR_COUNT,
        null,
        expectedProgress: 1,
        isExist: true,
    );
}
```

## トラブルシューティング

### テストが失敗する

**原因1**: トリガーが送信されていない
```php
// トリガー存在確認
$this->checkExistMissionManagerTriggers(isExist: true);
```

**原因2**: saveAll()忘れ
```php
// updateTriggeredMissions()の後に必ず呼ぶ
$this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
$this->saveAll();  // これ必須
```

**原因3**: マスタデータ不足
```php
// マスタデータが作成されているか確認
$mstMission = MstMissionAchievement::find($mstMissionId);
$this->assertNotNull($mstMission);
```

### シナリオテストでエラー

**原因**: resetAppForNextRequest()忘れ
```php
$response1 = $this->sendRequest('api1', $params1);
$this->resetAppForNextRequest($usrUserId);  // これ必須

$response2 = $this->sendRequest('api2', $params2);
$this->resetAppForNextRequest($usrUserId);  // これも必須
```
