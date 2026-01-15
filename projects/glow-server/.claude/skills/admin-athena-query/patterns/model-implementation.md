# Model実装パターン

## 目次

1. [基本構造](#基本構造)
2. [パターン概要](#パターン概要)
3. [パターンA: 基本パターン](#パターンa-基本パターン)
4. [パターンB: Filamentページ後処理対応パターン](#パターンb-filamentページ後処理対応パターン)
5. [パターン選択の判断基準](#パターン選択の判断基準)
6. [IAthenaModel実装クラス一覧](#iathenamodel実装クラス一覧)
7. [AthenaModelTraitの内部動作](#athenamodetraitの内部動作)
8. [注意点](#注意点)

---

Athenaクエリ結果からモデルを作成するには、`IAthenaModel`インターフェースと`AthenaModelTrait`を実装します。

## 基本構造

```php
<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Traits\AthenaModelTrait;

class LogXxx extends BaseLogXxx implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;
}
```

## パターン概要

ログの特性に応じて2つの実装パターンがあります：

| パターン | 使用ケース | 特徴 |
|---------|----------|------|
| **A: 基本パターン** | Filamentページでの追加の後処理が不要なシンプルなログ | `IAthenaModel` + `AthenaModelTrait`のみ |
| **B: Filamentページ後処理対応** | ページネート後処理（トリガー情報、報酬情報等の表示）が必要なログ | データ変換メソッドを定義 |

---

## パターンA: 基本パターン

Filamentページでの追加の後処理が不要なシンプルなログに使用します。

**実装例**: `LogLogin`, `LogGachaAction`, `LogStageAction`, `LogPvpAction`

```php
<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Stage\Models\LogStageAction as BaseLogStageAction;
use App\Models\Mst\MstStage;
use App\Models\Mst\MstArtwork;
use App\Traits\AthenaModelTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogStageAction extends BaseLogStageAction implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    // DB参照時のリレーション（Athenaクエリ時は使用不可）
    public function mst_stage(): BelongsTo
    {
        return $this->belongsTo(MstStage::class, 'mst_stage_id', 'id');
    }

    public function mst_artwork(): BelongsTo
    {
        return $this->belongsTo(MstArtwork::class, 'mst_artwork_id', 'id');
    }
}
```

**注意**: リレーションはDB参照時のみ有効です。Athenaクエリ時はリレーションが使用できないため、Filamentページ側でページネート後処理が必要な場合はパターンBを参照。

---

## パターンB: Filamentページ後処理対応パターン

Filamentページでページネート後処理（トリガー情報、報酬情報等の表示）が必要なログに使用します。

**理由**: Filamentページの`LogTriggerInfoGetTrait`や`RewardInfoGetTrait`と連携して、ページネート後のレコードに対してのみ必要なデータを取得・表示するため

**特徴**: Filamentページ後処理で使用するデータ変換メソッド（アクセサ or DTOコレクション返却メソッド）を定義

---

### B-1: LogTriggerアクセサ（トリガー情報）

`trigger_source`/`trigger_value`カラムを持つリソース変動ログで使用。

**実装例**: `LogCoin`, `LogStamina`, `LogItem`, `LogExp`, `LogEmblem`

```php
<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\User\Models\LogCoin as BaseLogCoin;
use App\Dtos\LogTriggerDto;
use App\Traits\AthenaModelTrait;

class LogCoin extends BaseLogCoin implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    /**
     * トリガー情報をDTOとして取得
     * LogTriggerInfoGetTraitと連携して使用
     */
    public function getLogTriggerAttribute(): LogTriggerDto
    {
        return new LogTriggerDto(
            $this->trigger_source,
            $this->trigger_value ?? '',
            $this->trigger_option ?? '',
        );
    }

    /**
     * トリガー情報のキーを取得（キャッシュ用）
     */
    public function getLogTriggerKeyAttribute(): string
    {
        return $this->trigger_source . $this->trigger_value;
    }
}
```

**ポイント**:
- `LogTriggerDto`はFilamentページ側の`LogTriggerInfoGetTrait`で処理される
- `log_trigger_key`属性はトリガー情報のキャッシュキーとして使用

---

### B-2: RewardDto変換（報酬/コスト情報）

JSONカラムに報酬やコスト情報が配列で保存されているログに使用。

**実装例**: `LogExchangeAction`, `LogReceiveMessageReward`, `LogTradeShopItem`

```php
<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Exchange\Models\LogExchangeAction as BaseLogExchangeAction;
use App\Dtos\RewardDto;
use App\Entities\Reward;
use App\Traits\AthenaModelTrait;
use Illuminate\Support\Collection;

class LogExchangeAction extends BaseLogExchangeAction implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    /**
     * 報酬エンティティのキャッシュ（遅延初期化用）
     */
    private ?Collection $rewardsEntitiesCache = null;

    /**
     * 報酬配列をRewardエンティティのCollectionに変換（遅延初期化）
     */
    private function getRewardsEntities(): Collection
    {
        if ($this->rewardsEntitiesCache !== null) {
            return $this->rewardsEntitiesCache;
        }

        $rewardsArray = $this->rewards ?? [];

        $result = collect();
        foreach ($rewardsArray as $rewardArray) {
            $result->push(Reward::createByArray($rewardArray));
        }

        $this->rewardsEntitiesCache = $result;
        return $this->rewardsEntitiesCache;
    }

    /**
     * 報酬をRewardDtoのCollectionとして取得
     * @return Collection<RewardDto>
     */
    public function getRewardsDtos(): Collection
    {
        $result = collect();
        foreach ($this->getRewardsEntities() as $reward) {
            $result->push(
                new RewardDto(
                    $reward->getId(),
                    $reward->getType(),
                    $reward->getResourceId(),
                    $reward->getAmount()
                )
            );
        }

        return $result;
    }

    /**
     * コストをRewardDtoのCollectionとして取得
     * @return Collection<RewardDto>
     */
    public function getCostsDtos(): Collection
    {
        $result = collect();
        $costsArray = $this->costs ?? [];

        foreach ($costsArray as $index => $cost) {
            $result->push(
                new RewardDto(
                    $this->id . '_cost_' . $index,
                    $cost['cost_type'] ?? '',
                    $cost['cost_id'] ?? null,
                    $cost['cost_amount'] ?? 0
                )
            );
        }

        return $result;
    }
}
```

**ポイント**:
- 遅延初期化パターンで複数回呼び出し時のパフォーマンスを最適化
- JSON配列のキー名はAPI側の保存形式に合わせる（PascalCase/snake_case等）
- Filamentページで`RewardInfoColumn`と組み合わせて使用

### JSON配列のキー名について

API側での保存形式によってキー名が異なります：

```php
// rewardsカラム（PascalCase）
[
    'resourceType' => 'Coin',
    'resourceId' => null,
    'resourceAmount' => 100
]

// costsカラム（snake_case）
[
    'cost_type' => 'Coin',
    'cost_id' => null,
    'cost_amount' => 50
]
```

対応するDTO変換では、適切なキー名を使用してください。

---

## パターン選択の判断基準

| 判断ポイント | パターンA | パターンB |
|------------|:--------:|:--------:|
| Filamentページでの追加の後処理が不要 | ✅ | - |
| trigger_source/trigger_value列がある | - | ✅ B-1 |
| rewards/costs等のJSONカラムがある | - | ✅ B-2 |
| 複合パターン（トリガー + JSON変換） | - | ✅ B-1 + B-2 |

**ポイント**: パターンBは、Filamentページ側のページネート後処理（`LogTriggerInfoGetTrait`、`RewardInfoGetTrait`等）と連携するためのデータ変換メソッドを提供します。どちらもFilamentページのパターンB（ページネート後処理パターン）と組み合わせて使用します。

---

## IAthenaModel実装クラス一覧

現在`IAthenaModel`を実装しているModelの一覧です。

| クラス名 | パターン | 説明 |
|---------|:-------:|------|
| `LogCoin` | B-1 | コイン変動ログ（トリガー情報） |
| `LogEmblem` | B-1 | エンブレム変動ログ（トリガー情報） |
| `LogExchangeAction` | B-2 | 交換所ログ（報酬/コストDTO） |
| `LogExp` | B-1 | EXP変動ログ（トリガー情報） |
| `LogGacha` | A | ガシャログ |
| `LogGachaAction` | A | ガシャアクションログ |
| `LogItem` | B-1 | アイテム変動ログ（トリガー情報） |
| `LogLogin` | A | ログインログ |
| `LogPvpAction` | A | PvPアクションログ |
| `LogReceiveMessageReward` | B-2 | メッセージ報酬受取ログ（報酬DTO） |
| `LogStageAction` | A | ステージアクションログ |
| `LogStamina` | B-1 | スタミナ変動ログ（トリガー情報） |
| `LogTradeShopItem` | B-2 | ショップ交換ログ（報酬DTO） |
| `LogUnitGradeUp` | A | ユニットグレードアップログ |
| `LogUnitLevelUp` | A | ユニットレベルアップログ |
| `LogUnitRankUp` | A | ユニットランクアップログ |

---

## AthenaModelTraitの内部動作

### createFromAthenaArray()

```php
public static function createFromAthenaArray(array $data): static
{
    $model = new self();
    $model->fillFromAthenaData($data);
    return $model;
}
```

### fillFromAthenaData()

```php
protected function fillFromAthenaData(array $data): void
{
    $dateTimeFields = ['created_at', 'updated_at'];

    foreach ($data as $key => $value) {
        if (in_array($key, $dateTimeFields) && !is_null($value)) {
            // UTC→JSTに変換
            $this->$key = $this->parseAthenaDateTime($value);
        } else {
            $this->$key = $value;
        }
    }
}
```

---

## 注意点

1. **Athena結果はstring型**: NULLABLEカラムは全てstringとして返される
2. **JSONカラムの扱い**: AthenaからはJSON文字列として返される。必要に応じてデコード
3. **タイムゾーン**: `created_at`, `updated_at`は自動的にUTC→JSTに変換される
4. **リレーション**: Athenaクエリ時は使用不可。マスタ参照が必要な場合は別途取得
5. **キャスト**: ベースモデルの`$casts`定義は引き継がれるが、Athena結果では手動変換が必要な場合あり
