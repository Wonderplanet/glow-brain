# ミッションシステムアーキテクチャ

ミッションシステムの全体像、コンポーネント、データフロー、設計思想を解説します。

## 目次

1. [システム全体像](#システム全体像)
2. [主要コンポーネント](#主要コンポーネント)
3. [データフロー](#データフロー)
4. [ミッション処理の流れ](#ミッション処理の流れ)
5. [設計思想](#設計思想)

## システム全体像

ミッションシステムは、ユーザーの行動（トリガー）を検知し、条件を判定して達成・報酬付与を管理します。

```
ユーザー行動
    ↓
[1] トリガー送信（各ドメインのMissionTriggerService）
    ↓
[2] MissionManager（トリガー蓄積）
    ↓
[3] MissionUpdateService（進捗判定・更新）
    ↓
[4] データベース更新（usr_mission_*）
    ↓
[5] レスポンス返却
```

## 主要コンポーネント

### 1. MissionTrigger（トリガーエンティティ）

ユーザー行動を表すデータクラス。

**ファイル**: `api/app/Domain/Common/Entities/MissionTrigger.php`

```php
class MissionTrigger
{
    public function __construct(
        string $criterionType,    // 達成条件タイプ（例: 'StageClearCount'）
        ?string $criterionValue,  // 条件値（例: ステージID）
        int $progress,            // 進捗値（例: 1回クリア）
    ) {}
}
```

**使用例**:
```php
// ステージクリア時のトリガー
new MissionTrigger(
    MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
    'stage_001',
    1
)
```

### 2. MissionManager（トリガー管理）

トリガーを一時的に保持し、まとめて処理するためのマネージャー。

**ファイル**: `api/app/Domain/Mission/MissionManager.php`

**主要メソッド**:
```php
class MissionManager
{
    // トリガーを追加
    public function addTrigger(MissionTrigger $trigger, ?MissionType $missionType = null): void

    // トリガーを一括追加
    public function addTriggers(Collection $triggers, ?MissionType $missionType = null): void

    // トリガーを取り出す（取り出すと削除される）
    public function popTriggers(MissionType $missionType): Collection
}
```

**特徴**:
- シングルトンとしてDIコンテナで管理
- リクエスト内でトリガーを蓄積
- 重複判定を避けるため、popすると削除される

### 3. XXXMissionTriggerService（トリガー送信サービス）

各ドメインから呼び出され、MissionManagerにトリガーを送信します。

**実装例**:
- `StageMissionTriggerService` - ステージ関連
- `UnitMissionTriggerService` - ユニット関連
- `UserMissionTriggerService` - ユーザー関連
- `GachaMissionTriggerService` - ガチャ関連
- など

**実装パターン**:
```php
class StageMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {}

    public function sendStageClearTriggers(/*...*/)
    {
        $triggers = collect();

        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::STAGE_CLEAR_COUNT->value,
                null,
                $lapCount,
            )
        );

        $this->missionDelegator->addTriggers($triggers);
    }
}
```

### 4. MissionUpdateService（進捗判定・更新）

トリガーを受け取り、ミッション進捗を判定・更新します。

**ファイル**: `api/app/Domain/Mission/Services/MissionUpdateService.php`

**主要メソッド**:
```php
class MissionUpdateService
{
    // トリガーに基づいてミッション進捗を更新
    public function updateTriggeredMissions(
        string $usrUserId,
        CarbonImmutable $now,
    ): void

    // ミッション状態を作成
    public function createStates(MissionUpdateBundle $bundle): Collection

    // ユーザーデータを更新
    public function updateUsrMission(
        string $usrUserId,
        CarbonImmutable $now,
        int $missionType,
        Collection $states,
    ): void
}
```

### 5. MissionCriterion（達成条件）

各達成条件タイプごとの判定ロジックを持つクラス。

**基底クラス**: `api/app/Domain/Mission/Entities/Criteria/MissionCriterion.php`

**実装例**:
- `StageClearCountCriterion` - ステージクリア回数
- `LoginCountCriterion` - ログイン回数
- `UnitLevelCriterion` - ユニットレベル
- など（60種類以上）

**共通メソッド**:
```php
abstract class MissionCriterion
{
    // 進捗値を集約
    public function aggregateProgress(int $progress): void

    // 達成判定
    public function isClear(): bool

    // 進捗値取得
    public function getProgress(): int
}
```

### 6. MissionState（ミッション状態）

1つのミッションの状態を保持するエンティティ。

**ファイル**: `api/app/Domain/Mission/Entities/MissionState.php`

```php
class MissionState
{
    public function __construct(
        private MstMissionEntityInterface $mstMission,  // マスタデータ
        private ?IUsrMission $usrMission,               // ユーザーデータ
        private MissionCriterion $criterion,            // 達成条件
        private ?MissionCriterion $unlockCriterion,     // 開放条件
    ) {}

    // 達成判定を実行
    public function checkAndClear(): void

    // 開放判定を実行
    public function checkAndOpen(): void
}
```

## データフロー

### 1. トリガー送信フェーズ

```
[ユーザー行動（例: ステージクリア）]
    ↓
StageEndQuestService::end()
    ↓
StageMissionTriggerService::sendStageClearTriggers()
    ↓
MissionDelegator::addTriggers()
    ↓
MissionManager::addTriggers()
    ↓
[トリガー蓄積（メモリ上）]
```

### 2. 進捗更新フェーズ

```
UseCase終了時
    ↓
UseCaseTrait::afterUseCase()
    ↓
MissionUpdateHandleService::handleAllUpdateTriggeredMissions()
    ↓
MissionUpdateService::updateTriggeredMissions()
    ↓
各ミッションタイプごとに処理:
  - マスタデータ取得
  - ユーザーデータ取得
  - MissionStateの作成
  - 達成判定（checkAndClear）
  - 開放判定（checkAndOpen）
  - 依存関係判定（MissionChain）
  - データベース更新
```

### 3. データベース更新

```
MissionUpdateService::updateUsrMission()
    ↓
UsrMissionRepository::syncModels()
    ↓
データベース更新:
  - usr_mission_normals（Achievement/Daily/Weekly/Beginner）
  - usr_mission_events（Event/EventDaily）
  - usr_mission_limited_terms（LimitedTerm）
```

## ミッション処理の流れ

### ステップ1: トリガー生成と送信

```php
// 例: ステージクリア時
class StageEndQuestService
{
    public function end(/*...*/)
    {
        // ... ステージクリア処理 ...

        // ミッショントリガーを送信
        $this->stageMissionTriggerService->sendStageClearTriggers(
            $usrUserId,
            $mstStage,
            $usrStage,
            $inGameBattleLogData,
            $partyNo,
            $isQuestFirstClear,
            $lapCount,
        );
    }
}
```

### ステップ2: トリガー蓄積

```php
class MissionManager
{
    public function addTrigger(MissionTrigger $trigger, ?MissionType $missionType = null): void
    {
        // 全ミッションタイプに対してトリガーを追加
        foreach ($targetMissionTypes as $missionType) {
            $this->triggers->put(
                $missionType->value,
                $this->triggers->get($missionType->value, collect())->push($trigger)
            );
        }
    }
}
```

### ステップ3: 進捗判定と更新

```php
class MissionUpdateService
{
    public function updateTriggeredMissions(string $usrUserId, CarbonImmutable $now): void
    {
        // 1. マスタデータ取得（MissionUpdateEntityFactory）
        $achievementBundle = $this->missionUpdateEntityFactory->createAchievementMissionUpdateBundle();
        $dailyBundle = $this->missionUpdateEntityFactory->createDailyMissionUpdateBundle();
        // ...

        // 2. ユーザーデータ取得
        $usrMissionNormalBundle = $this->usrMissionNormalRepository->getByMstMissionIds(/*...*/);

        // 3. 各ミッションタイプごとに進捗更新
        if ($achievementBundle !== null) {
            $achievementBundle->setUsrMissions($usrMissionNormalBundle->getAchievements());
            $this->updateMissions($usrUserId, $now, $achievementBundle);
        }
        // ...
    }

    private function updateMissions(/*...*/)
    {
        // 4. MissionState作成
        $states = $this->createStates($bundle);

        // 5. 達成判定
        $this->checkStatesClear($states);

        // 6. 複合ミッション進捗更新
        $this->updateCompositeCriterionProgresses($states);

        // 7. 開放判定
        $this->checkStatesOpen($states);

        // 8. 依存関係判定（MissionChain）
        if ($bundle->hasDependency()) {
            // 段階的な開放判定
        }

        // 9. データベース更新
        $this->updateUsrMission($usrUserId, $now, $bundle->getMissionType()->getIntValue(), $states);
    }
}
```

### ステップ4: 達成判定ロジック

```php
class MissionState
{
    public function checkAndClear(): void
    {
        // 既にクリア済みなら何もしない
        if ($this->usrMission?->isClear()) {
            return;
        }

        // 達成条件チェック
        if ($this->criterion->isClear()) {
            $this->clear = true;
        }
    }
}

// 具体的なCriterion例
class StageClearCountCriterion extends MissionCriterion
{
    public function isClear(): bool
    {
        return $this->progress >= $this->criterionCount;
    }
}
```

## 設計思想

### 1. トリガー駆動アーキテクチャ

**なぜトリガーを使うのか**:
- 各ドメインはミッションの存在を意識しない（疎結合）
- ミッション種類追加時に既存コードを変更不要
- トリガーを蓄積してまとめて処理することで効率化

### 2. 達成条件の抽象化（Criterion）

**なぜCriterionクラスを使うのか**:
- 各達成条件の判定ロジックをカプセル化
- 新しい達成条件の追加が容易
- テストが書きやすい

### 3. 複合ミッション対応

**複合ミッション**とは:
- 「他のミッションをN個達成する」というミッション
- 例: 「デイリーミッションを5個クリア」

**実装のポイント**:
```php
// 複合ミッション進捗更新
private function updateCompositeCriterionProgresses(Collection $states): void
{
    // 新規達成数を計算
    $compositeClearCount = $this->calcCompositeMissionProgressAdditions($states);

    // 複合ミッションの進捗を更新
    foreach ($states as $state) {
        if ($state->isCompositeMission()) {
            $state->getCriterion()->aggregateProgress($compositeClearCount->getAllClearCount());
            $state->checkAndClear();
        }
    }
}
```

### 4. 依存関係とチェーン開放

**依存関係**とは:
- ミッションAを達成したらミッションBが開放される
- 例: 初心者ミッションの段階的開放

**MissionChain**:
```php
// 依存関係のあるミッションを順次開放
class MissionChain
{
    public function stepForOpen(): bool
    {
        foreach ($this->states as $state) {
            if ($state->canDependencyUnlock()) {
                $state->dependencyUnlock();
                return false; // まだ処理継続
            }
        }
        return true; // すべて処理完了
    }
}
```

### 5. リセット機能

**デイリー/ウィークリーミッションのリセット**:
```php
public function resetDailyUsrMissions(Collection $usrMissions, CarbonImmutable $now): Collection
{
    foreach ($usrMissions as $usrMission) {
        if ($usrMission->getMissionType() === MissionType::DAILY->getIntValue()
            && $this->clock->isFirstToday($usrMission->getLatestResetAt())
        ) {
            $usrMission->reset($now);
        }
    }
    return $usrMissions;
}
```

## ミッションタイプ一覧

| タイプ | 説明 | リセット | テーブル |
|--------|------|----------|----------|
| Achievement | 永続的な実績 | なし | usr_mission_normals |
| Daily | デイリーミッション | 毎日 | usr_mission_normals |
| Weekly | ウィークリーミッション | 毎週 | usr_mission_normals |
| Beginner | 初心者ミッション | なし（日数で開放） | usr_mission_normals |
| Event | イベントミッション | イベント開始時 | usr_mission_events |
| EventDaily | イベントデイリー | 毎日 | usr_mission_events |
| LimitedTerm | 期間限定ミッション | 期間開始時 | usr_mission_limited_terms |
| DailyBonus | デイリーボーナス | 毎日 | usr_mission_daily_bonuses |
| EventDailyBonus | イベントデイリーボーナス | 毎日 | usr_mission_event_daily_bonuses |

## 関連ファイル

### 主要クラス
- `api/app/Domain/Mission/MissionManager.php`
- `api/app/Domain/Mission/Services/MissionUpdateService.php`
- `api/app/Domain/Mission/Services/MissionUpdateHandleService.php`
- `api/app/Domain/Mission/Entities/MissionState.php`
- `api/app/Domain/Common/Entities/MissionTrigger.php`

### Enum
- `api/app/Domain/Mission/Enums/MissionType.php`
- `api/app/Domain/Mission/Enums/MissionCriterionType.php`
- `api/app/Domain/Mission/Enums/MissionStatus.php`

### トリガーサービス
- `api/app/Domain/Stage/Services/StageMissionTriggerService.php`
- `api/app/Domain/User/Services/UserMissionTriggerService.php`
- `api/app/Domain/Unit/Services/UnitMissionTriggerService.php`
- 他多数（各ドメインに存在）

### Repository
- `api/app/Domain/Mission/Repositories/UsrMissionNormalRepository.php`
- `api/app/Domain/Mission/Repositories/UsrMissionEventRepository.php`
- `api/app/Domain/Mission/Repositories/UsrMissionLimitedTermRepository.php`
