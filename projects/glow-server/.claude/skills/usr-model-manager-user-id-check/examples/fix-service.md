# Service/UseCase層での修正例

Service/UseCase層でのuser id checkエラーの修正方法を解説します。

## 目次

- [UseCase/Service/Delegatorの役割](#usecaseservicedelegatorの役割)
- [user id取得のタイミング](#user-id取得のタイミング)
- [モデル生成時の注意点](#モデル生成時の注意点)
- [実際のコード例](#実際のコード例)
- [よくある間違い](#よくある間違い)

## UseCase/Service/Delegatorの役割

### 層の責務

```
Controller
    ↓ リクエスト転送
UseCase (ビジネスロジックの実行)
    ↓ 機能単位で呼び出し
Service/Delegator (機能の実装)
    ↓ データ操作
Repository (データ永続化)
    ↓ user id check
UsrModelManager (キャッシュ管理)
```

### user id checkが行われる場所

- **Repository層**: `syncModel()`/`syncModels()`実行時
- **UseCase/Service層**: モデル生成時のuser id指定

### UseCase/Service層の責務

- 認証済みユーザーのuser idを使用
- モデル生成時に正しいuser idを渡す
- 他人のデータを扱う場合は、Repositoryの専用メソッドを使う

## user id取得のタイミング

### パターン1: UseCaseの引数から取得

最も一般的なパターンです。

```php
public function exec(string $userId): void
{
    // 引数で受け取ったuser idを使う
    $user = $this->usrUserRepository->findById($userId);

    // ...処理...
}
```

### パターン2: UserモデルのgetUsrUserId()から取得

Userモデルを先に取得してから、そのuser idを使います。

```php
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    // Userモデルからuser idを取得
    $usrUserId = $user->getUsrUserId();

    // 他のデータを操作
    $this->itemService->addItem($usrUserId, 'item-id-1');
}
```

### パターン3: モデルから直接取得

既に取得したモデルから、user idを取得します。

```php
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    // getId()とgetUsrUserId()は異なる
    // UsrUserの場合、getId()が正しい
    $usrUserId = $user->getId();

    // ...処理...
}
```

### 注意: UsrUserの特殊性

```php
// UsrUserテーブルは、user idのカラム名が「id」
// 他のテーブルは「usr_user_id」

// ✅ UsrUser
$usrUserId = $user->getId(); // 正しい

// ✅ 他のテーブル（UsrItem, UsrUnit等）
$usrUserId = $model->getUsrUserId(); // 正しい
```

## モデル生成時の注意点

### 基本原則

**モデルのuser idは、UsrModelManager::getUsrUserId()と一致させる**

### パターンA: 認証済みユーザーのデータを作成

```php
// ✅ 正しい
public function addItem(string $usrUserId, string $itemId): void
{
    // UsrModelManager::getUsrUserId() === $usrUserId

    $model = UsrItem::factory()->make([
        'usr_user_id' => $usrUserId, // 認証済みユーザーのid
        'mst_item_id' => $itemId,
    ]);

    $this->usrItemRepository->syncModel($model);
}
```

### パターンB: Userモデルから取得したuser idを使う

```php
// ✅ 正しい
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    $model = UsrItem::factory()->make([
        'usr_user_id' => $user->getId(), // Userモデルから取得
        'mst_item_id' => 'item-id-1',
    ]);

    $this->usrItemRepository->syncModel($model);
}
```

### パターンC: Repository経由でモデルを作成

Repositoryに`create()`や`make()`メソッドがある場合、それを使います。

```php
// ✅ 正しい: Repository::create()を使う
public function exec(string $userId): void
{
    $usrUnit = $this->usrUnitRepository->create($userId, 'unit-id-1');

    // Repositoryのcreate()内でsyncModel()が呼ばれる
}
```

参照: `api/app/Domain/Unit/Repositories/UsrUnitRepository.php:186-196`

## 実際のコード例

### 例1: PartySaveUseCase

```php
// api/app/Domain/Party/UseCases/PartySaveUseCase.php:26-43
public function exec(UsrUser $user, Collection $mstUnitIds): void
{
    // Userモデルからuser idを取得
    $usrUserId = $user->getUsrUserId();

    /** @var Collection<string> $mstUnitIds */
    $mstUnitIds = $mstUnitIds
        ->filter(fn($mstUnitId) => $this->usrUnitRepository->isCheckUnit($usrUserId, $mstUnitId));

    if ($mstUnitIds->count() !== config('const.party.max_unit_count')) {
        throw new GameException(ErrorCode::INVALID_PARAMETER);
    }

    // user idを使って処理
    $usrParty = $this->usrPartyRepository->getByUsrUserId($usrUserId);
    $usrParty->setMstUnitIds($mstUnitIds->toArray());

    // syncModel()が内部で呼ばれる
    $this->usrPartyRepository->save($usrParty);
}
```

参照: `api/app/Domain/Party/UseCases/PartySaveUseCase.php:26-43`

### 例2: GachaDrawUseCase

```php
// api/app/Domain/Gacha/UseCases/GachaDrawUseCase.php:71-82
// Userモデルからuser idを取得
$usrGacha = $this->gachaService->getUsrGacha($usr->getUsrUserId(), $oprGacha->getId());

// ...処理...

$this->rewardDelegator->addMstRewardsToPool(
    $usr->getUsrUserId(), // user idを渡す
    $platform,
    $now,
    $gachaResult->getGachaRewards(),
    $resourceType = LogResourceType::GACHA,
);
```

参照: `api/app/Domain/Gacha/UseCases/GachaDrawUseCase.php:71-82`

### 例3: StageEndUseCase

```php
// api/app/Domain/Stage/UseCases/StageEndUseCase.php:71
// Userモデルからuser idを取得
$usrUserId = $user->getUsrUserId();

// user idを使って処理
$this->stageService->endStage(
    $usrUserId,
    $mstStageId,
    $battleResult,
    $now,
);
```

参照: `api/app/Domain/Stage/UseCases/StageEndUseCase.php:71`

### 例4: Repository::create()の使用

```php
// api/app/Domain/Unit/Repositories/UsrUnitRepository.php:186-196
public function create(string $usrUserId, string $mstUnitId): UsrUnitInterface
{
    // モデル作成
    $usrUnit = UsrUnit::create(
        usrUserId: $usrUserId,
        mstUnitId: $mstUnitId,
    );

    // キャッシュに追加
    $this->syncModel($usrUnit);

    return $usrUnit;
}
```

参照: `api/app/Domain/Unit/Repositories/UsrUnitRepository.php:186-196`

## よくある間違い

### 間違い1: 引数の取り違え

❌ **間違い:**

```php
public function exec(string $targetUserId): void
{
    // UsrModelManager::getUsrUserId() = 'authenticated-user-id'

    $model = UsrItem::factory()->make([
        'usr_user_id' => $targetUserId, // 認証済みユーザーと異なる可能性
    ]);

    // エラー: user id checkに失敗
    $this->usrItemRepository->syncModel($model);
}
```

✅ **正しい:**

```php
public function exec(string $userId): void
{
    // 認証済みユーザーのuser idを使う
    $user = $this->usrUserRepository->findById($userId);

    $model = UsrItem::factory()->make([
        'usr_user_id' => $user->getId(), // 認証済みユーザーのid
    ]);

    $this->usrItemRepository->syncModel($model);
}
```

### 間違い2: UsrUserでgetUsrUserId()を使う

❌ **間違い:**

```php
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    // UsrUserの場合、getUsrUserId()ではなくgetId()を使う
    $usrUserId = $user->getUsrUserId(); // 間違い

    // ...処理...
}
```

✅ **正しい:**

```php
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    // UsrUserの場合、getId()を使う
    $usrUserId = $user->getId(); // 正しい

    // ...処理...
}
```

**理由:**
- UsrUserテーブルは、user idのカラム名が`id`
- `getUsrUserId()`は存在しない、または別の値を返す

### 間違い3: 変数名の混同

❌ **間違い:**

```php
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    // 別の変数を用意してしまう
    $targetUserId = $this->getTargetUserId(); // 他人のid

    $model = UsrItem::factory()->make([
        'usr_user_id' => $targetUserId, // 間違った変数を使用
    ]);

    // エラー: user id checkに失敗
    $this->usrItemRepository->syncModel($model);
}
```

✅ **正しい:**

```php
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    // 認証済みユーザーのidを使う
    $usrUserId = $user->getId();

    $model = UsrItem::factory()->make([
        'usr_user_id' => $usrUserId, // 正しい変数を使用
    ]);

    $this->usrItemRepository->syncModel($model);
}
```

### 間違い4: 他人のデータを取得してsyncModel

❌ **間違い:**

```php
public function exec(string $userId, string $targetUserId): void
{
    // UsrModelManager::getUsrUserId() = $userId

    // 他人のデータを取得
    $otherUser = $this->usrUserRepository->findById($targetUserId);

    // エラー: 他人のデータをキャッシュに追加しようとしている
    $this->usrUserRepository->syncModel($otherUser);
}
```

✅ **正しい:**

```php
public function exec(string $userId, string $clientUuid): void
{
    // UsrModelManager::getUsrUserId() = $userId

    // 他人のデータは、キャッシュを使わない専用メソッドで取得
    $otherUser = $this->usrUserRepository->findRecentlyCreatedAtByClientUuid($clientUuid);

    // syncModelは呼ばない
    // そのまま使う
}
```

### 間違い5: UsrModelManagerを直接操作

❌ **間違い:**

```php
public function exec(string $userId): void
{
    // setUsrUserIdは、SignUpUseCase以外で使わない
    $this->usrModelManager->setUsrUserId($userId);

    // ...処理...
}
```

✅ **正しい:**

```php
public function exec(string $userId): void
{
    // setUsrUserIdは呼ばない
    // 認証ミドルウェアが自動的に設定する

    // ...処理...
}
```

## 実装パターン集

### パターン1: UseCase → Repository

```php
// UseCase
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    // Repository::create()を使う
    $this->usrItemRepository->create($user->getId(), 'item-id-1');
}

// Repository
public function create(string $usrUserId, string $itemId): UsrItemInterface
{
    $model = UsrItem::factory()->make([
        'usr_user_id' => $usrUserId,
        'mst_item_id' => $itemId,
    ]);

    $this->syncModel($model);

    return $model;
}
```

### パターン2: UseCase → Service → Repository

```php
// UseCase
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    // Serviceを呼ぶ
    $this->itemService->addItem($user->getId(), 'item-id-1');
}

// Service
public function addItem(string $usrUserId, string $itemId): void
{
    // Repositoryを呼ぶ
    $this->usrItemRepository->create($usrUserId, $itemId);
}

// Repository
public function create(string $usrUserId, string $itemId): UsrItemInterface
{
    $model = UsrItem::factory()->make([
        'usr_user_id' => $usrUserId,
        'mst_item_id' => $itemId,
    ]);

    $this->syncModel($model);

    return $model;
}
```

### パターン3: UseCase → Delegator → Service → Repository

```php
// UseCase
public function exec(string $userId): void
{
    $user = $this->usrUserRepository->findById($userId);

    // Delegatorを呼ぶ
    $this->rewardDelegator->sendRewards($user->getId(), 'ios', $now);
}

// Delegator
public function sendRewards(string $usrUserId, string $platform, CarbonImmutable $now): void
{
    // 複数のServiceを呼ぶ
    $this->itemService->addItem($usrUserId, 'item-id-1');
    $this->unitService->addUnit($usrUserId, 'unit-id-1');
}

// Service
public function addItem(string $usrUserId, string $itemId): void
{
    $this->usrItemRepository->create($usrUserId, $itemId);
}

// Repository
public function create(string $usrUserId, string $itemId): UsrItemInterface
{
    $model = UsrItem::factory()->make([
        'usr_user_id' => $usrUserId,
        'mst_item_id' => $itemId,
    ]);

    $this->syncModel($model);

    return $model;
}
```

## チェックリスト

UseCase/Service実装時の確認項目：

- [ ] 認証済みユーザーのuser idを使用しているか
- [ ] モデル生成時に正しいuser idを渡しているか
- [ ] UsrUserの場合、getId()を使っているか
- [ ] 変数名を取り違えていないか
- [ ] 他人のデータを扱う場合、専用メソッドを使っているか
- [ ] SignUpUseCase以外でsetUsrUserId()を使っていないか

## 関連ドキュメント

- **[../error-patterns.md](../error-patterns.md#エラーパターン4-モデル生成時のuser-id不一致)** - モデル生成時のエラーパターン
- **[fix-repository.md](fix-repository.md)** - Repository層での修正例
- **[fix-signup-usecase.md](fix-signup-usecase.md)** - SignUpUseCaseの特殊パターン
