# Debugging User ID Check Errors

user id checkエラーの調査手順をステップバイステップで解説します。

## 目次

- [デバッグの基本フロー](#デバッグの基本フロー)
- [ステップ1: エラーメッセージを確認](#ステップ1-エラーメッセージを確認)
- [ステップ2: スタックトレースを追う](#ステップ2-スタックトレースを追う)
- [ステップ3: UsrModelManagerの状態を確認](#ステップ3-usrmodelmanagerの状態を確認)
- [ステップ4: モデルのuser idを確認](#ステップ4-モデルのuser-idを確認)
- [ステップ5: 修正方針を決定](#ステップ5-修正方針を決定)
- [デバッグ用のヘルパーメソッド](#デバッグ用のヘルパーメソッド)

## デバッグの基本フロー

```
エラー発生
    ↓
[ステップ1] エラーメッセージ確認
    ↓
[ステップ2] スタックトレース分析
    ↓
[ステップ3] UsrModelManagerの状態確認
    ↓
[ステップ4] モデルのuser id確認
    ↓
[ステップ5] 修正方針決定
    ↓
修正実装
```

## ステップ1: エラーメッセージを確認

### エラーメッセージの形式

```
ErrorCode: INVALID_PARAMETER
Message: this model class is invalid. (model class: App\Domain\Item\Models\Eloquent\UsrItem, user id check: false)
```

### 確認ポイント

#### 1. `user id check: false` が含まれているか

含まれている → **user id不一致エラー**（このスキルの対象）
含まれていない → モデルクラス自体の問題（別の問題）

#### 2. モデルクラス名を確認

- どのモデルでエラーが発生したか
- どのRepositoryが関係しているか

```
model class: App\Domain\Item\Models\Eloquent\UsrItem
              ↓
関係するRepository: App\Domain\Item\Repositories\UsrItemRepository
```

## ステップ2: スタックトレースを追う

### スタックトレースの読み方

```
#0 UsrModelCacheRepository.php(95): isValidModel()
#1 UsrItemRepository.php(150): syncModels()
#2 ItemService.php(42): syncModel()
#3 ItemUseCase.php(30): addItem()
```

### 確認ポイント

#### 1. syncModel/syncModelsが呼ばれた箇所

スタックトレースから、どこで`syncModel()`または`syncModels()`が呼ばれたかを特定します。

```
#2 ItemService.php(42): syncModel()
    ↓
ItemService.phpの42行目で syncModel() を呼んでいる
```

#### 2. モデルが生成された箇所

syncModelの前に、モデルがどこで生成されたかを確認します。

```php
// ItemService.php:40-42
$model = UsrItem::factory()->make([
    'usr_user_id' => $userId, // ← ここで user id を設定
]);
$this->usrItemRepository->syncModel($model); // 42行目
```

#### 3. user idの取得元

モデル生成時に使用したuser idが、どこから来たかを追跡します。

```php
public function addItem(string $userId, string $itemId): void
{
    // $userId はどこから来たか？
    // - 引数として渡された
    // - $user->getId() から取得した
    // - どこかの変数から取得した
}
```

## ステップ3: UsrModelManagerの状態を確認

### 確認方法

#### 1. UsrModelManager::getUsrUserId()の値

```php
// デバッグ用コード
$usrUserId = $this->usrModelManager->getUsrUserId();
dump('UsrModelManager user id:', $usrUserId);
```

#### 2. 空文字列チェック

```php
if ($usrUserId === '') {
    // UsrModelManagerが未初期化
    dump('UsrModelManager is not initialized');
}
```

#### 3. テストコードでの確認

```php
// テストコード
public function test_example(): void
{
    $usrUserId = $this->usrModelManager->getUsrUserId();
    $this->assertNotEmpty($usrUserId); // 空文字列でないことを確認
}
```

### よくあるパターン

#### パターンA: 空文字列（未初期化）

```
UsrModelManager::getUsrUserId() = ''
    ↓
原因: setUsrUserId()が呼ばれていない
    ↓
対策: 認証ミドルウェアが動作しているか確認
```

#### パターンB: 異なるuser id

```
UsrModelManager::getUsrUserId() = 'user-A-id'
Model::getUsrUserId() = 'user-B-id'
    ↓
原因: モデル生成時のuser idが間違っている
    ↓
対策: モデル生成時の引数を修正
```

## ステップ4: モデルのuser idを確認

### 確認方法

#### 1. モデルのuser idを取得

```php
// デバッグ用コード
dump('Model user id:', $model->getUsrUserId());
dump('UsrModelManager user id:', $this->usrModelManager->getUsrUserId());
```

#### 2. 比較

```php
$modelUserId = $model->getUsrUserId();
$managerUserId = $this->usrModelManager->getUsrUserId();

if ($modelUserId !== $managerUserId) {
    dump('User id mismatch!');
    dump('Model:', $modelUserId);
    dump('Manager:', $managerUserId);
}
```

### よくある原因

#### 1. 引数の取り違え

```php
// ❌ 間違い
public function exec(string $targetUserId): void
{
    // UsrModelManager::getUsrUserId() = 'authenticated-user-id'

    $model = UsrItem::factory()->make([
        'usr_user_id' => $targetUserId, // ← これが間違い
    ]);
}
```

#### 2. 変数名の混同

```php
// ❌ 間違い
$userId = '...'; // どこかで取得
$usrUserId = '...'; // 別のところで取得

$model = UsrItem::factory()->make([
    'usr_user_id' => $userId, // ← $usrUserIdと間違えた
]);
```

#### 3. 他人のデータを使用

```php
// ❌ 間違い
$otherUser = $this->usrUserRepository->findRecentlyCreatedAtByClientUuid($uuid);

$model = UsrItem::factory()->make([
    'usr_user_id' => $otherUser->getId(), // ← 他人のid
]);
```

## ステップ5: 修正方針を決定

### 修正方針のフローチャート

```
UsrModelManager::getUsrUserId()が空文字列？
    ↓ Yes
    [修正A] setUsrUserIdを呼ぶ（SignUpUseCaseのみ）
    または
    [修正B] 認証ミドルウェアの設定を確認

    ↓ No
モデルのuser idが間違っている？
    ↓ Yes
    [修正C] モデル生成時の引数を修正

    ↓ No
他人のデータを使っている？
    ↓ Yes
    [修正D] キャッシュを使わない実装に変更

    ↓ No
    [その他] error-patterns.mdを参照
```

### 修正方針別の対応例

#### 修正A: SignUpUseCaseでsetUsrUserIdを呼ぶ

```php
// SignUpUseCase
$user = $this->usrUserRepository->make($now, $clientUuid);

// ユーザー作成直後に設定
$this->usrModelManager->setUsrUserId($user->getId());

$this->usrUserRepository->syncModel($user);
```

参照: **[examples/fix-signup-usecase.md](../examples/fix-signup-usecase.md)**

#### 修正B: 認証ミドルウェアの設定確認

```php
// api/app/Http/Kernel.php
// 認証ミドルウェアが正しく設定されているか確認
```

#### 修正C: モデル生成時の引数を修正

```php
// ✅ 正しい
$user = $this->usrUserRepository->findById($userId);

$model = UsrItem::factory()->make([
    'usr_user_id' => $user->getId(), // 認証済みユーザーのid
]);
```

参照: **[examples/fix-service.md](../examples/fix-service.md)**

#### 修正D: キャッシュを使わない実装

```php
// ✅ 他人のデータは、キャッシュを使わずに直接DB取得
public function findRecentlyCreatedAtByClientUuid(string $clientUuid): ?UsrUserInterface
{
    return UsrUser::where('client_uuid', $clientUuid)
        ->orderBy('created_at', 'desc')
        ->first();
}
```

参照: **[examples/fix-repository.md](../examples/fix-repository.md)**

## デバッグ用のヘルパーメソッド

### 1. user id比較メソッド

```php
// デバッグ用
private function debugUserIdCheck(UsrModelInterface $model): void
{
    $modelUserId = $model->getUsrUserId();
    $managerUserId = $this->usrModelManager->getUsrUserId();

    dump([
        'Model user id' => $modelUserId,
        'Manager user id' => $managerUserId,
        'Match' => $modelUserId === $managerUserId,
    ]);
}
```

### 2. スタックトレース出力

```php
// デバッグ用
private function debugStackTrace(): void
{
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    foreach ($backtrace as $i => $trace) {
        dump("#{$i} {$trace['file']}:{$trace['line']} {$trace['function']}()");
    }
}
```

### 3. テストコードでの確認

```php
// テストコード
public function test_user_id_check_debug(): void
{
    $usrUserId = $this->createUsrUser()->getId();

    // UsrModelManagerの状態を確認
    dump('Before setUsrUserId:', $this->usrModelManager->getUsrUserId());

    $this->usrModelManager->setUsrUserId($usrUserId);

    // UsrModelManagerの状態を確認
    dump('After setUsrUserId:', $this->usrModelManager->getUsrUserId());

    $model = TestMultiModel::create('id1', $usrUserId, intValue: 0, isChanged: true);

    // モデルのuser idを確認
    dump('Model user id:', $model->getUsrUserId());

    $this->usrModelManager->syncModels(TestRepository::class, [$model]);
}
```

## チェックリスト

エラー調査時に確認すべき項目：

- [ ] エラーメッセージに`user id check: false`が含まれているか
- [ ] スタックトレースから、syncModel/syncModelsが呼ばれた箇所を特定したか
- [ ] UsrModelManager::getUsrUserId()の値を確認したか
- [ ] モデルのgetUsrUserId()の値を確認したか
- [ ] 両者が一致しているか
- [ ] SignUpUseCase以外でsetUsrUserId()を呼んでいないか
- [ ] 他人のデータをキャッシュに追加しようとしていないか
- [ ] モデル生成時の引数が正しいか

## 関連ドキュメント

- **[../error-patterns.md](../error-patterns.md)** - エラーパターン別の詳細
- **[architecture.md](architecture.md)** - UsrModelManagerの仕組み
- **[../examples/fix-signup-usecase.md](../examples/fix-signup-usecase.md)** - SignUpUseCaseの修正例
- **[../examples/fix-repository.md](../examples/fix-repository.md)** - Repositoryの修正例
- **[../examples/fix-service.md](../examples/fix-service.md)** - Service/UseCaseの修正例
