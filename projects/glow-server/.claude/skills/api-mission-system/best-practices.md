# ミッション実装ベストプラクティス

ミッション機能の実装におけるベストプラクティス、よくあるパターン、アンチパターンを解説します。

## 目次

1. [設計原則](#設計原則)
2. [トリガー送信のベストプラクティス](#トリガー送信のベストプラクティス)
3. [Criterion実装のベストプラクティス](#criterion実装のベストプラクティス)
4. [テスト実装のベストプラクティス](#テスト実装のベストプラクティス)
5. [よくあるパターン](#よくあるパターン)
6. [アンチパターン](#アンチパターン)
7. [パフォーマンス最適化](#パフォーマンス最適化)

## 設計原則

### 1. 疎結合の維持

**Good**: トリガー駆動で疎結合

```php
// 各ドメインはミッションを意識しない
class StageService
{
    public function clearStage(/*...*/)
    {
        // ステージクリア処理
        $this->updateStageData();

        // ミッショントリガー送信（疎結合）
        $this->stageMissionTriggerService->sendStageClearTriggers(/*...*/);
    }
}
```

**Bad**: 直接的な依存

```php
// ❌ ドメインがミッション詳細を知っている（密結合）
class StageService
{
    public function clearStage(/*...*/)
    {
        // ステージクリア処理
        $this->updateStageData();

        // ❌ ミッションの存在を直接意識
        $missions = $this->getMissionsByStage($stageId);
        foreach ($missions as $mission) {
            $this->updateMissionProgress($mission);
        }
    }
}
```

### 2. 単一責任の原則

**Good**: 役割を分離

```php
// トリガー送信はMissionTriggerServiceの責任
class StageMissionTriggerService
{
    public function sendStageClearTriggers(/*...*/) { }
}

// 進捗判定はMissionUpdateServiceの責任
class MissionUpdateService
{
    public function updateTriggeredMissions(/*...*/) { }
}
```

**Bad**: 責任の混在

```php
// ❌ Serviceが進捗判定まで行う
class StageService
{
    public function clearStage(/*...*/)
    {
        // ステージクリア処理
        $this->updateStageData();

        // ❌ ミッション進捗判定まで行ってしまう
        $this->checkAndUpdateMissions();
    }
}
```

### 3. Criterionの独立性

**Good**: Criterionが独立している

```php
// 各Criterionは独立して動作
class StageClearCountCriterion extends MissionCriterion
{
    // 自身の判定ロジックのみを持つ
    public function isClear(): bool
    {
        return $this->progress >= $this->criterionCount;
    }
}
```

**Bad**: Criterionが他に依存

```php
// ❌ 他のCriterionや外部状態に依存
class BadCriterion extends MissionCriterion
{
    public function isClear(): bool
    {
        // ❌ 外部データに依存
        $userData = $this->getUserData();
        return $this->progress >= $this->criterionCount && $userData->level >= 10;
    }
}
```

## トリガー送信のベストプラクティス

### パターン1: まとめて送信

**Good**: Collectionにまとめて一度に送信

```php
public function sendMultipleTriggers(/*...*/)
{
    $triggers = collect();

    // トリガー1
    $triggers->push(new MissionTrigger(/*...*/));

    // トリガー2
    $triggers->push(new MissionTrigger(/*...*/));

    // トリガー3
    if ($condition) {
        $triggers->push(new MissionTrigger(/*...*/));
    }

    // まとめて送信
    $this->missionDelegator->addTriggers($triggers);
}
```

**Bad**: 個別に送信

```php
// ❌ 複数回呼び出すのは非効率
public function sendMultipleTriggers(/*...*/)
{
    $this->missionDelegator->addTrigger(new MissionTrigger(/*...*/));
    $this->missionDelegator->addTrigger(new MissionTrigger(/*...*/));
    $this->missionDelegator->addTrigger(new MissionTrigger(/*...*/));
}
```

### パターン2: 全体 + 特定の両方を送信

**Good**: 汎用性の高い設計

```php
public function sendActionTriggers(string $targetId, int $count)
{
    $triggers = collect();

    // 全体カウント（どれでもOKミッション用）
    $triggers->push(
        new MissionTrigger(
            MissionCriterionType::ACTION_COUNT->value,
            null,
            $count,
        )
    );

    // 特定カウント（特定対象のみミッション用）
    $triggers->push(
        new MissionTrigger(
            MissionCriterionType::SPECIFIC_ACTION_COUNT->value,
            $targetId,
            $count,
        )
    );

    $this->missionDelegator->addTriggers($triggers);
}
```

これにより以下の両方のミッションに対応できる：
- 「どのアクションでも10回」
- 「特定のアクションを3回」

### パターン3: 条件付きトリガー

**Good**: 条件に応じたトリガー送信

```php
public function sendStageClearTriggers(
    bool $isQuestFirstClear,
    int $lapCount,
)
{
    $triggers = collect();

    // 常に送信するトリガー
    $triggers->push(new MissionTrigger(
        MissionCriterionType::STAGE_CLEAR_COUNT->value,
        null,
        $lapCount,
    ));

    // 初回クリア時のみ送信
    if ($isQuestFirstClear) {
        $triggers->push(new MissionTrigger(
            MissionCriterionType::QUEST_CLEAR_COUNT->value,
            null,
            1,
        ));
    }

    $this->missionDelegator->addTriggers($triggers);
}
```

### パターン4: ループ内でのトリガー生成

**Good**: ループ外でまとめて送信

```php
public function sendPartyUnitTriggers(Collection $units, string $stageId)
{
    $triggers = collect();

    // ループ内でトリガー生成
    foreach ($units as $unitEntity) {
        $mstUnit = $unitEntity->getMstUnit();

        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_UNIT_STAGE_CLEAR_COUNT->value,
                MissionUtil::makeSpecificUnitStageClearCountCriterionValue(
                    $mstUnit->getId(),
                    $stageId,
                ),
                1,
            )
        );
    }

    // ループ外でまとめて送信
    $this->missionDelegator->addTriggers($triggers);
}
```

**Bad**: ループ内で送信

```php
// ❌ ループ内で複数回送信するのは非効率
foreach ($units as $unitEntity) {
    $this->missionDelegator->addTrigger(new MissionTrigger(/*...*/));
}
```

## Criterion実装のベストプラクティス

### パターン1: カウント系Criterion

**基本形**:

```php
class CountCriterion extends MissionCriterion
{
    public function getCriterionKey(): string
    {
        // criterionValueの有無で決定
        if ($this->criterionValue === null) {
            return $this->getCriterionType();
        }

        return MissionUtil::makeCriterionKey(
            $this->getCriterionType(),
            $this->criterionValue
        );
    }

    // aggregateProgress()はデフォルト実装（加算）を使用
}
```

### パターン2: レベル系Criterion

**最大値で判定**:

```php
class LevelCriterion extends MissionCriterion
{
    public function aggregateProgress(int $progress): void
    {
        // 最大値を保持（現在値より大きい場合のみ更新）
        if ($progress > $this->progress) {
            $this->progress = $progress;
        }
    }
}
```

### パターン3: フラグ系Criterion

**達成したら1、未達成なら0**:

```php
class FlagCriterion extends MissionCriterion
{
    public function aggregateProgress(int $progress): void
    {
        // 1が来たら達成（0の場合は無視）
        if ($progress > 0) {
            $this->progress = 1;
        }
    }

    public function isClear(): bool
    {
        // criterionCountは1固定を想定
        return $this->progress >= 1;
    }
}
```

### パターン4: 複合キーCriterion

**複数の値を組み合わせる**:

```php
class CompositeKeyCriterion extends MissionCriterion
{
    public function getCriterionKey(): string
    {
        // criterionValueには "key1-key2" のような形式を想定
        return MissionUtil::makeCriterionKey(
            $this->getCriterionType(),
            $this->criterionValue
        );
    }
}

// 使用例（ユーティリティメソッド）
class MissionUtil
{
    public static function makeCompositeKey(string $key1, string $key2): string
    {
        return sprintf('%s-%s', $key1, $key2);
    }
}
```

## テスト実装のベストプラクティス

### パターン1: Given-When-Then構造

**Good**: 明確な構造

```php
/** @test */
public function ミッション達成判定が正しく動作する(): void
{
    // Given（前提条件）
    $usrUserId = 'user_001';
    $now = CarbonImmutable::now();

    $this->createMstMission(/*...*/);
    $this->createUsrMissionNormal(/*...*/);
    $this->missionManager->addTrigger(/*...*/);

    // When（実行）
    $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
    $this->saveAll();

    // Then（検証）
    $usrMissions = $this->getUsrMissions($usrUserId, MissionType::ACHIEVEMENT);
    $this->checkUsrMissionStatus(/*...*/);
}
```

### パターン2: DataProvider活用

**Good**: 複数パターンをまとめてテスト

```php
/**
 * @test
 * @dataProvider progressDataProvider
 */
public function 進捗値による達成判定(
    int $initialProgress,
    int $addProgress,
    bool $expectedClear,
): void {
    // テストロジック
}

public static function progressDataProvider(): array
{
    return [
        '未達成' => [5, 3, false],
        '達成' => [5, 5, true],
        '超過達成' => [5, 10, true],
    ];
}
```

### パターン3: テストメソッドの命名

**Good**: 日本語で意図を明確に

```php
/** @test */
public function ステージクリア時にミッションが達成される(): void
{
}

/** @test */
public function デイリーミッションが毎日リセットされる(): void
{
}

/** @test */
public function 達成済みミッションは進捗が更新されない(): void
{
}
```

## よくあるパターン

### パターン1: 初回のみトリガー

```php
public function sendFirstTimeTriggers(bool $isFirst)
{
    if (!$isFirst) {
        return;
    }

    $triggers = collect();
    $triggers->push(new MissionTrigger(/*...*/));
    $this->missionDelegator->addTriggers($triggers);
}
```

### パターン2: しきい値判定

```php
public function sendThresholdTriggers(int $value, int $threshold)
{
    if ($value < $threshold) {
        return;
    }

    $triggers = collect();
    $triggers->push(new MissionTrigger(/*...*/));
    $this->missionDelegator->addTriggers($triggers);
}
```

### パターン3: 複数条件のAND

```php
public function sendConditionalTriggers(bool $condition1, bool $condition2)
{
    if (!$condition1 || !$condition2) {
        return;
    }

    $triggers = collect();
    $triggers->push(new MissionTrigger(/*...*/));
    $this->missionDelegator->addTriggers($triggers);
}
```

## アンチパターン

### アンチパターン1: UseCase外でのトリガー送信

**Bad**: UseCase外で送信

```php
// ❌ UseCase外で送信しても進捗更新されない
class SomeRepository
{
    public function save($data)
    {
        // データ保存
        $this->model->save($data);

        // ❌ Repository層でトリガー送信（進捗更新されない）
        $this->missionDelegator->addTrigger(/*...*/);
    }
}
```

**Good**: UseCaseで送信

```php
class SomeUseCase
{
    public function execute()
    {
        // ビジネスロジック
        $this->someService->doSomething();

        // ✓ UseCase層でトリガー送信（進捗更新される）
        $this->someMissionTriggerService->sendTriggers(/*...*/);
    }
}
```

### アンチパターン2: トリガーの重複送信

**Bad**: 同じトリガーを複数回送信

```php
// ❌ 同じトリガーを複数回送信すると進捗が2重カウントされる
public function doAction()
{
    $this->triggerService->sendTriggers(/*...*/);

    // ... 処理 ...

    $this->triggerService->sendTriggers(/*...*/);  // ❌ 重複
}
```

**Good**: トリガーは一度だけ送信

```php
public function doAction()
{
    // 処理
    $result = $this->executeAction();

    // トリガーは最後に一度だけ
    if ($result->isSuccess()) {
        $this->triggerService->sendTriggers(/*...*/);
    }
}
```

### アンチパターン3: Criterion内での外部データ取得

**Bad**: Criterion内で外部データ取得

```php
// ❌ Criterionが外部に依存
class BadCriterion extends MissionCriterion
{
    public function isClear(): bool
    {
        // ❌ DB問い合わせなど外部データ取得
        $userData = DB::table('usr_users')->find($this->userId);
        return $this->progress >= $this->criterionCount && $userData->level >= 10;
    }
}
```

**Good**: トリガー送信時に判定

```php
// ✓ 外部判定はトリガー送信前に行う
class GoodTriggerService
{
    public function sendTriggers(string $usrUserId, int $progress)
    {
        $userData = $this->getUserData($usrUserId);

        // 条件を満たす場合のみトリガー送信
        if ($userData->level >= 10) {
            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SOME_CRITERION->value,
                    null,
                    $progress,
                )
            );
        }
    }
}
```

### アンチパターン4: MissionManager直接操作

**Bad**: MissionManagerを直接操作

```php
// ❌ MissionManagerを直接使うのは避ける
class SomeService
{
    public function __construct(
        private MissionManager $missionManager,
    ) {}

    public function doAction()
    {
        $this->missionManager->addTrigger(/*...*/);  // ❌
    }
}
```

**Good**: MissionDelegatorを使用

```php
// ✓ MissionDelegatorを使用（推奨）
class SomeMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {}

    public function sendTriggers()
    {
        $this->missionDelegator->addTriggers(/*...*/);  // ✓
    }
}
```

### アンチパターン5: saveAll()忘れ

**Bad**: saveAll()を呼ばない

```php
// ❌ saveAll()を呼ばないとDBに反映されない
public function test_ミッション達成()
{
    $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
    // ❌ saveAll()忘れ

    $usrMissions = $this->getUsrMissions($usrUserId, MissionType::ACHIEVEMENT);
    // データが取得できない
}
```

**Good**: saveAll()を呼ぶ

```php
// ✓ saveAll()を呼ぶ
public function test_ミッション達成()
{
    $this->missionUpdateService->updateTriggeredMissions($usrUserId, $now);
    $this->saveAll();  // ✓

    $usrMissions = $this->getUsrMissions($usrUserId, MissionType::ACHIEVEMENT);
    // データが取得できる
}
```

## パフォーマンス最適化

### 最適化1: トリガーのバッチ処理

**Good**: トリガーをまとめて処理

```php
// MissionManagerはトリガーを蓄積し、まとめて処理
$this->missionManager->addTriggers($triggers);  // 蓄積
// ...
$this->missionUpdateService->updateTriggeredMissions(/*...*/);  // まとめて処理
```

### 最適化2: N+1問題の回避

**Good**: 必要なデータを一括取得

```php
// MissionUpdateService::updateTriggeredMissions()
// マスタデータを一括取得
$mstMissions = $this->mstMissionRepository->getAll();

// ユーザーデータを一括取得
$usrMissions = $this->usrMissionRepository->getByMstMissionIds(
    $usrUserId,
    $mstMissionIds,
);
```

### 最適化3: 不要な更新をスキップ

**Good**: 変更がない場合はスキップ

```php
// MissionState::isUpdateNotNeeded()
public function isUpdateNotNeeded(): bool
{
    // 進捗変更なし、かつ既にクリア済み
    if ($this->criterion->getProgress() === $this->usrMission?->getProgress()
        && $this->usrMission?->isClear()
    ) {
        return true;
    }

    return false;
}
```

## チェックリスト

実装完了時に確認してください：

### トリガー送信
- [ ] トリガーはCollectionにまとめて送信しているか
- [ ] 全体 + 特定の両方を送信しているか（必要な場合）
- [ ] UseCase内で送信しているか
- [ ] 重複送信していないか

### Criterion実装
- [ ] `getCriterionType()`を実装
- [ ] `getCriterionKey()`を実装
- [ ] criterionValueの有無が適切か
- [ ] `aggregateProgress()`のオーバーライドが必要なら実装

### テスト実装
- [ ] Given-When-Then構造で書いているか
- [ ] `saveAll()`を呼んでいるか
- [ ] TestMissionTraitを使用しているか
- [ ] 複数パターンをDataProviderでテストしているか

### パフォーマンス
- [ ] N+1問題が発生していないか
- [ ] 不要な更新をスキップしているか
- [ ] トリガーをまとめて処理しているか

### アンチパターン回避
- [ ] UseCase外でトリガー送信していないか
- [ ] Criterion内で外部データ取得していないか
- [ ] MissionManagerを直接操作していないか
- [ ] トリガーを重複送信していないか
