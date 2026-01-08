# Athenaクエリ対応 運用フロー

新しいログテーブルを追加した際に、管理ツール（admin）でAthenaクエリを使用してデータを表示するための運用手順をまとめています。

## 概要

TiDBのログデータは一定期間（TTL: 31日）でデータが削除されるため、過去のログを参照するにはAWS Athenaを使用します。管理ツールでは、日付範囲フィルターで30日以上前のデータを検索する場合、自動的にDBからAthenaクエリに切り替わります。

## 対応が必要なケース

以下の場合にAthena対応が必要です：

1. **新しいログテーブル（log_*）を追加した場合**
2. **管理ツールでログテーブルの履歴ページを新規実装した場合**
3. **既存ログテーブルにカラムを追加/変更した場合**（Athenaテーブル定義の再生成が必要）

## 運用フロー

### Step 1: API側でログテーブルを実装

通常通り、api側でログテーブルのマイグレーションとモデルを実装します。

```bash
# マイグレーション作成例
sail artisan make:migration create_log_xxx_table --database=tidb
```

### Step 2: Athenaテーブル定義SQLの生成

artisanコマンドを使用して、develop/production環境用のAthenaテーブル定義SQLを生成します。

#### 特定テーブルのみ生成する場合

```bash
# develop環境用
sail artisan app:athena:generate-table \
  --table=log_xxx \
  --target-env=develop \
  --database=glow_develop_user_action_logs

# production環境用
sail artisan app:athena:generate-table \
  --table=log_xxx \
  --target-env=prod \
  --database=glow_prod_user_action_logs
```

#### 全テーブルを再生成する場合

```bash
# develop環境用（全log_*テーブル）
sail artisan app:athena:generate-table \
  --target-env=develop \
  --database=glow_develop_user_action_logs

# production環境用（全log_*テーブル）
sail artisan app:athena:generate-table \
  --target-env=prod \
  --database=glow_prod_user_action_logs
```

#### コマンドオプション

| オプション | 説明 | デフォルト値 |
|-----------|------|------------|
| `--table` | 特定のテーブル名を指定（未指定の場合は全log/usrテーブル） | - |
| `--target-env` | 環境名（develop/prod/staging等） | develop |
| `--bucket` | S3バケット名 | glow-{target-env}-datalake |
| `--database` | CREATE TABLE文内のデータベース名 | **必須** |
| `--start-date` | パーティション開始日 | 2025/07/01 |

生成されたSQLファイルは以下のパスに出力されます：
- `admin/database/athena_tables/{env}/{database}/{table_name}.sql`

### Step 3: 管理ツールでログページを実装

Filamentページでログを表示する実装を行います。

#### 3-1. Modelの実装パターン

ログモデルに`IAthenaModel`インターフェースと`AthenaModelTrait`を実装します。ログの特性に応じて2つのパターンがあります。

---

##### モデルパターンA: 基本パターン

**使用ケース**: Filamentページでの追加の後処理が不要なシンプルなログ

**特徴**: `IAthenaModel` + `AthenaModelTrait`をuseし、必要に応じてリレーションを定義

**実装例**: `LogLogin`, `LogGachaAction`, `LogStageAction`, `LogPvpAction`

```php
<?php

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

##### モデルパターンB: Filamentページ後処理対応パターン

**使用ケース**: Filamentページでページネート後処理（トリガー情報、報酬情報等の表示）が必要なログ

**理由**: Filamentページの`LogTriggerInfoGetTrait`や`RewardInfoGetTrait`と連携して、ページネート後のレコードに対してのみ必要なデータを取得・表示するため

**特徴**: Filamentページ後処理で使用するデータ変換メソッド（アクセサ or DTOコレクション返却メソッド）を定義

---

###### B-1: LogTriggerアクセサ（トリガー情報）

`trigger_source`/`trigger_value`カラムを持つリソース変動ログで使用。

**実装例**: `LogCoin`, `LogStamina`, `LogItem`, `LogExp`, `LogEmblem`

```php
<?php

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

---

###### B-2: RewardDto変換（報酬/コスト情報）

JSONカラムに報酬やコスト情報が配列で保存されているログで使用。

**実装例**: `LogExchangeAction`, `LogReceiveMessageReward`, `LogTradeShopItem`

```php
<?php

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

---

##### モデルパターン選択の判断基準

| 判断ポイント | パターンA | パターンB |
|------------|:--------:|:--------:|
| Filamentページでの追加の後処理が不要 | ✅ | - |
| trigger_source/trigger_value列がある | - | ✅ B-1 |
| rewards/costs等のJSONカラムがある | - | ✅ B-2 |
| 複合パターン（トリガー + JSON変換） | - | ✅ B-1 + B-2 |

**ポイント**: パターンBは、Filamentページ側のページネート後処理（`LogTriggerInfoGetTrait`、`RewardInfoGetTrait`等）と連携するためのデータ変換メソッドを提供します。どちらもFilamentページのパターンB（ページネート後処理パターン）と組み合わせて使用します。

---

##### IAthenaModel実装クラス一覧

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

#### 3-2. Filamentページの実装パターン

`AthenaQueryTrait`を使用したFilamentページの実装には、ログの特性に応じて複数のパターンがあります。

---

##### ページネート後処理の仕組み

Filamentのテーブル表示ではページネーションが使用されており、**表示に必要なレコードのみ**が取得されます。

`AthenaQueryTrait`も内部で同じパターン（`$paginator->getCollection()` → 処理 → `$paginator->setCollection($collection)`）を使用しています：

```php
// AthenaQueryTrait内部の処理
private function forceFillPaginatorItemsWithAthenaResults(Paginator $paginator): void
{
    // ... Athenaクエリ実行 ...

    // Athenaクエリ結果でpaginatorの中身を置き換え
    $paginator->setCollection($models);
}
```

**重要**: `AthenaQueryTrait`をuseするだけでは、Athenaクエリ結果への置き換えのみが行われます。それ以外の後処理（トリガー情報、報酬情報等のマスタデータ参照）が必要な場合は、`getTableRecords()`をオーバーライドして追加の後処理を実装する必要があります。

マスタデータの参照が必要な場合、毎回全件取得するのは非効率です。そのため、**ページネート後のレコードに対してのみ**必要なマスタデータを取得する実装が重要です。

```php
// 追加の後処理が必要な場合のパターン
public function getTableRecords(): Paginator | CursorPaginator
{
    // AthenaQueryTraitの処理（Athena結果への置き換え）
    $paginator = $this->getTableRecordsWithAthena();

    // ページネート後のレコードのみ取得
    $collection = $paginator->getCollection();

    // 必要なマスタデータを取得して追加
    // ... 処理 ...

    // 処理結果をpaginatorに戻す
    $paginator->setCollection($collection);

    return $paginator;
}
```

この処理を特定ケースで使いやすくラップしたのが `LogTriggerInfoGetTrait` や `RewardInfoGetTrait` です。

---

##### パターンA: 基本パターン（シンプルなログ表示）

**使用ケース**: マスタデータ参照が不要なシンプルなログ表示

**特徴**: `AthenaQueryTrait`をuseするだけで、自動的にAthenaクエリに対応。追加の後処理が不要な場合のみ使用可能。

**実装例**: `UserLogLogin`, `UserLogBnidLink`, `UserLogUnitRankUp`

```php
<?php

namespace App\Filament\Pages;

use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;

class LogXxxPage extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;  // これを追加するだけでAthena対応
    use UserLogTableFilterTrait;

    private function table(Table $table): Table
    {
        $query = LogXxx::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->columns([
                // カラム定義
            ])
            ->filters(
                $this->getCommonLogFilters([
                    LogTablePageConstants::CREATED_AT_RANGE,
                    LogTablePageConstants::NGINX_REQUEST_ID,
                ]),
                FiltersLayout::AboveContent
            )
            ->deferFilters()
            ->defaultSort('created_at', 'desc');
    }
}
```

---

##### パターンB: ページネート後処理パターン

**使用ケース**: ログ表示時にマスタデータ参照が必要な場合（トリガー情報、報酬情報、キャラ名等）

**理由**: テーブル表示でページネーションが使用されているため、**そのページに表示するレコードのみ**に必要なマスタデータを取得する。毎回マスタデータを全件取得するのは非効率。

**特徴**: `getTableRecords()`をオーバーライドし、`$paginator->getCollection()` → 処理 → `$paginator->setCollection($collection)` のパターンで後処理を追加

**実装例**: `UserLogCoin`, `UserLogStamina`, `UserLogItem`, `LogExchangeActionPage`, `UserLogReceiveMessageReward`

```php
<?php

namespace App\Filament\Pages;

use App\Traits\AthenaQueryTrait;
use App\Traits\LogTriggerInfoGetTrait;
use App\Traits\RewardInfoGetTrait;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;

class UserLogCoin extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use LogTriggerInfoGetTrait;  // トリガー情報取得用Trait

    /**
     * getTableRecords()をオーバーライドして後処理を追加
     */
    public function getTableRecords(): Paginator | CursorPaginator
    {
        // AthenaQueryTraitの処理を実行
        $paginator = $this->getTableRecordsWithAthena();

        // ページネート後のレコードに対してのみトリガー情報を追加
        // 内部で getCollection() → 処理 → setCollection() している
        $this->addLogTriggerInfoToPaginatedRecords($paginator);

        return $paginator;
    }

    // ...
}
```

**よく使われるTrait**:

| Trait | 用途 | メソッド |
|-------|-----|---------|
| `LogTriggerInfoGetTrait` | トリガー情報（発生元機能名） | `addLogTriggerInfoToPaginatedRecords()` |
| `RewardInfoGetTrait` | 報酬/コスト情報（アイテム名等） | `addRewardInfoToPaginatedRecords()`, `addMultipleRewardInfosToPaginatedRecords()` |

**複数の後処理を組み合わせる例**:

```php
public function getTableRecords(): Paginator | CursorPaginator
{
    $paginator = $this->getTableRecordsWithAthena();

    // トリガー情報を追加
    $this->addLogTriggerInfoToPaginatedRecords($paginator);

    // 報酬情報を追加
    $this->addMultipleRewardInfosToPaginatedRecords($paginator, 'getRewardsDtos', 'reward_info');

    return $paginator;
}
```

---

##### パターン選択の判断基準

| 判断ポイント | パターンA | パターンB |
|------------|:--------:|:--------:|
| マスタデータ参照が不要 | ✅ | - |
| trigger_value列がある（リソース変動ログ） | - | ✅ `LogTriggerInfoGetTrait` |
| 報酬/コスト情報を表示 | - | ✅ `RewardInfoGetTrait` |
| キャラ名、ステージ名等を表示 | - | ✅ カスタム後処理 |

### Step 4: PRに含めてレビュー

以下のファイルをPRに含めます：
- `admin/database/athena_tables/develop/{database}/{table_name}.sql`
- `admin/database/athena_tables/prod/{database}/{table_name}.sql`
- 管理ツールのModel、Filamentページ実装

### Step 5: 各環境のAthenaでテーブル作成

PRがマージされた後、**各環境のAWS Athenaコンソールで手動でSQLを実行**してテーブルを作成します。

1. AWSコンソールにログイン
2. Athenaサービスを開く
3. 対象の環境（develop/production）のワークグループを選択
4. 生成されたSQLファイルの内容をクエリエディタに貼り付けて実行

```sql
-- 実行例：log_xxx テーブルの作成
CREATE EXTERNAL TABLE `glow_develop_user_action_logs`.`log_xxx` (
    ...
)
PARTITIONED BY (`dt` string)
...
```

## ファイル構成

```
admin/
├── app/
│   ├── Console/Commands/
│   │   └── AthenaGenerateTableCommand.php  # テーブル定義生成コマンド
│   ├── Contracts/
│   │   └── IAthenaModel.php                # Athenaモデルインターフェース
│   ├── Operators/
│   │   └── AthenaOperator.php              # Athenaクエリ実行
│   ├── Traits/
│   │   ├── AthenaModelTrait.php            # モデル用トレイト
│   │   └── AthenaQueryTrait.php            # ページ用トレイト
│   └── Constants/
│       └── AthenaConstant.php              # Athena関連定数
└── database/
    └── athena_tables/
        ├── templates/
        │   └── athena_create_table_csv_daily.sql  # テンプレート
        ├── develop/
        │   └── glow_develop_user_action_logs/
        │       └── log_xxx.sql
        └── prod/
            └── glow_prod_user_action_logs/
                └── log_xxx.sql
```

## 動作の仕組み

### 自動切り替えの条件

`AthenaQueryTrait`を使用したページでは、以下の条件を満たすと自動的にAthenaクエリが使用されます：

1. 環境が`develop`または`production`である
2. `created_at_range`フィルターで日付範囲が指定されている
3. 開始日が現在から30日以上前である

### データフロー

```
1. ユーザーがログページで日付範囲を指定
2. shouldUseAthenaQuery()で条件チェック
3. 条件を満たす場合：
   - EloquentクエリをAthenaクエリに変換
   - AthenaOperatorでクエリ実行
   - 結果をModelインスタンスに変換
   - Paginatorに結果をセット
4. 条件を満たさない場合：
   - 通常のDBクエリを実行
```

## 注意事項

1. **Athenaテーブル作成は手動**: SQLファイルの生成は自動ですが、Athenaへのテーブル作成は各環境で手動実行が必要です
2. **パーティション**: `dt`カラムでパーティショニングされています。日付範囲フィルターは必須です
3. **タイムゾーン**: Athenaのデータ日時はUTC、表示時にJST変換されます
4. **NULL値の扱い**: CSVSerdeの制約により、NULLABLEカラムはstring型として定義されます
5. **結果件数制限**: 1ページあたり最大1000件まで取得可能です

## 参考実装

### PR例

- [#2021 交換所管理画面を追加](https://github.com/Wonderplanet/glow-server/pull/2021)

### AthenaQueryTrait使用クラス一覧

現在`AthenaQueryTrait`を使用しているFilamentページの一覧です。新規実装時の参考にしてください。

#### パターンA: 基本パターン（マスタデータ参照不要）

| クラス名 | ログテーブル | 説明 |
|---------|------------|------|
| `UserLogLogin` | log_logins | ログイン履歴 |
| `UserLogBnidLink` | log_bnid_links | BNID連携履歴 |
| `UserLogPvpAction` | log_pvp_actions | PvP履歴 |
| `UserLogAdventBattleAction` | log_advent_battle_actions | 降臨バトル履歴 |
| `UserLogArtworkFragment` | log_artwork_fragments | 原画のかけら履歴 |
| `UserLogOutpostEnhancement` | log_outpost_enhancements | ゲート強化履歴 |
| `UserLogUnitRankUp` | log_unit_rank_ups | ユニットランクアップ履歴 |
| `UserLogUnitLevelUp` | log_unit_level_ups | ユニットレベルアップ履歴 |
| `UserLogUnitGradeUp` | log_unit_grade_ups | ユニットグレードアップ履歴 |

#### パターンB: ページネート後処理パターン

##### LogTriggerInfoGetTrait使用（トリガー情報）

| クラス名 | ログテーブル | 説明 |
|---------|------------|------|
| `UserLogCoin` | log_coins | コイン変動履歴 |
| `UserLogStamina` | log_staminas | スタミナ変動履歴 |
| `UserLogItem` | log_items | アイテム変動履歴 |
| `UserLogExp` | log_exps | EXP変動履歴 |
| `UserLogEmblem` | log_emblems | エンブレム変動履歴 |

##### RewardInfoGetTrait使用（報酬/コスト情報）

| クラス名 | ログテーブル | 説明 |
|---------|------------|------|
| `LogExchangeActionPage` | log_exchange_actions | 交換所履歴 |
| `UserLogTradeShopItem` | log_trade_shop_items | ショップ交換履歴 |
| `UserLogReceiveMessageReward` | log_receive_message_rewards | メッセージ報酬受取履歴 |

##### カスタム後処理

| クラス名 | ログテーブル | 説明 |
|---------|------------|------|
| `UserLogStageAction` | log_stage_actions | ステージ履歴（パーティ情報表示） |
| `UserLogGachaAction` | log_gacha_actions | ガシャ履歴（排出物情報表示） |
