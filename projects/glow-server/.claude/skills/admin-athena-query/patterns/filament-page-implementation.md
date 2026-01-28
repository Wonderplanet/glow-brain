# FilamentページでのAthena対応

## 目次

1. [ページネート後処理の仕組み](#ページネート後処理の仕組み)
2. [実装パターン概要](#実装パターン概要)
3. [パターンA: 基本パターン](#パターンa-基本パターン)
4. [パターンB: ページネート後処理パターン](#パターンb-ページネート後処理パターン)
5. [必須要素](#必須要素)
6. [完全な実装例（パターンB）](#完全な実装例パターンb)
7. [Bladeテンプレート](#bladeテンプレート)
8. [AthenaQueryTraitの内部動作](#athenaquerytraitの内部動作)
9. [注意点](#注意点)

---

`AthenaQueryTrait`を使用することで、自動的にAthenaクエリに切り替わるログページを実装できます。

## ページネート後処理の仕組み

Filamentのテーブル表示ではページネーションが使用されており、**表示に必要なレコードのみ**が取得されます。

`AthenaQueryTrait`も内部で`$paginator->getCollection()` → 処理 → `$paginator->setCollection($collection)`のパターンを使用しています：

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

## 実装パターン概要

ログの特性に応じて2つの実装パターンがあります：

| パターン | 使用ケース | 特徴 |
|---------|----------|------|
| **A: 基本パターン** | マスタデータ参照が不要なログ | `AthenaQueryTrait`をuseするだけ |
| **B: ページネート後処理** | マスタデータ参照が必要なログ | `getTableRecords()`オーバーライド |

---

## パターンA: 基本パターン

マスタデータ参照が不要なシンプルなログに使用します。

**使用ケース**: 追加の後処理が不要な場合のみ

**実装例**: `UserLogLogin`, `UserLogBnidLink`, `UserLogUnitRankUp`

```php
<?php

namespace App\Filament\Pages;

use App\Constants\LogTablePageConstants;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class LogXxxPage extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;          // Athena対応の核心
    use UserLogTableFilterTrait;   // 共通フィルター

    protected static string $view = 'filament.pages.log-xxx-page';

    private function table(Table $table): Table
    {
        $query = LogXxx::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->columns([...])
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

## パターンB: ページネート後処理パターン

マスタデータ参照（トリガー情報、報酬情報、キャラ名等）が必要なログに使用します。

**理由**: テーブル表示でページネーションが使用されているため、**そのページに表示するレコードのみ**に必要なマスタデータを取得する。毎回マスタデータを全件取得するのは非効率。

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

    private function table(Table $table): Table
    {
        $query = LogCoin::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->columns(self::getResourceLogColumns())
            ->filters(self::getResourceLogFilters(), FiltersLayout::AboveContent)
            ->deferFilters()
            ->defaultSort('created_at', 'desc');
    }
}
```

### よく使われるTrait

| Trait | 用途 | メソッド |
|-------|-----|---------|
| `LogTriggerInfoGetTrait` | トリガー情報（発生元機能名） | `addLogTriggerInfoToPaginatedRecords()` |
| `RewardInfoGetTrait` | 報酬/コスト情報（アイテム名等） | `addRewardInfoToPaginatedRecords()`, `addMultipleRewardInfosToPaginatedRecords()` |

### 複数の後処理を組み合わせる例

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

### カスタム後処理の実装例

既存のTraitでカバーできないケースでは、直接`getCollection()`/`setCollection()`を使用します：

```php
public function getTableRecords(): Paginator | CursorPaginator
{
    $paginator = $this->getTableRecordsWithAthena();

    $collection = $paginator->getCollection();

    // ページネート後のレコードに必要なマスタIDを収集
    $mstStageIds = $collection->pluck('mst_stage_id')->unique()->filter();

    // 必要なマスタデータのみを取得
    $mstStages = MstStage::whereIn('id', $mstStageIds)->get()->keyBy('id');

    // 各レコードにマスタデータを追加
    $collection = $collection->map(function ($record) use ($mstStages) {
        $record->mst_stage_name = $mstStages->get($record->mst_stage_id)?->name ?? '';
        return $record;
    });

    $paginator->setCollection($collection);

    return $paginator;
}
```

---

## 必須要素

### 1. AthenaQueryTrait

```php
use App\Traits\AthenaQueryTrait;

class LogXxxPage extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
```

このトレイトが`getTableRecords()`をオーバーライドし、条件に応じてAthenaクエリに切り替えます。

### 2. 日付範囲フィルター

Athena切り替え判定に `created_at_range` フィルターが必須です。

```php
use App\Traits\UserLogTableFilterTrait;

class LogXxxPage extends UserDataBasePage implements HasTable
{
    use UserLogTableFilterTrait;

    public function table(Table $table): Table
    {
        return $table
            ->filters(
                $this->getCommonLogFilters([
                    LogTablePageConstants::CREATED_AT_RANGE,  // 必須
                    LogTablePageConstants::NGINX_REQUEST_ID,
                ]),
                FiltersLayout::AboveContent
            )
            ->deferFilters();  // フィルター遅延適用
    }
}
```

### 3. deferFilters

```php
->deferFilters()
```

これにより、フィルターが「検索」ボタンを押すまで適用されません。Athenaクエリは時間がかかるため、不要なクエリ実行を防ぎます。

---

## 完全な実装例（パターンB）

```php
<?php

namespace App\Filament\Pages;

use App\Constants\LogTablePageConstants;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Log\LogExchangeAction;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\AthenaQueryTrait;
use App\Traits\RewardInfoGetTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;

class LogExchangeActionPage extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use RewardInfoGetTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.log-exchange-action-page';
    public string $currentTab = UserSearchTabs::LOG_EXCHANGE_ACTION->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    /**
     * getTableRecords()をオーバーライドして報酬情報の後処理を追加
     */
    public function getTableRecords(): Paginator | CursorPaginator
    {
        $paginator = $this->getTableRecordsWithAthena();

        // 報酬情報を追加
        $this->addMultipleRewardInfosToPaginatedRecords($paginator, 'getRewardsDtos', 'reward_info');

        // コスト情報を追加
        $this->addMultipleRewardInfosToPaginatedRecords($paginator, 'getCostsDtos', 'cost_info');

        return $paginator;
    }

    public function table(Table $table): Table
    {
        $query = LogExchangeAction::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('日時')
                    ->sortable(),
                TextColumn::make('mst_exchange_lineup_id')
                    ->label('交換所ラインナップID'),
                RewardInfoColumn::make('reward_info')
                    ->label('報酬情報'),
                RewardInfoColumn::make('cost_info')
                    ->label('コスト情報'),
                TextColumn::make('nginx_request_id')
                    ->label('Nginx Request ID'),
            ])
            ->filters(
                $this->getCommonLogFilters([
                    LogTablePageConstants::CREATED_AT_RANGE,
                    LogTablePageConstants::NGINX_REQUEST_ID,
                ]),
                FiltersLayout::AboveContent
            )
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn(Action $action) => $action->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_exchange_action')
            ]);
    }
}
```

## Bladeテンプレート

```php
// resources/views/filament/pages/log-xxx-page.blade.php
<x-filament-panels::page>
    {{ $this->table }}
</x-filament-panels::page>
```

## AthenaQueryTraitの内部動作

### getTableRecords()

```php
public function getTableRecords(): Collection | Paginator | CursorPaginator
{
    return $this->getTableRecordsWithAthena();
}
```

### shouldUseAthenaQuery()

以下の条件を全て満たすとAthenaクエリを使用：

1. 環境が `develop` または `production`
2. `created_at_range` フィルターで日付範囲が指定
3. 開始日が現在から30日以上前

### forceFillPaginatorItemsWithAthenaResults()

条件を満たした場合：

1. EloquentクエリをAthenaクエリに変換
2. バッククォート → ダブルクォートに置換
3. パーティション列 `dt` のBETWEEN条件を追加
4. タイムゾーン変換（JST→UTC）
5. AthenaOperatorでクエリ実行
6. 結果をモデルインスタンスに変換
7. **Paginatorに結果をセット**（`$paginator->setCollection($models)`）

## 注意点

1. **フィルター必須**: 日付範囲なしではAthenaクエリは実行されない
2. **ページネーション**: Athenaクエリでも最大1000件/ページ
3. **ソート**: Athenaクエリでもソートは適用される
4. **リレーション**: Athenaクエリ結果ではリレーションは使用不可
5. **CSV出力**: 現在表示中のデータのみ出力される
6. **後処理**: `AthenaQueryTrait`だけでは不十分な場合、`getTableRecords()`をオーバーライドして追加の後処理を実装
