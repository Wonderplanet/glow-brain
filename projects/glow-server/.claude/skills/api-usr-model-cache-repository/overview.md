# UsrModelManagerとUsrModelCacheRepositoryの概要

## UsrModelManagerとは

ユーザーデータモデルのキャッシュを管理するクラスです。1リクエスト中のシングルトンとして生成され、同一リクエスト内でのDBアクセスを最小化します。

### 主要機能

| 機能 | 説明 |
|------|------|
| キャッシュ管理 | 同一リクエスト内でのDBアクセスを最小化 |
| 変更追跡 | isDirtyなモデルを追跡し、DB更新が必要なモデルを管理 |
| 一括保存 | トランザクション内で変更モデルを一括更新 |
| 変更差分取得 | レスポンス用に変更があったモデルのみ取得 |

### 動作フロー

```
1. リクエスト開始時にUsrModelManagerが生成される
2. Repository経由でモデル取得時、キャッシュを確認
   - キャッシュにあれば返却（DBアクセスなし）
   - なければDBから取得してキャッシュに保存
3. モデル更新時、自動的にisDirty=trueになる
4. applyUserTransactionChanges()内でsaveAll()が呼ばれる
   - isDirty=trueのモデルのみDB更新
   - 更新後、isDirty=falseに変更
5. getChangedModels()で変更があったモデルを取得（レスポンス用）
```

## UsrModelCacheRepositoryの種類

### 1. UsrModelSingleCacheRepository

**用途:** 1ユーザーあたりのレコード数が最大でも1つになるテーブル

**継承するテーブル例:**
- `usr_user` - ユーザー基本情報
- `usr_user_login` - ユーザーログイン情報
- `usr_user_profile` - ユーザープロフィール

**特徴:**
- `cachedGetOne()` メソッドを使用
- 1回DB取得したら、以降はキャッシュから取得
- `isAllFetched()` で全データ取得済みかを管理

### 2. UsrModelMultiCacheRepository

**用途:** 1ユーザーあたりのレコード数が2つ以上になるテーブル

**継承するテーブル例:**
- `usr_unit` - ユーザー所持ユニット
- `usr_item` - ユーザー所持アイテム
- `usr_exchange_lineup` - 交換所ラインナップ
- `usr_pvp` - PVPシーズン情報

**特徴:**
- `cachedGetAll()`, `cachedGetMany()`, `cachedGetOneWhere()` メソッドを使用
- 条件に応じてDBアクセスを最適化
- expectedCountで期待取得数を指定可能

## キャッシュ機構の重要ポイント

### 1. 本人のデータのみキャッシュ

```php
// 他人のデータを取得する場合は、毎回DBから取得する
if ($this->isOwnUsrUserId($usrUserId) === false) {
    return $dbCallback();
}
```

### 2. クローンして返却

```php
// キャッシュのモデルを直接操作しないように、クローンして参照を切る
public function getCache(bool $isClone = true): array
{
    if ($isClone) {
        return $this->usrModelManager->getClonedModels($this::class);
    }
    return $this->usrModelManager->getModels($this::class);
}
```

### 3. 変更追跡の仕組み

```php
// syncModels時に変更があれば、DB更新フラグを立てる
if ($targetModel->isChanged()) {
    $this->needSaves[$repositoryClass] = true;
    $this->addChangedModelKey($repositoryClass, $modelKey);
}
```

### 4. DB更新のタイミング

```php
// UseCaseのapplyUserTransactionChanges内で一括更新
protected function applyUserTransactionChanges(callable $callback): mixed
{
    return DB::transaction(function () use ($callback) {
        $result = $callback();
        app(UsrModelManager::class)->saveAll();  // ここで一括更新
        return $result;
    });
}
```

## saveModelsの役割

各Repositoryで実装するsaveModelsメソッドは、UsrModelManagerから呼び出される一括更新ロジックです。

```php
protected function saveModels(Collection $models): void
{
    $upsertValues = $models->map(function (UsrExchangeLineup $model) {
        return [
            'id' => $model->getId(),
            'usr_user_id' => $model->getUsrUserId(),
            'mst_exchange_lineup_id' => $model->getMstExchangeLineupId(),
            // ... 全カラムを記述
        ];
    })->toArray();

    UsrExchangeLineup::query()->upsert(
        $upsertValues,
        ['usr_user_id', 'mst_exchange_lineup_id'],  // ユニークキー
        ['trade_count', 'reset_at'],  // 更新対象カラム
    );
}
```

**重要:** 新規列を追加した場合は、必ずsaveModelsにも追加する必要があります。

## 関連クラス

### UsrModelDiffGetService

APIリクエスト中に変更があったユーザーデータのみを取得するサービス。

```php
// UseCase内で使用
return new StageEndResultData(
    $this->usrModelDiffGetService->getChangedUsrUnits(),
    $this->usrModelDiffGetService->getChangedUsrItems(),
);
```

### UseCaseTrait

トランザクション処理を提供するTrait。

```php
trait UseCaseTrait
{
    protected function applyUserTransactionChanges(callable $callback): mixed
    {
        return DB::transaction(function () use ($callback) {
            $result = $callback();
            app(UsrModelManager::class)->saveAll();
            return $result;
        });
    }
}
```
