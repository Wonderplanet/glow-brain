# キャッシュ同期パターン

## syncModel / syncModelsの概要

キャッシュへモデルを追加または上書きするメソッドです。

**主な用途:**
- 新規作成したモデルをキャッシュに追加
- 更新したモデルをキャッシュに反映
- DBから取得したモデルをキャッシュに保存

## メソッド一覧

| メソッド | 引数 | 用途 |
|---------|------|------|
| `syncModel()` | 1つのモデル | 単一モデルをキャッシュに追加・更新 |
| `syncModels()` | Collection<モデル> | 複数モデルをキャッシュに一括追加・更新 |

## syncModel - 単一モデルの同期

### メソッドシグネチャ

```php
public function syncModel(UsrModelInterface $model): void
```

### 実装パターン

#### パターン1: 新規作成時

```php
public function create(string $usrUserId, CarbonImmutable $now): UsrUserLoginInterface
{
    $model = new UsrUserLogin();
    $model->usr_user_id = $usrUserId;
    $model->first_login_at = null;
    $model->last_login_at = null;
    $model->login_count = 0;

    // キャッシュに追加
    $this->syncModel($model);

    return $model;
}
```

#### パターン2: 更新時

```php
public function updateHourlyAccessedAt(string $usrUserId, string $hourlyAccessedAt): UsrUserLoginInterface
{
    $model = $this->get($usrUserId);
    $model->setHourlyAccessedAt($hourlyAccessedAt);

    // 更新後のモデルをキャッシュに反映
    $this->syncModel($model);

    return $model;
}
```

#### パターン3: BNID連携

```php
public function linkBnid(string $usrUserId, string $bnUserId): void
{
    $usrUser = $this->findById($usrUserId);
    $usrUser->setBnUserId($bnUserId);

    // BNID連携情報をキャッシュに反映
    $this->syncModel($usrUser);
}
```

## syncModels - 複数モデルの同期

### メソッドシグネチャ

```php
public function syncModels(Collection $models): void
```

### 実装パターン

#### パターン1: DBから取得したモデルをキャッシュに追加

```php
// cachedGetManyメソッド内で自動的に実行される
$models = $dbCallback();

// DBから取得したデータをキャッシュに追加する
$this->syncModels($models);
```

#### パターン2: 複数モデルを一括作成

```php
public function createMultiple(string $usrUserId, Collection $mstItemIds): Collection
{
    $models = $mstItemIds->map(function ($mstItemId) use ($usrUserId) {
        $model = new UsrItem();
        $model->usr_user_id = $usrUserId;
        $model->mst_item_id = $mstItemId;
        $model->count = 1;
        return $model;
    });

    // 複数モデルを一括でキャッシュに追加
    $this->syncModels($models);

    return $models;
}
```

## キャッシュ同期の仕組み

### 1. クローンして保存

```php
// 参照渡しされているモデルをキャッシュに追加すると、
// ビジネスロジックでの変更が伝播してしまうので、クローンして参照を切る
$targetModel = clone $targetModel;
```

### 2. 変更追跡

```php
if ($targetModel->isChanged()) {
    // 変更がある場合は、DB更新が必要なため、DB更新フラグを立てる
    $this->needSaves[$repositoryClass] = true;

    // apiリクエスト前と比較して、変更があったモデルのキーを管理する
    $this->addChangedModelKey($repositoryClass, $modelKey);
}
```

### 3. 上書きルール

```php
// 既にキャッシュ済みのモデルなら、上書きしようとしているモデルに変更がある場合のみ上書きする
if ($isExists && $targetModel->isChanged() === false) {
    continue;  // 変更がなければスキップ
}
```

## 使用タイミング

### syncModelを使うケース

- ✅ 新規モデルを1つ作成した直後
- ✅ モデルの値を1つ更新した直後
- ✅ キャッシュに反映したい変更が明確

### syncModelsを使うケース

- ✅ DBから複数モデルを取得した直後（cachedGetMany内部で自動実行）
- ✅ 複数モデルを一括作成した直後
- ✅ 複数モデルをまとめてキャッシュに追加したい

## DB即時保存との組み合わせ

通常、モデルの変更はトランザクション終了時に一括保存されますが、特定のケースでは即時保存が必要です。

### 即時保存が必要なケース

```php
public function updateHourlyAccessedAtWithSave(string $usrUserId, string $hourlyAccessedAt): UsrUserLoginInterface
{
    // モデルを更新してキャッシュに反映
    $model = $this->updateHourlyAccessedAt($usrUserId, $hourlyAccessedAt);

    // DB即時保存が必要な場合（BankF001の判定等）
    $models = collect([$model]);
    $this->saveModels($models);

    return $model;
}
```

**注意:**
- 即時保存は例外的なケースのみ使用
- 通常はトランザクション終了時に一括保存される

## キャッシュ取得時の動作

### getCacheFilteredByModelKey

DBから取得したモデルをキャッシュから取得し直すことで、値のデグレを防ぎます。

```php
// DBから取得したデータをキャッシュに追加する
$this->syncModels($models);

// 最新の変更を反映するために、キャッシュから取得する
// DBから取得したものをそのまま返すと、もしキャッシュすでにある場合に、値がデグレする可能性があるため。
return $this->getCacheFilteredByModelKey($models);
```

**理由:**
- DBから取得したモデルをそのまま返すと、キャッシュに既に存在する場合に値がデグレする
- キャッシュから取得することで、最新の変更（他の処理での更新）を反映

## 変更モデルの取得

### getChangedModels

APIリクエスト実行前と比較して、変更があったモデルのみを取得します。

```php
public function getChangedModels(): Collection
{
    return $this->usrModelManager->getChangedModels($this::class);
}
```

**使用例:**
```php
// UseCase内で使用
$changedUsrUnits = $this->usrUnitRepository->getChangedModels();
```

**UsrModelDiffGetServiceとの連携:**
```php
class UsrModelDiffGetService
{
    public function getChangedUsrUnits(): Collection
    {
        return $this->usrUnitRepository->getChangedModels();
    }
}
```

## よくあるパターン

### パターン1: create → syncModel

```php
public function create(string $usrUserId, string $mstArtworkId): UsrArtworkInterface
{
    $model = $this->make($usrUserId, $mstArtworkId);
    $this->syncModel($model);  // キャッシュに追加
    return $model;
}
```

### パターン2: update → syncModel

```php
public function updateLevel(string $id, int $level): UsrUnitInterface
{
    $model = $this->getById($id);
    $model->setLevel($level);
    $this->syncModel($model);  // 更新をキャッシュに反映
    return $model;
}
```

### パターン3: DB取得 → syncModels → getCacheFilteredByModelKey

```php
protected function cachedGetMany(...): Collection
{
    // DBから取得
    $models = $dbCallback();

    // キャッシュに追加
    $this->syncModels($models);

    // キャッシュから取得し直す（値のデグレを防ぐ）
    return $this->getCacheFilteredByModelKey($models);
}
```

## まとめ

- **syncModel**: 単一モデルをキャッシュに追加・更新
- **syncModels**: 複数モデルをキャッシュに一括追加・更新
- **変更追跡**: isDirtyなモデルを自動で追跡
- **クローン**: 参照渡しを防ぐため、クローンして保存
- **値のデグレ防止**: キャッシュから取得し直すことで、最新の変更を反映
