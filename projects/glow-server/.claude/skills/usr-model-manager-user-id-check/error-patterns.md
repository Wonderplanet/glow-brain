# User ID Check エラーパターン

UsrModelManager機構で発生するuser id checkエラーの全パターンと原因を解説します。

## 目次

- [エラーメッセージの形式](#エラーメッセージの形式)
- [エラーパターン1: UsrModelManager未初期化](#エラーパターン1-usrmodelmanager未初期化)
- [エラーパターン2: 空文字列での初期化](#エラーパターン2-空文字列での初期化)
- [エラーパターン3: 他人のモデルをsyncModelsに渡す](#エラーパターン3-他人のモデルをsyncmodelsに渡す)
- [エラーパターン4: モデル生成時のuser id不一致](#エラーパターン4-モデル生成時のuser-id不一致)
- [エラーパターン5: setUsrUserIdの不適切な使用](#エラーパターン5-setusruseridの不適切な使用)

## エラーメッセージの形式

user id checkエラーは、`UsrModelCacheRepository::isValidModel()`で検出され、以下の形式のGameExceptionがスローされます：

```
ErrorCode: INVALID_PARAMETER
Message: this model class is invalid. (model class: {クラス名}, user id check: false)
```

**重要なポイント:**
- `user id check: false` が含まれる場合、user id不一致エラー
- `model class is invalid` と出るが、実際にはモデルクラス自体の問題ではなく、user idの問題

## エラーパターン1: UsrModelManager未初期化

### 症状

UsrModelManagerに`usrUserId`が設定されていない状態で、`syncModels()`や`syncModel()`を呼び出す。

### 原因

リクエスト開始時に`UsrModelManager::getUsrUserId()`が空文字列（初期値）のまま、Repositoryの処理が実行される。

### エラーが発生するコード例

❌ **間違い:**

```php
// UseCase
public function exec(string $userId): void
{
    // UsrModelManagerにuser idが設定されていない
    $user = $this->usrUserRepository->findById($userId); // これは成功する

    $newModel = UsrItem::factory()->make([
        'usr_user_id' => $userId,
        'mst_item_id' => '1',
    ]);

    // ここでエラー: UsrModelManager::getUsrUserId() が空文字列
    $this->usrItemRepository->syncModel($newModel);
}
```

### 正しい実装

✅ **正しい:**

```php
// UseCase
public function exec(string $userId): void
{
    // 認証済みユーザーは、リクエスト開始時に自動的にUsrModelManagerに設定される
    // 明示的にsetUsrUserIdを呼ぶ必要はない（SignUpUseCase以外）

    $user = $this->usrUserRepository->findById($userId);

    $newModel = UsrItem::factory()->make([
        'usr_user_id' => $userId,
        'mst_item_id' => '1',
    ]);

    $this->usrItemRepository->syncModel($newModel); // 正常に動作
}
```

### 根本原因

- glow-serverでは、認証ミドルウェアがリクエスト開始時に自動的に`UsrModelManager::setUsrUserId()`を呼び出す
- このミドルウェアが動作する前に、UsrModelManagerを使用するとエラーになる
- テストコードで`UsrModelManager`を直接使う場合は、明示的に`setUsrUserId()`を呼ぶ必要がある

## エラーパターン2: 空文字列での初期化

### 症状

`UsrModelManager::setUsrUserId('')` のように空文字列を設定している。

### 原因

UsrModelManagerの初期値が空文字列であるため、空文字列を設定しても未初期化と同じ状態になる。

### エラーが発生するコード例

❌ **間違い:**

```php
// テストコード
public function test_example(): void
{
    $this->usrModelManager->setUsrUserId(''); // 空文字列

    $model = TestMultiModel::create('id1', '', intValue: 0, isChanged: true);

    // エラー: user id check fails
    $this->usrModelManager->syncModels(TestRepository::class, [$model]);
}
```

### 正しい実装

✅ **正しい:**

```php
// テストコード
public function test_example(): void
{
    $usrUserId = $this->createUsrUser()->getId(); // 有効なuser idを取得
    $this->usrModelManager->setUsrUserId($usrUserId);

    $model = TestMultiModel::create('id1', $usrUserId, intValue: 0, isChanged: true);

    $this->usrModelManager->syncModels(TestRepository::class, [$model]);
}
```

## エラーパターン3: 他人のモデルをsyncModelsに渡す

### 症状

UsrModelManagerに設定されているuser idと異なるuser idを持つモデルを`syncModels()`に渡す。

### 原因

UsrModelManagerは、APIリクエストを送ったユーザー本人のデータのみを扱うことを保証するため、他人のデータを拒否する。

### エラーが発生するコード例

❌ **間違い:**

```php
// UsrModelManager::getUsrUserId() = 'user-id-1'

$otherUserModel = UsrItem::factory()->make([
    'usr_user_id' => 'user-id-2', // 異なるuser id
    'mst_item_id' => '1',
]);

// エラー: user id check fails
$this->usrItemRepository->syncModel($otherUserModel);
```

### 正しい実装

✅ **正しい:**

```php
// UsrModelManager::getUsrUserId() = 'user-id-1'

$ownModel = UsrItem::factory()->make([
    'usr_user_id' => 'user-id-1', // 同じuser id
    'mst_item_id' => '1',
]);

$this->usrItemRepository->syncModel($ownModel); // 正常に動作
```

### 特殊ケース: 他人のデータをDB取得のみ行う場合

他人のデータをDB取得のみ行う場合は、キャッシュを経由せずに直接DBから取得する：

```php
// 他人のデータを取得する（キャッシュを使わない）
public function findRecentlyCreatedAtByClientUuid(string $clientUuid): ?UsrUserInterface
{
    return UsrUser::where('client_uuid', $clientUuid)
        ->orderBy('created_at', 'desc')
        ->first();
}
```

参照: `api/app/Domain/User/Repositories/UsrUserRepository.php:86-91`

## エラーパターン4: モデル生成時のuser id不一致

### 症状

モデル生成時に、UsrModelManagerに設定されているuser idと異なるuser idを指定する。

### 原因

モデル生成時の引数ミス、または変数の取り違え。

### エラーが発生するコード例

❌ **間違い:**

```php
public function exec(string $targetUserId): void
{
    // UsrModelManager::getUsrUserId() = 'authenticated-user-id'

    $model = UsrItem::factory()->make([
        'usr_user_id' => $targetUserId, // 引数の値を使っている
        'mst_item_id' => '1',
    ]);

    // エラー: user id check fails
    $this->usrItemRepository->syncModel($model);
}
```

### 正しい実装

✅ **正しい:**

```php
public function exec(string $userId): void
{
    // 認証済みユーザーのidを使う
    $user = $this->usrUserRepository->findById($userId);

    $model = UsrItem::factory()->make([
        'usr_user_id' => $user->getId(), // 認証済みユーザーのid
        'mst_item_id' => '1',
    ]);

    $this->usrItemRepository->syncModel($model);
}
```

## エラーパターン5: setUsrUserIdの不適切な使用

### 症状

SignUpUseCase以外で`UsrModelManager::setUsrUserId()`を呼び出している。

### 原因

`setUsrUserId()`は、SignUpUseCase内でユーザー作成時のみ使用すべきメソッド。

### エラーが発生するコード例

❌ **間違い:**

```php
public function exec(string $userId): void
{
    // 不要な呼び出し
    $this->usrModelManager->setUsrUserId($userId);

    // ...処理...
}
```

### 正しい実装

✅ **正しい:**

```php
public function exec(string $userId): void
{
    // setUsrUserIdは呼ばない
    // 認証ミドルウェアが自動的に設定する

    // ...処理...
}
```

### SignUpUseCaseの特殊な例外

SignUpUseCaseでは、ユーザー作成時に`setUsrUserId()`を使う必要がある：

```php
// SignUpUseCase
public function exec(string $platform, string $billingPlatform, ?string $clientUuid): array
{
    $user = $this->usrUserRepository->make($now, $clientUuid);

    // ユーザー作成直後に設定
    $this->usrModelManager->setUsrUserId($user->getId());

    // この後、syncModelを呼ぶ
    $this->usrUserRepository->syncModel($user);

    // ...続く処理...
}
```

参照: `api/app/Domain/Auth/UseCases/SignUpUseCase.php:74-80`

## デバッグのチェックリスト

user id checkエラーが発生した場合、以下を確認：

- [ ] `UsrModelManager::getUsrUserId()`が空文字列ではないか
- [ ] モデルの`usr_user_id`と`UsrModelManager::getUsrUserId()`が一致しているか
- [ ] SignUpUseCase以外で`setUsrUserId()`を呼んでいないか
- [ ] 他人のデータをキャッシュに追加しようとしていないか
- [ ] モデル生成時の引数が正しいか

## 関連ファイル

- `api/app/Infrastructure/UsrModelManager.php` - UsrModelManager本体
- `api/app/Domain/Resource/Usr/Repositories/UsrModelCacheRepository.php:31-48` - isValidModel()の実装
- `api/app/Domain/Auth/UseCases/SignUpUseCase.php:74-80` - setUsrUserIdの正しい使用例
