# UsrModelキャッシュパターン

UsrModelManager/UsrModelCacheRepositoryを使用したユーザーモデル専用のキャッシュ実装パターンについて説明します。

## パターン概要

UsrModelキャッシュは、APIリクエスト中のユーザーデータ（UsrModelInterface実装クラス）をメモリ上にキャッシュする仕組みです。

**特徴:**
- リクエストスコープでのキャッシュ（リクエスト終了後は破棄）
- UsrModelManagerによる一元管理
- 変更検知機能（レスポンスに差分のみ含める）
- 型安全性の保証

**適用ケース:**
- ユーザーデータ（usr_*テーブル）のキャッシュ
- リクエスト中の複数回読み込みを削減
- APIレスポンスで変更があったモデルのみ返す

## 基本構造

### Repositoryクラスの構成

UsrModelCacheRepositoryを継承してRepositoryを作成します。

```php
<?php

declare(strict_types=1);

namespace App\Domain\{Domain}\Repositories;

use App\Domain\{Domain}\Models\{Model};
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;

class {Model}Repository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = {Model}::class;

    // Repositoryメソッド
}
```

### UsrModelManagerの注入

```php
public function __construct(
    protected UsrModelManager $usrModelManager,
) {
    parent::__construct($usrModelManager);
}
```

## キャッシュの種類

### 1. UsrModelMultiCacheRepository（複数レコード）

ユーザーが複数レコードを持つテーブル用。

**例:**
- UsrItem（アイテム）
- UsrUnit（ユニット）
- UsrStage（ステージ）

```php
class UsrItemRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrItem::class;

    public function getAllByUserId(string $usrUserId): Collection
    {
        // キャッシュから取得を試みる
        if ($this->isAllFetched()) {
            return collect($this->getCache());
        }

        // DBから取得
        $models = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->get();

        // キャッシュに保存
        $this->syncModels($models);
        $this->markAllFetched($usrUserId);

        return $models;
    }
}
```

### 2. UsrModelSingleCacheRepository（単一レコード）

ユーザーが1レコードのみ持つテーブル用。

**例:**
- UsrUser（ユーザー基本情報）
- UsrUserProfile（ユーザープロフィール）
- UsrTutorial（チュートリアル進捗）

```php
class UsrUserRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUser::class;

    public function getByUserId(string $usrUserId): ?UsrUser
    {
        // キャッシュから取得を試みる
        $cache = $this->getCache();
        if (!empty($cache)) {
            return current($cache);
        }

        // DBから取得
        $model = UsrUser::query()
            ->where('usr_user_id', $usrUserId)
            ->first();

        if ($model === null) {
            return null;
        }

        // キャッシュに保存
        $this->syncModels(collect([$model]));

        return $model;
    }
}
```

### 3. UsrModelNoCacheRepository（キャッシュなし）

キャッシュを使用しないRepository。

**適用ケース:**
- ログテーブル（書き込み専用）
- 大量データを扱うテーブル
- リクエスト中に複数回読み込まないテーブル

## キャッシュ操作

### syncModels（キャッシュに追加・更新）

モデルをキャッシュに追加または上書きします。

```php
public function getAllByUserId(string $usrUserId): Collection
{
    if ($this->isAllFetched()) {
        return collect($this->getCache());
    }

    $models = UsrItem::query()
        ->where('usr_user_id', $usrUserId)
        ->get();

    // キャッシュに保存
    $this->syncModels($models);
    $this->markAllFetched($usrUserId);

    return $models;
}
```

**重要:**
- syncModelsは自動的にUsrModelManagerに登録する
- 同じmodelKeyのモデルは上書きされる
- Collection型を渡す

### getCache（キャッシュ取得）

キャッシュから全モデルを取得します。

```php
public function getAllFromCache(): array
{
    // クローンしたモデルを取得（デフォルト）
    return $this->getCache();

    // クローンせずに取得（内部処理用）
    return $this->getCache(false);
}
```

**クローンの有無:**
- `getCache(true)`: クローンしたモデルを返す（デフォルト、推奨）
- `getCache(false)`: クローンせずに返す（内部処理の効率化用）

### getCacheWhereIn（条件でフィルタリング）

キャッシュから条件に合うモデルを取得します。

```php
public function getItemsByMstItemIds(array $mstItemIds): array
{
    // キャッシュがない場合は空配列を返す
    if (!$this->isAllFetched()) {
        return [];
    }

    // キャッシュからmst_item_idでフィルタリング
    return $this->getCacheWhereIn('mst_item_id', $mstItemIds);
}
```

**使用例:**
```php
$items = $this->usrItemRepository->getItemsByMstItemIds(['item_1', 'item_2', 'item_3']);
```

### getChangedModels（変更検知）

APIリクエスト実行前と比較して、変更があったモデルのみを返します。

```php
public function getChangedItems(): Collection
{
    return $this->getChangedModels();
}
```

**使用例（UseCase）:**
```php
public function execute(): ResultData
{
    // ビジネスロジック実行（モデル変更）
    $this->itemService->addItem($userId, 'item_1', 10);

    // 変更があったモデルのみ取得してレスポンスに含める
    $changedItems = $this->usrItemRepository->getChangedItems();

    return new ResultData($changedItems);
}
```

### isAllFetched（全取得済みフラグ）

対象テーブルの全データを取得済みかを確認します。

```php
public function getAllItems(string $usrUserId): Collection
{
    if ($this->isAllFetched()) {
        // 既に全データを取得済みなのでキャッシュから返す
        return collect($this->getCache());
    }

    // DBから全データ取得
    $models = UsrItem::query()
        ->where('usr_user_id', $usrUserId)
        ->get();

    $this->syncModels($models);
    $this->markAllFetched($usrUserId); // フラグを立てる

    return $models;
}
```

### markAllFetched（全取得済みフラグを設定）

全データ取得済みであることを記録します。

```php
$this->markAllFetched($usrUserId);
```

**注意:**
- apiリクエストを送ったユーザー本人の場合のみフラグを立てる
- 他のユーザーのデータを取得した場合はフラグを立てない

## 実装パターン

### パターン1: 基本的なCRUD操作

```php
class UsrItemRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrItem::class;

    /**
     * 全アイテムを取得
     */
    public function getAllByUserId(string $usrUserId): Collection
    {
        if ($this->isAllFetched()) {
            return collect($this->getCache());
        }

        $models = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->get();

        $this->syncModels($models);
        $this->markAllFetched($usrUserId);

        return $models;
    }

    /**
     * IDで1件取得
     */
    public function getById(string $usrUserId, string $usrItemId): ?UsrItem
    {
        // キャッシュから取得を試みる
        $cache = $this->getCache();
        if (isset($cache[$usrItemId])) {
            return $cache[$usrItemId];
        }

        // DBから取得
        $model = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('usr_item_id', $usrItemId)
            ->first();

        if ($model === null) {
            return null;
        }

        // キャッシュに保存
        $this->syncModels(collect([$model]));

        return $model;
    }

    /**
     * 保存
     */
    public function save(UsrItem $model): void
    {
        $model->save();

        // キャッシュに反映
        $this->syncModels(collect([$model]));
    }
}
```

### パターン2: 条件での取得

```php
class UsrItemRepository extends UsrModelMultiCacheRepository
{
    /**
     * mst_item_idで複数取得
     */
    public function getByMstItemIds(string $usrUserId, array $mstItemIds): Collection
    {
        // 全データ取得済みならキャッシュから取得
        if ($this->isAllFetched()) {
            $cachedItems = $this->getCacheWhereIn('mst_item_id', $mstItemIds);
            return collect($cachedItems);
        }

        // DBから取得
        $models = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_item_id', $mstItemIds)
            ->get();

        // キャッシュに保存
        $this->syncModels($models);

        return $models;
    }

    /**
     * 特定のアイテムタイプを取得
     */
    public function getByItemType(string $usrUserId, int $itemType): Collection
    {
        // 全データ取得済みならキャッシュから取得
        if ($this->isAllFetched()) {
            $cachedItems = $this->getCacheWhereIn('item_type', [$itemType]);
            return collect($cachedItems);
        }

        // DBから取得
        $models = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('item_type', $itemType)
            ->get();

        // キャッシュに保存
        $this->syncModels($models);

        return $models;
    }
}
```

### パターン3: バッチ更新

```php
class UsrItemRepository extends UsrModelMultiCacheRepository
{
    /**
     * 複数アイテムを一括保存
     */
    public function saveBatch(Collection $models): void
    {
        // トランザクション内でバッチ更新
        DB::transaction(function () use ($models) {
            foreach ($models as $model) {
                $model->save();
            }
        });

        // キャッシュに反映
        $this->syncModels($models);
    }
}
```

### パターン4: 変更検知を利用したレスポンス最適化

```php
class ItemAddUseCase
{
    public function execute(string $usrUserId, string $mstItemId, int $amount): ResultData
    {
        // アイテム追加処理
        $this->itemService->addItem($usrUserId, $mstItemId, $amount);

        // 変更があったアイテムのみ取得
        $changedItems = $this->usrItemRepository->getChangedModels();

        // レスポンスに変更があったアイテムのみ含める
        return new ResultData([
            'items' => $changedItems,
        ]);
    }
}
```

## モデルキーの扱い

### makeModelKey（モデルキー生成）

モデルを一意に識別するキーを生成します。デフォルトでは`id`が使用されます。

```php
// UsrModel.php（基底クラス）
public function makeModelKey(): string
{
    return $this->id; // デフォルトはid
}
```

### カスタムモデルキー

複合キーが必要な場合はオーバーライドします。

```php
// UsrConditionPack.php
public function makeModelKey(): string
{
    // usr_user_id + condition_type_id の組み合わせをキーにする
    return "{$this->usr_user_id}_{$this->condition_type_id}";
}
```

**理由:**
- DBスキーマのユニークキーと一致させる
- idがUUIDで毎回生成される場合に対応

## バリデーション

### isValidModel（モデル検証）

モデルが正しいクラス・ユーザーIDかを検証します。

```php
// 自動で実行される（syncModels内部）
public function isValidModel(UsrModelInterface $model): bool
{
    $isValidClass = $model instanceof $this->modelClass;
    $isValidUser = $this->isOwnUsrUserId($model->getUsrUserId());

    if ($isValidClass === false || $isValidUser === false) {
        throw new GameException(
            ErrorCode::INVALID_PARAMETER,
            sprintf(
                'this model class is invalid. (model class: %s, user id check: %s)',
                get_class($model),
                (string) $isValidUser ? 'true' : 'false',
            ),
        );
    }

    return true;
}
```

**検証内容:**
- モデルクラスが一致しているか
- リクエストユーザー本人のデータか

## ベストプラクティス

### DO（推奨）

✅ UsrModelCacheRepositoryを継承する
✅ 全データ取得時はmarkAllFetchedを呼ぶ
✅ syncModelsで常にキャッシュを更新する
✅ getChangedModelsでレスポンス最適化する

```php
// ✅ 正しい実装
public function getAllItems(string $usrUserId): Collection
{
    if ($this->isAllFetched()) {
        return collect($this->getCache());
    }

    $models = UsrItem::query()
        ->where('usr_user_id', $usrUserId)
        ->get();

    $this->syncModels($models);
    $this->markAllFetched($usrUserId);

    return $models;
}
```

### DON'T（非推奨）

❌ キャッシュを使わずにDBから毎回取得
❌ syncModelsを呼ばずにDB更新
❌ 他ユーザーのデータでmarkAllFetchedを呼ぶ

```php
// ❌ 間違った実装
public function getAllItems(string $usrUserId): Collection
{
    // キャッシュをチェックせずに毎回DB取得
    return UsrItem::query()
        ->where('usr_user_id', $usrUserId)
        ->get();
}

// ❌ キャッシュを更新せずにDB保存
public function save(UsrItem $model): void
{
    $model->save();
    // syncModelsを呼んでいない！
}
```

## UsrModelManagerの内部動作

### リクエストスコープ

UsrModelManagerはリクエストスコープで動作し、リクエスト終了後にキャッシュは破棄されます。

**流れ:**
1. リクエスト開始
2. UsrModelManagerがインスタンス化
3. 各Repositoryがキャッシュを使用
4. リクエスト終了 → キャッシュ破棄

### キャッシュの構造

```php
// UsrModelManager内部の構造（イメージ）
[
    UsrItemRepository::class => [
        'item_1' => UsrItem {...},
        'item_2' => UsrItem {...},
    ],
    UsrUnitRepository::class => [
        'unit_1' => UsrUnit {...},
        'unit_2' => UsrUnit {...},
    ],
]
```

Repository単位でキャッシュが管理されています。

## まとめ

UsrModelキャッシュパターンは、リクエスト中のユーザーデータアクセスを最適化する強力な仕組みです。正しく使用することで、DB負荷を削減し、レスポンス時間を短縮できます。
