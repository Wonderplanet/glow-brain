# ミッショントリガー実装ガイド

新しいミッション達成条件（Criterion）とトリガーを実装する手順を解説します。

## 目次

1. [実装の全体フロー](#実装の全体フロー)
2. [ステップ1: MissionCriterionType追加](#ステップ1-missioncriteriontypeの追加)
3. [ステップ2: Criterionクラス実装](#ステップ2-criterionクラスの実装)
4. [ステップ3: トリガー送信実装](#ステップ3-トリガー送信の実装)
5. [実装例](#実装例)
6. [チェックリスト](#チェックリスト)

## 実装の全体フロー

新しいミッション達成条件を追加する際の手順：

```
1. MissionCriterionTypeにEnum追加
   ↓
2. Criterionクラスを実装
   ↓
3. MissionCriterionFactoryにマッピング追加
   ↓
4. XXXMissionTriggerServiceでトリガー送信
   ↓
5. テスト実装
```

## ステップ1: MissionCriterionTypeの追加

**ファイル**: `api/app/Domain/Mission/Enums/MissionCriterionType.php`

### 1-1. Enum定義の追加

```php
enum MissionCriterionType: string
{
    // 既存のEnum...

    // 新規追加
    case NEW_CRITERION = 'NewCriterion';
}
```

### 1-2. Criterionクラスマッピングの追加

```php
public function getCriterionClass(): ?string
{
    return match ($this) {
        // 既存のマッピング...

        // 新規追加
        self::NEW_CRITERION => NewCriterion::class,

        default => null,
    };
}
```

### 命名規則

**CriterionType命名ルール**:
- パスカルケース
- 具体的で分かりやすい名前
- 類似の既存Criterionを参考にする

**例**:
- `StageClearCount` - ステージクリア回数
- `SpecificStageClearCount` - 特定ステージクリア回数
- `UnitLevel` - ユニットレベル
- `SpecificUnitLevel` - 特定ユニットのレベル

**パターン**:
- 特定の対象を指定する場合は`Specific`プレフィックス
- 回数系は`Count`サフィックス
- 累計系は明示しない（デフォルト）

## ステップ2: Criterionクラスの実装

**配置場所**: `api/app/Domain/Mission/Entities/Criteria/`

### 2-1. 基本構造

```php
<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

class NewCriterion extends MissionCriterion
{
    /**
     * @param string|null $criterionValue マスタに設定される条件値
     * @param int|null $progress 現在の進捗値
     */
    public function __construct(
        ?string $criterionValue,
        ?int $progress,
    ) {
        parent::__construct($criterionValue, $progress);
    }

    /**
     * 達成条件タイプを返す
     */
    public function getCriterionType(): string
    {
        return MissionCriterionType::NEW_CRITERION->value;
    }

    /**
     * 達成条件のキーを生成
     * 同じキーを持つトリガーが集約される
     */
    public function getCriterionKey(): string
    {
        // パターン1: criterionValueを使わない（全体集計）
        return $this->getCriterionType();

        // パターン2: criterionValueを使う（特定の対象を集計）
        return MissionUtil::makeCriterionKey(
            $this->getCriterionType(),
            $this->criterionValue
        );
    }
}
```

### 2-2. criterionValueの使い方

**criterionValue不要なパターン（全体集計）**:
```php
// 例: ステージクリア回数（どのステージでもOK）
class StageClearCountCriterion extends MissionCriterion
{
    public function getCriterionKey(): string
    {
        // 全ステージの合計を集計
        return $this->getCriterionType();
    }
}

// マスタデータ
criterion_type: 'StageClearCount'
criterion_value: null  // 不要
criterion_count: 10    // 10回クリア

// トリガー送信例
new MissionTrigger(
    MissionCriterionType::STAGE_CLEAR_COUNT->value,
    null,  // criterionValueなし
    1      // 1回クリア
)
```

**criterionValue必要なパターン（特定対象）**:
```php
// 例: 特定ステージのクリア回数
class SpecificStageClearCountCriterion extends MissionCriterion
{
    public function getCriterionKey(): string
    {
        // 特定ステージのみ集計
        return MissionUtil::makeCriterionKey(
            $this->getCriterionType(),
            $this->criterionValue
        );
    }
}

// マスタデータ
criterion_type: 'SpecificStageClearCount'
criterion_value: 'stage_001'  // stage_001のみ
criterion_count: 3             // 3回クリア

// トリガー送信例
new MissionTrigger(
    MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
    'stage_001',  // ステージID
    1             // 1回クリア
)
```

### 2-3. 複雑なcriterionValueの例

**複合キーパターン**:
```php
// 例: 特定ユニットで特定ステージをクリア
class SpecificUnitStageClearCountCriterion extends MissionCriterion
{
    public function getCriterionKey(): string
    {
        return MissionUtil::makeCriterionKey(
            $this->getCriterionType(),
            $this->criterionValue  // "unit001-stage001" のような形式
        );
    }
}

// トリガー送信時
$criterionValue = MissionUtil::makeSpecificUnitStageClearCountCriterionValue(
    $mstUnitId,
    $mstStageId
);

new MissionTrigger(
    MissionCriterionType::SPECIFIC_UNIT_STAGE_CLEAR_COUNT->value,
    $criterionValue,  // "unit001-stage001"
    1
)
```

## ステップ3: トリガー送信の実装

### 3-1. 既存MissionTriggerServiceに追加

**パターン1: 既存サービスにメソッド追加**

```php
// 例: StageMissionTriggerService
class StageMissionTriggerService
{
    public function sendStageClearTriggers(
        string $usrUserId,
        MstStageEntity $mstStage,
        IBaseUsrStage $usrStage,
        int $lapCount,
    ): void {
        $triggers = collect();

        // 既存のトリガー...

        // 新規追加
        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::NEW_CRITERION->value,
                $criterionValue,  // 必要に応じて
                $progress,
            )
        );

        $this->missionDelegator->addTriggers($triggers);
    }
}
```

### 3-2. 新規MissionTriggerService作成

**新しいドメインの場合**:

```php
<?php

declare(strict_types=1);

namespace App\Domain\NewDomain\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;

class NewDomainMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {}

    public function sendNewActionTriggers(
        string $usrUserId,
        string $targetId,
        int $count,
    ): void {
        $triggers = collect();

        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::NEW_CRITERION->value,
                $targetId,
                $count,
            )
        );

        $this->missionDelegator->addTriggers($triggers);
    }
}
```

### 3-3. サービスからの呼び出し

```php
class NewDomainService
{
    public function __construct(
        private NewDomainMissionTriggerService $newDomainMissionTriggerService,
    ) {}

    public function executeAction(string $usrUserId, string $targetId): void
    {
        // ビジネスロジック...

        // ミッショントリガー送信
        $this->newDomainMissionTriggerService->sendNewActionTriggers(
            $usrUserId,
            $targetId,
            1
        );
    }
}
```

## 実装例

### 例1: シンプルなカウント系（criterionValueなし）

**要件**: ガチャを引いた回数をカウント

#### 1. CriterionType追加

```php
// MissionCriterionType.php
case GACHA_DRAW_COUNT = 'GachaDrawCount';

// getCriterionClass()
self::GACHA_DRAW_COUNT => GachaDrawCountCriterion::class,
```

#### 2. Criterionクラス実装

```php
// api/app/Domain/Mission/Entities/Criteria/GachaDrawCountCriterion.php
class GachaDrawCountCriterion extends MissionCriterion
{
    public function __construct(
        ?string $criterionValue,
        ?int $progress,
    ) {
        parent::__construct($criterionValue, $progress);
    }

    public function getCriterionType(): string
    {
        return MissionCriterionType::GACHA_DRAW_COUNT->value;
    }

    public function getCriterionKey(): string
    {
        // 全ガチャの合計回数
        return $this->getCriterionType();
    }
}
```

#### 3. トリガー送信

```php
// GachaMissionTriggerService.php
class GachaMissionTriggerService
{
    public function sendGachaDrawTriggers(
        string $usrUserId,
        int $drawCount,
    ): void {
        $triggers = collect();

        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::GACHA_DRAW_COUNT->value,
                null,        // criterionValueなし
                $drawCount,  // 引いた回数
            )
        );

        $this->missionDelegator->addTriggers($triggers);
    }
}
```

### 例2: 特定対象のカウント系（criterionValueあり）

**要件**: 特定ガチャを引いた回数をカウント

#### 1. CriterionType追加

```php
// MissionCriterionType.php
case SPECIFIC_GACHA_DRAW_COUNT = 'SpecificGachaDrawCount';

// getCriterionClass()
self::SPECIFIC_GACHA_DRAW_COUNT => SpecificGachaDrawCountCriterion::class,
```

#### 2. Criterionクラス実装

```php
// api/app/Domain/Mission/Entities/Criteria/SpecificGachaDrawCountCriterion.php
class SpecificGachaDrawCountCriterion extends MissionCriterion
{
    public function __construct(
        ?string $criterionValue,
        ?int $progress,
    ) {
        parent::__construct($criterionValue, $progress);
    }

    public function getCriterionType(): string
    {
        return MissionCriterionType::SPECIFIC_GACHA_DRAW_COUNT->value;
    }

    public function getCriterionKey(): string
    {
        // 特定ガチャのみ
        return MissionUtil::makeCriterionKey(
            $this->getCriterionType(),
            $this->criterionValue
        );
    }
}
```

#### 3. トリガー送信

```php
// GachaMissionTriggerService.php
class GachaMissionTriggerService
{
    public function sendGachaDrawTriggers(
        string $usrUserId,
        string $mstGachaId,
        int $drawCount,
    ): void {
        $triggers = collect();

        // 全ガチャのカウント
        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::GACHA_DRAW_COUNT->value,
                null,
                $drawCount,
            )
        );

        // 特定ガチャのカウント
        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_GACHA_DRAW_COUNT->value,
                $mstGachaId,  // ガチャID
                $drawCount,
            )
        );

        $this->missionDelegator->addTriggers($triggers);
    }
}
```

### 例3: レベル系（現在値で判定）

**要件**: ユニットレベルが一定値に達したか判定

#### 1. CriterionType追加

```php
// MissionCriterionType.php
case UNIT_LEVEL = 'UnitLevel';

// getCriterionClass()
self::UNIT_LEVEL => UnitLevelCriterion::class,
```

#### 2. Criterionクラス実装

```php
// api/app/Domain/Mission/Entities/Criteria/UnitLevelCriterion.php
class UnitLevelCriterion extends MissionCriterion
{
    public function __construct(
        ?string $criterionValue,
        ?int $progress,
    ) {
        parent::__construct($criterionValue, $progress);
    }

    public function getCriterionType(): string
    {
        return MissionCriterionType::UNIT_LEVEL->value;
    }

    public function getCriterionKey(): string
    {
        return $this->getCriterionType();
    }

    /**
     * レベル系は最大値で判定
     */
    public function aggregateProgress(int $progress): void
    {
        // 現在の進捗より大きい場合のみ更新（最大値を保持）
        if ($progress > $this->progress) {
            $this->progress = $progress;
        }
    }
}
```

#### 3. トリガー送信

```php
// UnitMissionTriggerService.php
class UnitMissionTriggerService
{
    public function sendUnitLevelUpTriggers(
        string $usrUserId,
        int $newLevel,
    ): void {
        $triggers = collect();

        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::UNIT_LEVEL->value,
                null,
                $newLevel,  // 現在のレベル
            )
        );

        $this->missionDelegator->addTriggers($triggers);
    }
}
```

## よくあるパターン

### パターン1: 全体カウント + 特定カウントの両方を送信

```php
public function sendTriggers(string $targetId, int $count): void
{
    $triggers = collect();

    // 全体のカウント
    $triggers->push(
        new MissionTrigger(
            MissionCriterionType::ACTION_COUNT->value,
            null,
            $count,
        )
    );

    // 特定対象のカウント
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

### パターン2: 複数のトリガーをまとめて送信

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

### パターン3: ループ内でトリガー生成

```php
public function sendPartyUnitTriggers(Collection $units, string $stageId): void
{
    $triggers = collect();

    foreach ($units as $unitEntity) {
        $mstUnit = $unitEntity->getMstUnit();

        // ユニットごとにトリガー生成
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

    $this->missionDelegator->addTriggers($triggers);
}
```

## チェックリスト

実装完了時に確認してください：

### Enum定義
- [ ] `MissionCriterionType`にcase追加
- [ ] `getCriterionClass()`にマッピング追加
- [ ] 命名規則に従っているか（既存のパターンと統一）

### Criterionクラス
- [ ] `api/app/Domain/Mission/Entities/Criteria/`に配置
- [ ] `MissionCriterion`を継承
- [ ] `getCriterionType()`を実装
- [ ] `getCriterionKey()`を実装
- [ ] criterionValueの有無が適切か
- [ ] `aggregateProgress()`のオーバーライドが必要ならば実装

### トリガー送信
- [ ] 適切なMissionTriggerServiceに実装
- [ ] MissionTriggerの引数が正しい
  - criterionType
  - criterionValue（必要な場合）
  - progress
- [ ] `$this->missionDelegator->addTriggers()`を呼び出し

### テスト
- [ ] Criterionの単体テスト実装
- [ ] トリガー送信のテスト実装
- [ ] 実際のミッション達成をテスト

## トラブルシューティング

### トリガーが反映されない

**原因1**: MissionCriterionTypeのマッピング忘れ
```php
// getCriterionClass()にマッピングを追加
self::NEW_CRITERION => NewCriterion::class,
```

**原因2**: criterionKeyの不一致
```php
// CriterionクラスのgetCriterionKey()と
// トリガー送信時のcriterionValueが一致しているか確認
```

**原因3**: トリガー送信タイミングが遅い
```php
// UseCaseの中でトリガーを送信すること
// UseCase外だと進捗更新が動かない
```

### 進捗が2重にカウントされる

**原因**: 同じトリガーを複数回送信している
```php
// 同じアクションで複数回addTriggers()を呼んでいないか確認
// トリガーはcollectにまとめて、最後に一度だけaddTriggers()
```

### 特定ミッションだけ反映されない

**原因**: MissionTypeによるフィルタリング
```php
// addTrigger()の第2引数でMissionTypeを指定している場合、
// 指定したタイプのミッションにしかトリガーが送られない

// 全タイプに送る場合は第2引数を省略
$this->missionManager->addTrigger($trigger);  // OK

// 特定タイプのみ
$this->missionManager->addTrigger($trigger, MissionType::DAILY);  // DAILYのみ
```
