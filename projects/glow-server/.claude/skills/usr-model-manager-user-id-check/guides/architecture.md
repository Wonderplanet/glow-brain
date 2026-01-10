# UsrModelManager Architecture

UsrModelManagerの仕組みとuser id checkの重要性を解説します。

## 目次

- [UsrModelManagerとは](#usrmodelmanagerとは)
- [User ID Checkの目的](#user-id-checkの目的)
- [リクエストライフサイクル](#リクエストライフサイクル)
- [キャッシュ機構の仕組み](#キャッシュ機構の仕組み)
- [User ID Checkが行われるタイミング](#user-id-checkが行われるタイミング)

## UsrModelManagerとは

UsrModelManagerは、1リクエスト中のユーザーデータモデルのキャッシュを管理するクラスです。

### 基本的な特徴

```php
// api/app/Infrastructure/UsrModelManager.php
class UsrModelManager
{
    /**
     * apiリクエストを送ったユーザーのID。
     * このIDを元に、本人のユーザーデータのみを扱うことを保証する。
     */
    private string $usrUserId = '';

    /**
     * ユーザーデータモデルのキャッシュを格納する。
     * 1リクエスト中のsingletonインスタンスとして生成される。
     *
     * key: repository class name string
     * value: array<string, UsrModelInterface>
     */
    private array $models = [];
}
```

参照: `api/app/Infrastructure/UsrModelManager.php:16-37`

### Singletonとしての動作

- AppServiceProviderで登録され、1リクエストにつき1インスタンスのみ存在
- リクエスト開始時に生成され、リクエスト終了時に破棄される
- 複数のRepository間でキャッシュを共有できる

## User ID Checkの目的

### セキュリティ: 他人のデータへのアクセスを防ぐ

UsrModelManagerは、**APIリクエストを送ったユーザー本人のデータのみ**を扱うことを保証します。

```php
// 想定される攻撃シナリオ
// ユーザーAが、ユーザーBのデータを取得しようとする

// ❌ これを防ぐ
$userBModel = UsrItem::factory()->make([
    'usr_user_id' => 'user-B-id',
    'mst_item_id' => '1',
]);

// UsrModelManager::getUsrUserId() = 'user-A-id'
// エラー: user id check fails
$this->usrItemRepository->syncModel($userBModel);
```

### データ整合性: キャッシュの混入を防ぐ

異なるユーザーのデータがキャッシュに混入すると、データ破損やバグの原因になります。

```php
// ❌ キャッシュに異なるユーザーのデータが混入する危険性

// ユーザーAのリクエスト中
$userAModel = UsrItem::factory()->make(['usr_user_id' => 'user-A-id']);
$this->usrItemRepository->syncModel($userAModel);

// もし、ここでユーザーBのデータを追加できてしまうと...
$userBModel = UsrItem::factory()->make(['usr_user_id' => 'user-B-id']);
$this->usrItemRepository->syncModel($userBModel); // これを防ぐ

// キャッシュに2人のデータが混在してしまう
```

## リクエストライフサイクル

### 1. リクエスト開始

認証ミドルウェアが、認証済みユーザーのIDをUsrModelManagerに設定します。

```php
// api/app/Http/Kernel.php の設定により、認証ミドルウェアが動作
// 自動的に UsrModelManager::setUsrUserId() が呼ばれる
```

### 2. リクエスト処理中

Repository経由でデータを取得・更新する際、UsrModelManagerのキャッシュを使用します。

```php
// Controller -> UseCase -> Repository の順に呼ばれる

// UseCase
public function exec(string $userId): void
{
    // Repository経由でデータ取得
    // UsrModelManagerのキャッシュが使われる
    $user = $this->usrUserRepository->findById($userId);
}
```

### 3. リクエスト終了

UsrModelManagerのインスタンスが破棄され、キャッシュも消えます。

```
Request Start
    ↓
[Authentication Middleware]
    ↓ setUsrUserId('authenticated-user-id')
[UsrModelManager] usrUserId = 'authenticated-user-id'
    ↓
[Controller]
    ↓
[UseCase]
    ↓
[Repository] ← UsrModelManager (キャッシュ)
    ↓
[DB] (必要な場合のみ)
    ↓
Response
    ↓
Request End (UsrModelManager破棄)
```

## キャッシュ機構の仕組み

### Repository階層構造

```
UsrModelCacheRepository (abstract)
    ├─ isValidModel() ← User ID Checkを実行
    ├─ syncModels() ← キャッシュに追加
    └─ getCache() ← キャッシュから取得
         ↓
    UsrModelSingleCacheRepository (1ユーザー1レコード)
         ├─ cachedGetOne()
         └─ 例: UsrUserRepository
         ↓
    UsrModelMultiCacheRepository (1ユーザー複数レコード)
         ├─ cachedGetAll()
         ├─ cachedGetMany()
         ├─ cachedGetOneWhere()
         └─ 例: UsrItemRepository, UsrUnitRepository
```

### キャッシュの追加フロー

```php
// 1. RepositoryがsyncModel()を呼ぶ
$this->usrItemRepository->syncModel($model);

// 2. UsrModelCacheRepository::syncModels()が呼ばれる
public function syncModels(Collection $models): void
{
    foreach ($models as $model) {
        // 3. User ID Checkを実行
        if ($this->isValidModel($model)) {
            $targetModels[] = $model;
        }
    }

    // 4. UsrModelManagerにキャッシュを追加
    $this->usrModelManager->syncModels($this::class, $targetModels);
}

// 5. UsrModelManager::syncModels()がキャッシュに追加
public function syncModels(string $repositoryClass, array $targetModels): void
{
    // キャッシュに追加
    $this->models[$repositoryClass] = $models;
}
```

参照: `api/app/Domain/Resource/Usr/Repositories/UsrModelCacheRepository.php:86-101`

### キャッシュの取得フロー

```php
// 1. RepositoryがcachedGetOne()を呼ぶ
$model = $this->cachedGetOne($usrUserId);

// 2. キャッシュを確認
$model = $this->getFirstCache();
if ($model !== null) {
    return $model; // キャッシュヒット
}

// 3. キャッシュミス→DBから取得
$model = $this->dbSelectOne($usrUserId);

// 4. 取得したモデルをキャッシュに追加
$this->syncModel($model);

// 5. キャッシュから取得して返す
return $this->getFirstCache();
```

参照: `api/app/Domain/Resource/Usr/Repositories/UsrModelSingleCacheRepository.php:29-65`

## User ID Checkが行われるタイミング

### 1. syncModels()実行時

`UsrModelCacheRepository::syncModels()`内で、`isValidModel()`が呼ばれます。

```php
// api/app/Domain/Resource/Usr/Repositories/UsrModelCacheRepository.php:86-101
public function syncModels(Collection $models): void
{
    $targetModels = [];
    foreach ($models as $model) {
        // ここでUser ID Checkが実行される
        if ($this->isValidModel($model)) {
            $targetModels[] = $model;
        }
    }

    $this->usrModelManager->syncModels($this::class, $targetModels);
}
```

### 2. isValidModel()の実装

```php
// api/app/Domain/Resource/Usr/Repositories/UsrModelCacheRepository.php:31-48
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

### 3. isOwnUsrUserId()の実装

```php
// api/app/Domain/Resource/Usr/Repositories/UsrModelCacheRepository.php:218-221
public function isOwnUsrUserId(string $usrUserId): bool
{
    return $usrUserId === $this->usrModelManager->getUsrUserId();
}
```

### User ID Checkの判定ロジック

```
モデルのusr_user_id === UsrModelManager::getUsrUserId()
    ↓
  true  → キャッシュに追加
    ↓
  false → GameException (INVALID_PARAMETER)
```

## 他人のデータを扱う特殊ケース

### 原則: 他人のデータはキャッシュを使わない

他人のデータを取得する必要がある場合、キャッシュを経由せずに直接DBから取得します。

```php
// 例: SignUpUseCaseで直近の登録ユーザーを確認
// api/app/Domain/User/Repositories/UsrUserRepository.php:86-91
public function findRecentlyCreatedAtByClientUuid(string $clientUuid): ?UsrUserInterface
{
    // キャッシュを使わず、直接DBから取得
    return UsrUser::where('client_uuid', $clientUuid)
        ->orderBy('created_at', 'desc')
        ->first();
}
```

### cachedGetOne/cachedGetAllの他人データ対応

Repository基底クラスは、他人のデータを取得する場合、自動的にキャッシュをスキップします。

```php
// api/app/Domain/Resource/Usr/Repositories/UsrModelSingleCacheRepository.php:29-38
protected function cachedGetOne(string $usrUserId): mixed
{
    // 他人のデータを取得する場合は、毎回DBから取得する。
    if ($this->isOwnUsrUserId($usrUserId) === false) {
        return $this->dbSelectOne($usrUserId);
    }

    // 本人のデータの場合は、キャッシュを使う
    $model = $this->getFirstCache();
    // ...
}
```

参照: `api/app/Domain/Resource/Usr/Repositories/UsrModelSingleCacheRepository.php:29-65`

## まとめ

### UsrModelManagerの役割

1. **セキュリティ**: 他人のデータへのアクセスを防ぐ
2. **パフォーマンス**: 1リクエスト中のDBアクセスを削減
3. **データ整合性**: キャッシュの混入を防ぐ

### User ID Checkの重要性

- APIリクエストを送ったユーザー本人のデータのみを扱うことを保証
- `syncModels()`実行時に、自動的にチェックされる
- エラーになった場合は、user idの不一致が原因

### 開発時の注意点

- SignUpUseCase以外では、`setUsrUserId()`を呼ばない
- モデル生成時は、認証済みユーザーのIDを使う
- 他人のデータが必要な場合は、キャッシュを使わない
