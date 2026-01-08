# Repository層での修正例

Repository層でのuser id checkエラーの修正方法を解説します。

## 目次

- [Repository基底クラスの理解](#repository基底クラスの理解)
- [syncModel/syncModelsの正しい使い方](#syncmodelsyncmodelsの正しい使い方)
- [他人のデータを取得する場合](#他人のデータを取得する場合)
- [cachedGetOne/cachedGetAllの使い方](#cachedgetone-cachedgetallの使い方)
- [実際のコード例](#実際のコード例)

## Repository基底クラスの理解

### Repository階層構造

```
UsrModelCacheRepository (abstract)
    ├─ syncModels() - キャッシュに追加（user id checkあり）
    ├─ isValidModel() - user id checkを実行
    └─ isOwnUsrUserId() - user id比較
         ↓
    UsrModelSingleCacheRepository
         ├─ cachedGetOne() - 1レコード取得
         └─ 例: UsrUserRepository, UsrUserLoginRepository
         ↓
    UsrModelMultiCacheRepository
         ├─ cachedGetAll() - 全レコード取得
         ├─ cachedGetMany() - 複数レコード取得
         ├─ cachedGetOneWhere() - 条件付き1レコード取得
         └─ 例: UsrItemRepository, UsrUnitRepository
```

### 継承関係

```php
// 1ユーザー1レコード
class UsrUserRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUser::class;
}

// 1ユーザー複数レコード
class UsrUnitRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrUnit::class;
}
```

## syncModel/syncModelsの正しい使い方

### 基本的な使い方

```php
// 1つのモデルをキャッシュに追加
$model = UsrItem::factory()->make([
    'usr_user_id' => $usrUserId,
    'mst_item_id' => '1',
]);
$this->syncModel($model);

// 複数のモデルをキャッシュに追加
$models = collect([
    UsrItem::factory()->make(['usr_user_id' => $usrUserId, 'mst_item_id' => '1']),
    UsrItem::factory()->make(['usr_user_id' => $usrUserId, 'mst_item_id' => '2']),
]);
$this->syncModels($models);
```

### syncModelの実装（UsrModelCacheRepository）

```php
// syncModel()は、syncModels()のラッパー
public function syncModel(UsrModelInterface $model): void
{
    $this->syncModels(collect([$model]));
}
```

### syncModelsの実装（UsrModelCacheRepository）

```php
// api/app/Domain/Resource/Usr/Repositories/UsrModelCacheRepository.php:86-101
public function syncModels(Collection $models): void
{
    if ($models->isEmpty()) {
        return;
    }

    // 担当するモデルのインスタンスでなければキャッシュに追加させない
    $targetModels = [];
    foreach ($models as $model) {
        // ここでuser id checkが実行される
        if ($this->isValidModel($model)) {
            $targetModels[] = $model;
        }
    }

    $this->usrModelManager->syncModels($this::class, $targetModels);
}
```

参照: `api/app/Domain/Resource/Usr/Repositories/UsrModelCacheRepository.php:86-101`

### user id checkのタイミング

```
syncModel($model)
    ↓
syncModels(collect([$model]))
    ↓
isValidModel($model) ← ここでuser id check
    ↓
$this->isOwnUsrUserId($model->getUsrUserId())
    ↓
$model->getUsrUserId() === $this->usrModelManager->getUsrUserId()
```

## 他人のデータを取得する場合

### 原則: キャッシュを使わない

他人のデータを取得する場合は、キャッシュを経由せず、直接DBから取得します。

### 実装例: UsrUserRepository

```php
// api/app/Domain/User/Repositories/UsrUserRepository.php:78-91
/**
 * 直近に指定client_uuidで作成されたユーザーを取得する
 *
 * APIリクエストしたユーザーとは別ユーザーのデータを取得するケースがあるので、
 * ユーザーキャッシュを介さずに、DBから直接取得する
 *
 * @param string $clientUuid
 * @return UsrUserInterface|null
 */
public function findRecentlyCreatedAtByClientUuid(string $clientUuid): ?UsrUserInterface
{
    return UsrUser::where('client_uuid', $clientUuid)
        ->orderBy('created_at', 'desc')
        ->first();
}
```

参照: `api/app/Domain/User/Repositories/UsrUserRepository.php:78-91`

### ポイント

- メソッド名に`cache`を含めない
- Eloquentの`query()`や`where()`を直接使う
- `syncModel()`や`syncModels()`を呼ばない
- コメントで理由を明記する

### 他人データ取得の判定ロジック（参考）

基底クラスの`cachedGetOne()`は、自動的に他人データを判定します。

```php
// api/app/Domain/Resource/Usr/Repositories/UsrModelSingleCacheRepository.php:29-38
protected function cachedGetOne(string $usrUserId): mixed
{
    $dbCallback = function () use ($usrUserId) {
        return $this->dbSelectOne($usrUserId);
    };

    // 他人のデータを取得する場合は、毎回DBから取得する。
    if ($this->isOwnUsrUserId($usrUserId) === false) {
        return $dbCallback();
    }

    // ...キャッシュを使う処理...
}
```

参照: `api/app/Domain/Resource/Usr/Repositories/UsrModelSingleCacheRepository.php:29-38`

## cachedGetOne/cachedGetAllの使い方

### cachedGetOne（単一レコード取得）

UsrModelSingleCacheRepositoryで使用します。

```php
// api/app/Domain/User/Repositories/UsrUserRepository.php:33-41
/**
 * @api
 */
public function findById(string $userId): UsrUserInterface
{
    $user = $this->cachedGetOne($userId);
    if ($user === null) {
        throw new GameException(ErrorCode::USER_NOT_FOUND);
    }

    return $user;
}
```

参照: `api/app/Domain/User/Repositories/UsrUserRepository.php:33-41`

### cachedGetAll（全レコード取得）

UsrModelMultiCacheRepositoryで使用します。

```php
// api/app/Domain/Unit/Repositories/UsrUnitRepository.php:178-184
/**
 * @api
 * @return Collection<UsrUnitInterface>
 */
public function getListByUsrUserId(string $usrUserId): Collection
{
    return $this->cachedGetAll($usrUserId);
}
```

参照: `api/app/Domain/Unit/Repositories/UsrUnitRepository.php:178-184`

### cachedGetOneWhere（条件付き単一レコード取得）

UsrModelMultiCacheRepositoryで使用します。

```php
// api/app/Domain/Unit/Repositories/UsrUnitRepository.php:77-96
public function getByMstUnitId(string $usrUserId, string $mstUnitId): ?UsrUnitInterface
{
    return $this->cachedGetOneWhere(
        $usrUserId,
        'mst_unit_id',
        $mstUnitId,
        function () use ($usrUserId, $mstUnitId) {
            $record = UsrUnit::query()
                ->where('usr_user_id', $usrUserId)
                ->where('mst_unit_id', $mstUnitId)
                ->first();

            if ($record === null) {
                return null;
            }

            return UsrUnit::createFromRecord($record);
        },
    );
}
```

参照: `api/app/Domain/Unit/Repositories/UsrUnitRepository.php:77-96`

### cachedGetMany（複数レコード取得）

UsrModelMultiCacheRepositoryで使用します。

```php
// api/app/Domain/Unit/Repositories/UsrUnitRepository.php:101-132
/**
 * @return Collection<UsrUnitInterface>
 */
public function getByIds(string $usrUserId, Collection $ids): Collection
{
    $targetIds = array_fill_keys($ids->all(), true);

    return $this->cachedGetMany(
        $usrUserId,
        cacheCallback: function (Collection $cache) use ($targetIds) {
            return $cache->filter(function (UsrUnitInterface $model) use ($targetIds) {
                return isset($targetIds[$model->getId()]);
            });
        },
        expectedCount: count($targetIds),
        dbCallback: function () use ($usrUserId, $targetIds) {
            $models = UsrUnit::query()
                ->whereIn('id', array_keys($targetIds))
                ->get()
                ->map(function ($record) {
                    return UsrUnit::createFromRecord($record);
                });

            $targetModels = [];
            foreach ($models as $model) {
                // 想定しない他人のデータは、データがなかったとみなす
                if ($model->getUsrUserId() === $usrUserId) {
                    $targetModels[] = $model;
                }
            }

            return collect($targetModels);
        },
    );
}
```

参照: `api/app/Domain/Unit/Repositories/UsrUnitRepository.php:101-132`

## 実際のコード例

### 例1: UsrUserRepository（SingleCache）

```php
// api/app/Domain/User/Repositories/UsrUserRepository.php
class UsrUserRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUser::class;

    /**
     * 他のユーザーテーブルとは違い、ユーザーIDの列名がusr_user_idではなくidであるため、
     * 直接DBから取得するメソッドをオーバーライドする
     */
    protected function dbSelectOne(string $usrUserId): ?UsrUserInterface
    {
        return UsrUser::query()->where('id', $usrUserId)->first();
    }

    /**
     * @api
     */
    public function findById(string $userId): UsrUserInterface
    {
        // cachedGetOne()を使う
        $user = $this->cachedGetOne($userId);
        if ($user === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * モデルインスタンスの生成のみを実行する
     */
    public function make(CarbonImmutable $now, ?string $clientUuid = null): UsrUserInterface
    {
        $usrUser = new UsrUser();
        // ...プロパティ設定...

        return $usrUser;
    }

    /**
     * BNID連携情報を設定する
     */
    public function linkBnid(string $usrUserId, string $bnUserId): void
    {
        $usrUser = $this->findById($usrUserId);
        $usrUser->setBnUserId($bnUserId);

        // syncModel()でキャッシュに追加
        $this->syncModel($usrUser);
    }

    /**
     * 直近に指定client_uuidで作成されたユーザーを取得する
     *
     * APIリクエストしたユーザーとは別ユーザーのデータを取得するケースがあるので、
     * ユーザーキャッシュを介さずに、DBから直接取得する
     */
    public function findRecentlyCreatedAtByClientUuid(string $clientUuid): ?UsrUserInterface
    {
        // キャッシュを使わず、直接DB取得
        return UsrUser::where('client_uuid', $clientUuid)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
```

参照: `api/app/Domain/User/Repositories/UsrUserRepository.php`

### 例2: UsrUnitRepository（MultiCache）

```php
// api/app/Domain/Unit/Repositories/UsrUnitRepository.php
class UsrUnitRepository extends UsrModelMultiCacheRawRepository
{
    protected string $modelClass = UsrUnit::class;

    public function getById(string $id, string $usrUserId): UsrUnitInterface
    {
        // cachedGetOneWhere()を使う
        $model = $this->cachedGetOneWhere(
            $usrUserId,
            'id',
            $id,
            function () use ($id, $usrUserId) {
                $record = UsrUnit::query()
                    ->where('id', $id)
                    ->first();

                if ($record === null) {
                    return null;
                }

                $model = UsrUnit::createFromRecord($record);

                // 想定しない他人のデータなら、データがなかったとみなす
                if ($model->getUsrUserId() !== $usrUserId) {
                    return null;
                }

                return $model;
            },
        );

        if ($model === null) {
            throw new GameException(
                ErrorCode::UNIT_NOT_FOUND,
                "usr_unit record is not found. (usr_user_id: $usrUserId, usr_unit_id: $id)"
            );
        }

        return $model;
    }

    public function getByMstUnitId(string $usrUserId, string $mstUnitId): ?UsrUnitInterface
    {
        // cachedGetOneWhere()を使う
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_unit_id',
            $mstUnitId,
            function () use ($usrUserId, $mstUnitId) {
                $record = UsrUnit::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_unit_id', $mstUnitId)
                    ->first();

                if ($record === null) {
                    return null;
                }

                return UsrUnit::createFromRecord($record);
            },
        );
    }

    /**
     * @api
     */
    public function getListByUsrUserId(string $usrUserId): Collection
    {
        // cachedGetAll()を使う
        return $this->cachedGetAll($usrUserId);
    }

    public function create(string $usrUserId, string $mstUnitId): UsrUnitInterface
    {
        $usrUnit = UsrUnit::create(
            usrUserId: $usrUserId,
            mstUnitId: $mstUnitId,
        );

        // syncModel()でキャッシュに追加
        $this->syncModel($usrUnit);

        return $usrUnit;
    }
}
```

参照: `api/app/Domain/Unit/Repositories/UsrUnitRepository.php`

## よくある間違い

### 間違い1: 他人のデータをsyncModelに渡す

❌ **間違い:**

```php
// 他人のデータを取得
$otherUser = UsrUser::where('client_uuid', $clientUuid)->first();

// エラー: 他人のデータをキャッシュに追加しようとしている
$this->syncModel($otherUser);
```

✅ **正しい:**

```php
// 他人のデータは、syncModelを呼ばない
$otherUser = UsrUser::where('client_uuid', $clientUuid)->first();

// そのまま使う（キャッシュに追加しない）
return $otherUser;
```

### 間違い2: user idを確認せずにsyncModel

❌ **間違い:**

```php
public function updateUser(string $targetUserId): void
{
    // targetUserIdが認証済みユーザーと異なる可能性がある
    $user = UsrUser::where('id', $targetUserId)->first();

    // エラー: user id checkに失敗する可能性
    $this->syncModel($user);
}
```

✅ **正しい:**

```php
public function updateUser(string $usrUserId): void
{
    // 認証済みユーザーのデータのみを取得
    $user = $this->cachedGetOne($usrUserId);

    // user id checkは自動的に行われる
    $this->syncModel($user);
}
```

### 間違い3: dbSelectOneをオーバーライドせずにカラム名が異なる

❌ **間違い:**

```php
// UsrUserテーブルは、usr_user_idではなくidを使う

class UsrUserRepository extends UsrModelSingleCacheRepository
{
    // dbSelectOne()をオーバーライドしていない
    // デフォルトではwhere('usr_user_id', $usrUserId)が実行される
}
```

✅ **正しい:**

```php
// api/app/Domain/User/Repositories/UsrUserRepository.php:24-27
class UsrUserRepository extends UsrModelSingleCacheRepository
{
    protected function dbSelectOne(string $usrUserId): ?UsrUserInterface
    {
        return UsrUser::query()->where('id', $usrUserId)->first();
    }
}
```

## チェックリスト

Repository実装時の確認項目：

- [ ] 正しい基底クラスを継承しているか（Single/Multi）
- [ ] `syncModel()`を呼ぶ前に、user idが正しいか確認したか
- [ ] 他人のデータを取得する場合、キャッシュを使わない実装にしたか
- [ ] `cachedGetOne`/`cachedGetAll`/`cachedGetOneWhere`を正しく使っているか
- [ ] カラム名が`usr_user_id`でない場合、`dbSelectOne()`をオーバーライドしたか

## 関連ドキュメント

- **[../error-patterns.md](../error-patterns.md#エラーパターン3-他人のモデルをsyncmodelsに渡す)** - 他人データのエラーパターン
- **[../guides/architecture.md](../guides/architecture.md#キャッシュ機構の仕組み)** - キャッシュ機構の詳細
- **[fix-service.md](fix-service.md)** - Service/UseCase層での修正例
