# SignUpUseCaseでの修正例

SignUpUseCaseは、UsrModelManagerにおいて唯一`setUsrUserId()`を使用する特殊なケースです。

## 目次

- [SignUpUseCaseの特殊性](#signupusecaseの特殊性)
- [正しい実装手順](#正しい実装手順)
- [実際のコード例](#実際のコード例)
- [よくある間違い](#よくある間違い)
- [テストでの注意点](#テストでの注意点)

## SignUpUseCaseの特殊性

### なぜsetUsrUserId()が必要か

SignUpUseCaseでは、ユーザー作成時にまだユーザーIDが存在しないため、認証ミドルウェアでUsrModelManagerを初期化できません。

```
通常のリクエスト:
    認証ミドルウェア → UsrModelManager初期化 → UseCase実行

SignUpリクエスト:
    認証なし → UseCase実行 → ユーザー作成 → UsrModelManager初期化
```

### setUsrUserId()の使用タイミング

```php
// SignUpUseCase内での処理順序
1. ユーザーモデルを作成（make）
2. setUsrUserId()でUsrModelManagerを初期化
3. syncModel()でキャッシュに追加
```

## 正しい実装手順

### ステップ1: ユーザーモデルを作成

```php
$user = $this->usrUserRepository->make($now, $clientUuid);
```

**注意点:**
- まだDBに保存されていない
- `make()`はモデルインスタンスを生成するのみ

### ステップ2: UsrModelManagerを初期化

```php
$this->usrModelManager->setUsrUserId($user->getId());
```

**重要:**
- syncModel()を呼ぶ**前**に実行する
- user idは`$user->getId()`から取得

### ステップ3: キャッシュに追加

```php
$this->usrUserRepository->syncModel($user);
```

**この時点で:**
- UsrModelManager::getUsrUserId()が設定済み
- user id checkが成功する

### ステップ4: 他のテーブルのデータを作成

```php
// UsrModelManagerが初期化されているので、他のRepositoryも使用可能
$this->usrUserParameterRepository->create($user->getId(), $stamina, $now);
$this->usrUserLoginRepository->create($user->getId(), $now);
```

## 実際のコード例

### SignUpUseCase.php

```php
// api/app/Domain/Auth/UseCases/SignUpUseCase.php:64-86
public function exec(string $platform, string $billingPlatform, ?string $clientUuid): array
{
    $now = $this->clock->now();

    try {
        $recentlyUser = null;
        if ($clientUuid !== null) {
            // 他人のデータを取得（キャッシュを使わない）
            $recentlyUser = $this->usrUserRepository->findRecentlyCreatedAtByClientUuid($clientUuid);
        }

        // ステップ1: ユーザーモデルを作成
        $user = $this->usrUserRepository->make($now, $clientUuid);

        // ステップ2: UsrModelManagerを初期化
        $this->usrModelManager->setUsrUserId($user->getId());

        /**
         * syncModelで指定したモデルのユーザーIDが
         * UsrModelManagerにセットされたユーザーIDと同じかチェックを行っているため
         * usrModelManagerにユーザーIDをセットしてからsyncModelを実行する
         */

        // ステップ3: キャッシュに追加
        $this->usrUserRepository->syncModel($user);

        // ステップ4: 他のテーブルのデータを作成
        $mstUserLevel = $this->mstUserLevelRepository->getByLevel(1, true);
        $this->usrUserParameterRepository->create($user->getId(), $mstUserLevel->getStamina(), $now);

        // 未ログイン状態の初期値でレコード作成
        $this->usrUserLoginRepository->create($user->getId(), $now);

        $this->userDelegator->createUsrUserProfile($user->getId());

        // ...以下省略...
    } catch (\Exception $e) {
        throw new GameException(ErrorCode::USER_CREATE_FAILED, $e->getMessage());
    }

    // ...以下省略...
}
```

参照: `api/app/Domain/Auth/UseCases/SignUpUseCase.php:64-86`

### UsrUserRepository::make()

```php
// api/app/Domain/User/Repositories/UsrUserRepository.php:46-61
/**
 * モデルインスタンスの生成のみを実行する
 */
public function make(CarbonImmutable $now, ?string $clientUuid = null): UsrUserInterface
{
    $usrUser = new UsrUser();
    $usrUser->status = UserStatus::NORMAL->value;
    $usrUser->tutorial_status = '';
    $usrUser->tos_version = 0;
    $usrUser->privacy_policy_version = 0;
    $usrUser->global_consent_version = 0;
    $usrUser->iaa_version = 0;
    $usrUser->bn_user_id = null;
    $usrUser->client_uuid = $clientUuid;
    $usrUser->suspend_end_at = null;
    $usrUser->game_start_at = $now->toDateTimeString();

    return $usrUser;
}
```

参照: `api/app/Domain/User/Repositories/UsrUserRepository.php:46-61`

## よくある間違い

### 間違い1: setUsrUserIdを呼ばない

❌ **間違い:**

```php
$user = $this->usrUserRepository->make($now, $clientUuid);

// setUsrUserIdを呼んでいない

// エラー: UsrModelManager::getUsrUserId()が空文字列
$this->usrUserRepository->syncModel($user);
```

✅ **正しい:**

```php
$user = $this->usrUserRepository->make($now, $clientUuid);

// setUsrUserIdを呼ぶ
$this->usrModelManager->setUsrUserId($user->getId());

$this->usrUserRepository->syncModel($user);
```

### 間違い2: setUsrUserIdをsyncModelの後に呼ぶ

❌ **間違い:**

```php
$user = $this->usrUserRepository->make($now, $clientUuid);

// syncModelを先に呼んでしまう
$this->usrUserRepository->syncModel($user); // エラー

// setUsrUserIdを後で呼んでも手遅れ
$this->usrModelManager->setUsrUserId($user->getId());
```

✅ **正しい:**

```php
$user = $this->usrUserRepository->make($now, $clientUuid);

// 先にsetUsrUserIdを呼ぶ
$this->usrModelManager->setUsrUserId($user->getId());

// その後syncModelを呼ぶ
$this->usrUserRepository->syncModel($user);
```

### 間違い3: 他人のデータをキャッシュに追加

❌ **間違い:**

```php
// 他人のデータを取得
$recentlyUser = $this->usrUserRepository->findRecentlyCreatedAtByClientUuid($clientUuid);

$user = $this->usrUserRepository->make($now, $clientUuid);
$this->usrModelManager->setUsrUserId($user->getId());
$this->usrUserRepository->syncModel($user);

// エラー: $recentlyUserのuser idが$userと異なる
$this->usrUserRepository->syncModel($recentlyUser);
```

✅ **正しい:**

```php
// 他人のデータは、キャッシュを使わない専用メソッドで取得
$recentlyUser = $this->usrUserRepository->findRecentlyCreatedAtByClientUuid($clientUuid);

$user = $this->usrUserRepository->make($now, $clientUuid);
$this->usrModelManager->setUsrUserId($user->getId());
$this->usrUserRepository->syncModel($user);

// $recentlyUserはキャッシュに追加しない
```

### 間違い4: SignUpUseCase以外でsetUsrUserIdを使う

❌ **間違い:**

```php
// 通常のUseCase
public function exec(string $userId): void
{
    // 不要な呼び出し
    $this->usrModelManager->setUsrUserId($userId);

    // ...処理...
}
```

✅ **正しい:**

```php
// 通常のUseCase
public function exec(string $userId): void
{
    // setUsrUserIdは呼ばない
    // 認証ミドルウェアが自動的に設定する

    // ...処理...
}
```

## テストでの注意点

### SignUpUseCaseのテスト

```php
public function test_signup(): void
{
    // SignUpUseCaseのテストでは、UsrModelManagerは自動初期化されない

    $result = $this->signUpUseCase->exec('ios', 'apple', 'client-uuid');

    // UseCase内でsetUsrUserIdが呼ばれるので、テスト側で呼ぶ必要はない

    $this->assertNotEmpty($result['id_token']);
}
```

### 他のUseCaseのテスト

```php
public function test_other_usecase(): void
{
    // 他のUseCaseのテストでは、UsrModelManagerを明示的に初期化する
    $usrUserId = $this->createUsrUser()->getId();
    $this->usrModelManager->setUsrUserId($usrUserId);

    $result = $this->otherUseCase->exec($usrUserId);

    // ...検証...
}
```

## チェックリスト

SignUpUseCase実装時の確認項目：

- [ ] `make()`でユーザーモデルを作成しているか
- [ ] `setUsrUserId()`を`syncModel()`の前に呼んでいるか
- [ ] `setUsrUserId()`に正しいuser idを渡しているか
- [ ] 他人のデータをキャッシュに追加していないか
- [ ] SignUpUseCase以外で`setUsrUserId()`を使っていないか

## 関連ドキュメント

- **[../error-patterns.md](../error-patterns.md#エラーパターン5-setusruseridの不適切な使用)** - setUsrUserIdのエラーパターン
- **[../guides/architecture.md](../guides/architecture.md#リクエストライフサイクル)** - リクエストライフサイクル
- **[fix-repository.md](fix-repository.md#他人のデータを取得する場合)** - 他人のデータ取得方法
