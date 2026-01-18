# 例外・エラーの修正パターン

## 概要

例外・エラーは、想定外の例外が発生したり、必要な例外が発生しなかった場合に発生します。

**典型的なエラーメッセージ**:
```
RuntimeException: User not found
TypeError: Argument must be of type string, null given
Error: Call to a member function on null
Fatal error: Uncaught exception
```

## 原因分析フロー

```
1. 例外の種類を特定
   ↓
2. スタックトレースを確認
   ↓
3. 原因を特定
   ├─ テストデータの不足
   ├─ nullチェック不足
   ├─ 型の不一致
   └─ 意図的な例外のテスト不備
   ↓
4. 適切な修正パターンを選択
```

## 修正パターン1: テストデータを準備

### ケース1-1: データが存在しない

**エラー**:
```
RuntimeException: User not found

at app/Domain/User/Services/UserService.php:23
```

**原因**: ユーザーが作成されていない

**修正前**:
```php
public function test_getUser()
{
    // ユーザーを作成していない
    $result = $this->userService->getUser('user_123');  // エラー
    $this->assertNotNull($result);
}
```

**修正後**:
```php
public function test_getUser()
{
    // ユーザーを作成
    $user = $this->createUsrUser(['id' => 'user_123']);  // ✅ 追加

    $result = $this->userService->getUser('user_123');  // 成功
    $this->assertNotNull($result);
}
```

### ケース1-2: マスターデータが不足

**エラー**:
```
RuntimeException: Master item not found

at app/Domain/Item/Services/ItemService.php:45
```

**原因**: マスターデータが作成されていない

**修正前**:
```php
public function test_addItem()
{
    $user = $this->createUsrUser();
    // MstItemを作成していない

    $this->itemService->addItem($user->getId(), 'item_1', 10);  // エラー
}
```

**修正後**:
```php
public function test_addItem()
{
    $user = $this->createUsrUser();
    MstItem::factory()->create(['id' => 'item_1']);  // ✅ 追加

    $this->itemService->addItem($user->getId(), 'item_1', 10);  // 成功
}
```

### ケース1-3: 依存データの作成順序が間違っている

**エラー**:
```
SQLSTATE[23000]: Integrity constraint violation
```

**原因**: 外部キー制約違反（親データが先に作成されていない）

**修正前**:
```php
public function test_example()
{
    // アイテムを先に作成（外部キー制約違反）
    UsrItem::factory()->create([
        'usr_user_id' => 'user_123',  // エラー: ユーザーが存在しない
        'mst_item_id' => 'item_1',
    ]);
}
```

**修正後**:
```php
public function test_example()
{
    // 先にユーザーを作成
    $user = $this->createUsrUser(['id' => 'user_123']);  // ✅ 先に作成

    // 次にアイテムを作成
    UsrItem::factory()->create([
        'usr_user_id' => $user->getId(),
        'mst_item_id' => 'item_1',
    ]);
}
```

## 修正パターン2: setUp()で初期化

### ケース2-1: プロパティが初期化されていない

**エラー**:
```
Error: Call to a member function getId() on null

at tests/Feature/Domain/Item/ItemServiceTest.php:45
```

**原因**: `$this->user`が初期化されていない

**修正前**:
```php
class ItemServiceTest extends TestCase
{
    private UsrUser $user;

    public function test_example()
    {
        $userId = $this->user->getId();  // エラー: $this->userが未初期化
    }
}
```

**修正後**:
```php
class ItemServiceTest extends TestCase
{
    private UsrUser $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUsrUser();  // ✅ setUp()で初期化
    }

    public function test_example()
    {
        $userId = $this->user->getId();  // 成功
    }
}
```

**注意**: setUp()は全テストメソッド実行前に毎回実行される。

### ケース2-2: サービスクラスが初期化されていない

**エラー**:
```
Error: Call to a member function apply() on null
```

**修正前**:
```php
class ItemServiceTest extends TestCase
{
    private ItemService $itemService;

    public function test_apply()
    {
        $this->itemService->apply(...);  // エラー: 未初期化
    }
}
```

**修正後**:
```php
class ItemServiceTest extends TestCase
{
    private ItemService $itemService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemService = app()->make(ItemService::class);  // ✅ 初期化
    }

    public function test_apply()
    {
        $this->itemService->apply(...);  // 成功
    }
}
```

## 修正パターン3: 例外を期待する

### ケース3-1: 意図的な例外のテスト

**エラー**:
```
RuntimeException: Invalid amount

at app/Domain/Item/Services/ItemService.php:50
```

**原因**: テストで例外が期待されていない

**修正前**:
```php
public function test_addItem_with_invalid_amount()
{
    $user = $this->createUsrUser();

    // 負の値で呼び出し（例外が発生する）
    $this->itemService->addItem($user->getId(), 'item_1', -10);  // エラー
}
```

**修正後**:
```php
public function test_addItem_with_invalid_amount()
{
    $user = $this->createUsrUser();

    // 例外を期待
    $this->expectException(\RuntimeException::class);  // ✅ 追加
    $this->expectExceptionMessage('Invalid amount');  // ✅ 追加

    // 負の値で呼び出し（例外が期待される）
    $this->itemService->addItem($user->getId(), 'item_1', -10);  // 成功
}
```

### ケース3-2: 複数の例外パターンをテスト

```php
public function test_例外ケース1_ユーザーが存在しない()
{
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('User not found');

    $this->service->process('invalid_user_id');
}

public function test_例外ケース2_無効な金額()
{
    $user = $this->createUsrUser();

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid amount');

    $this->service->process($user->getId(), -100);
}
```

## 修正パターン4: 型の問題を修正

### ケース4-1: null許容型の問題

**エラー**:
```
TypeError: Argument #1 must be of type string, null given

at app/Domain/User/Services/UserService.php:30
```

**原因**: nullを受け取る可能性があるがnull許容型になっていない

**修正前（実装コード）**:
```php
public function getUserName(string $userId): string  // バグ: nullを受け取れない
{
    $user = UsrUser::find($userId);
    return $user->name;
}
```

**修正後（実装コード）**:
```php
public function getUserName(?string $userId): ?string  // ✅ null許容型
{
    if ($userId === null) {
        return null;
    }

    $user = UsrUser::find($userId);
    return $user?->name;
}
```

### ケース4-2: 配列の型チェック

**エラー**:
```
TypeError: Argument #1 must be of type array, string given
```

**修正前（実装コード）**:
```php
public function processItems(array $items): void
{
    // ...
}

// 呼び出し箇所
$this->processItems('item_1');  // エラー: stringを渡している
```

**修正後**:
```php
// 呼び出し箇所
$this->processItems(['item_1']);  // ✅ 配列で渡す
```

## 修正パターン5: nullチェックを追加

### ケース5-1: nullチェック不足

**エラー**:
```
Error: Call to a member function toArray() on null

at app/Domain/Item/Services/ItemService.php:67
```

**原因**: nullチェックがない

**修正前（実装コード）**:
```php
public function getItem(string $userId, string $itemId): array
{
    $item = UsrItem::where('usr_user_id', $userId)
        ->where('mst_item_id', $itemId)
        ->first();

    return $item->toArray();  // バグ: $itemがnullの可能性
}
```

**修正後（実装コード - パターンA: nullチェック）**:
```php
public function getItem(string $userId, string $itemId): array
{
    $item = UsrItem::where('usr_user_id', $userId)
        ->where('mst_item_id', $itemId)
        ->first();

    if ($item === null) {  // ✅ nullチェック
        return [];
    }

    return $item->toArray();
}
```

**修正後（実装コード - パターンB: firstOrFail使用）**:
```php
public function getItem(string $userId, string $itemId): array
{
    $item = UsrItem::where('usr_user_id', $userId)
        ->where('mst_item_id', $itemId)
        ->firstOrFail();  // ✅ nullの場合は例外を投げる

    return $item->toArray();
}
```

**修正後（実装コード - パターンC: null合体演算子）**:
```php
public function getItem(string $userId, string $itemId): ?array
{
    $item = UsrItem::where('usr_user_id', $userId)
        ->where('mst_item_id', $itemId)
        ->first();

    return $item?->toArray();  // ✅ nullの場合はnullを返す
}
```

## デバッグ手順

### ステップ1: スタックトレースを読む

```
RuntimeException: User not found

at app/Domain/User/Services/UserService.php:23
  19▕ public function getUser(string $userId): UsrUser
  20▕ {
  21▕     $user = UsrUser::find($userId);
  22▕     if ($user === null) {
  ➜ 23▕         throw new \RuntimeException('User not found');
  24▕     }
  25▕     return $user;
  26▕ }
```

**確認事項**:
- 例外の種類: RuntimeException
- メッセージ: User not found
- 発生箇所: UserService.php:23

### ステップ2: try-catchでデバッグ

```php
public function test_getUser()
{
    $user = $this->createUsrUser(['id' => 'user_123']);
    dump('Created user:', $user->toArray());

    try {
        $result = $this->userService->getUser('user_123');
        dump('Result:', $result);
    } catch (\Exception $e) {
        dump('Exception:', $e->getMessage());
        dump('Stack trace:', $e->getTraceAsString());
        throw $e;
    }
}
```

### ステップ3: 原因を特定

dump()の出力から原因を特定:
- ユーザーが作成されていない → **修正パターン1-1**
- 初期化されていない → **修正パターン2-1**
- 意図的な例外 → **修正パターン3-1**

### ステップ4: 修正して確認

修正後、dump()とtry-catchを削除してテストを再実行。

## よくある間違い

### 間違い1: expectException()の位置

```php
// ❌ 間違い: expectException()が後
$this->service->process();  // エラー: ここで例外が発生
$this->expectException(\RuntimeException::class);  // 実行されない

// ✅ 正しい: expectException()が先
$this->expectException(\RuntimeException::class);
$this->service->process();  // 例外が期待される
```

### 間違い2: 複数の例外を期待

```php
// ❌ 間違い: 複数の例外は期待できない
$this->expectException(\RuntimeException::class);
$this->expectException(\InvalidArgumentException::class);  // 上書きされる

// ✅ 正しい: テストメソッドを分ける
public function test_例外ケース1()
{
    $this->expectException(\RuntimeException::class);
    // ...
}

public function test_例外ケース2()
{
    $this->expectException(\InvalidArgumentException::class);
    // ...
}
```

### 間違い3: setUp()でparent::setUp()を呼び忘れ

```php
// ❌ 間違い: parent::setUp()を呼んでいない
protected function setUp(): void
{
    $this->user = $this->createUsrUser();  // エラー: DBが初期化されていない
}

// ✅ 正しい: parent::setUp()を必ず呼ぶ
protected function setUp(): void
{
    parent::setUp();  // 必須
    $this->user = $this->createUsrUser();
}
```

## チェックリスト

### エラー発生時
- [ ] 例外の種類を確認（RuntimeException, TypeError等）
- [ ] スタックトレースを読む
- [ ] 発生箇所のファイル名・行番号を確認

### 修正前
- [ ] 原因を特定（データ不足/null/型/意図的）
- [ ] 適切な修正パターンを選択
- [ ] テストデータの依存関係を確認

### 修正後
- [ ] try-catch、dump()を削除
- [ ] テストを再実行して成功を確認
- [ ] 他のテストに影響がないか確認

### コミット前
- [ ] 全テストがPASS
- [ ] デバッグ用コードが残っていない
- [ ] setUp()でparent::setUp()を呼んでいる
