# DB関連エラーの修正パターン

## 概要

DB関連エラーは、データベース制約違反、マイグレーション未実行、テストデータ不備などで発生します。

**典型的なエラーメッセージ**:
```
SQLSTATE[23000]: Integrity constraint violation
SQLSTATE[42S02]: Base table or view not found
SQLSTATE[HY000]: General error
QueryException: SQLSTATE[...]: ...
```

## 原因分析フロー

```
1. SQLSTATEコードを確認
   ↓
2. エラーメッセージを読む
   ↓
3. 原因を特定
   ├─ 外部キー制約違反
   ├─ ユニーク制約違反
   ├─ テーブル不存在
   ├─ カラム不存在
   └─ saveAll()実行漏れ
   ↓
4. 適切な修正パターンを選択
```

## SQLSTATEコード一覧

| コード | 意味 | よくある原因 |
|-------|------|------------|
| 23000 | 制約違反 | 外部キー制約、ユニーク制約 |
| 42S02 | テーブル不存在 | マイグレーション未実行 |
| 42S22 | カラム不存在 | マイグレーション未実行、カラム名ミス |
| HY000 | 一般エラー | 様々な原因 |

## 修正パターン1: 外部キー制約違反

### ケース1-1: 親データが存在しない

**エラー**:
```
SQLSTATE[23000]: Integrity constraint violation:
Foreign key constraint fails (`usr_items`, CONSTRAINT `usr_items_usr_user_id_foreign`)
```

**原因**: 外部キー参照先のデータが存在しない

**修正前**:
```php
public function test_createItem()
{
    // ユーザーを作成していない
    UsrItem::factory()->create([
        'usr_user_id' => 'user_123',  // エラー: ユーザーが存在しない
        'mst_item_id' => 'item_1',
    ]);
}
```

**修正後**:
```php
public function test_createItem()
{
    // 先に親データ（ユーザー）を作成
    $user = $this->createUsrUser(['id' => 'user_123']);  // ✅ 追加

    UsrItem::factory()->create([
        'usr_user_id' => $user->getId(),  // 成功
        'mst_item_id' => 'item_1',
    ]);
}
```

### ケース1-2: マスターデータが不足

**エラー**:
```
SQLSTATE[23000]: Integrity constraint violation:
Foreign key constraint fails (`usr_items`, CONSTRAINT `usr_items_mst_item_id_foreign`)
```

**修正前**:
```php
public function test_addItem()
{
    $user = $this->createUsrUser();
    // MstItemを作成していない

    UsrItem::factory()->create([
        'usr_user_id' => $user->getId(),
        'mst_item_id' => 'item_1',  // エラー: マスターが存在しない
    ]);
}
```

**修正後**:
```php
public function test_addItem()
{
    $user = $this->createUsrUser();
    MstItem::factory()->create(['id' => 'item_1']);  // ✅ 追加

    UsrItem::factory()->create([
        'usr_user_id' => $user->getId(),
        'mst_item_id' => 'item_1',  // 成功
    ]);
}
```

### ケース1-3: 複数の外部キー依存

**修正手順**: 依存関係の順に作成

```php
public function test_複数外部キー()
{
    // 1. マスターデータ作成
    MstCharacter::factory()->create(['id' => 'char_1']);
    MstSkill::factory()->create(['id' => 'skill_1']);

    // 2. ユーザーデータ作成
    $user = $this->createUsrUser();

    // 3. ユーザー関連データ作成
    UsrCharacter::factory()->create([
        'usr_user_id' => $user->getId(),
        'mst_character_id' => 'char_1',
    ]);

    // 4. さらに依存するデータ作成
    UsrCharacterSkill::factory()->create([
        'usr_user_id' => $user->getId(),
        'mst_character_id' => 'char_1',
        'mst_skill_id' => 'skill_1',
    ]);
}
```

## 修正パターン2: ユニーク制約違反

### ケース2-1: 主キー重複

**エラー**:
```
SQLSTATE[23000]: Integrity constraint violation: Duplicate entry 'user_123' for key 'PRIMARY'
```

**原因**: 同じIDのデータを複数回作成している

**修正前**:
```php
public function test_example()
{
    $user1 = UsrUser::factory()->create(['id' => 'user_123']);
    $user2 = UsrUser::factory()->create(['id' => 'user_123']);  // エラー: ID重複
}
```

**修正後**:
```php
public function test_example()
{
    $user1 = UsrUser::factory()->create(['id' => 'user_123']);
    $user2 = UsrUser::factory()->create(['id' => 'user_456']);  // ✅ 別のID
}
```

### ケース2-2: ユニークカラム重複

**エラー**:
```
SQLSTATE[23000]: Integrity constraint violation: Duplicate entry 'test@example.com' for key 'users_email_unique'
```

**修正前**:
```php
public function test_example()
{
    $user1 = UsrUser::factory()->create(['email' => 'test@example.com']);
    $user2 = UsrUser::factory()->create(['email' => 'test@example.com']);  // エラー
}
```

**修正後**:
```php
public function test_example()
{
    $user1 = UsrUser::factory()->create(['email' => 'test1@example.com']);
    $user2 = UsrUser::factory()->create(['email' => 'test2@example.com']);  // ✅ 別のメール
}
```

## 修正パターン3: テーブル・カラム不存在

### ケース3-1: マイグレーション未実行

**エラー**:
```
SQLSTATE[42S02]: Base table or view not found: Table 'localDB.usr_new_table' doesn't exist
```

**原因**: マイグレーションが実行されていない

**解決策**:
```bash
# マイグレーション実行
sail migrate
sail admin migrate  # adminディレクトリの場合

# テスト実行
sail phpunit
```

### ケース3-2: カラム不存在

**エラー**:
```
SQLSTATE[42S22]: Column not found: Unknown column 'new_column' in 'field list'
```

**原因1**: マイグレーションが実行されていない

**解決策**:
```bash
sail migrate
```

**原因2**: カラム名のスペルミス

**修正前**:
```php
UsrUser::factory()->create([
    'new_collumn' => 'value',  // スペルミス: collumn → column
]);
```

**修正後**:
```php
UsrUser::factory()->create([
    'new_column' => 'value',  // ✅ 正しいスペル
]);
```

## 修正パターン4: saveAll()実行漏れ

### ケース4-1: Service/Repositoryテストでの実行漏れ

**エラー**:
```
Failed asserting that 0 matches expected 1.
（データが保存されていない）
```

**原因**: saveAll()を実行していない

**修正前**:
```php
public function test_apply_アイテム使用()
{
    $user = $this->createUsrUser();
    $this->itemService->addItem($user->getId(), 'item_1', 10);
    // saveAll()を実行していない

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    $this->assertCount(1, $items);  // エラー: 0件
}
```

**修正後**:
```php
public function test_apply_アイテム使用()
{
    $user = $this->createUsrUser();
    $this->itemService->addItem($user->getId(), 'item_1', 10);
    $this->saveAll();  // ✅ 追加

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    $this->assertCount(1, $items);  // 成功
}
```

**注意**: UseCase/Controllerテストでは`saveAll()`は不要。

### ケース4-2: sendRewards()の実行漏れ

**エラー**:
```
Failed asserting that 0 matches expected 5.
（報酬が送付されていない）
```

**修正前**:
```php
public function test_報酬送付()
{
    $user = $this->createUsrUser();
    $this->rewardService->apply($user->getId(), 'item_1', 5);
    // sendRewards()を実行していない
    $this->saveAll();

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    $this->assertCount(1, $items);  // エラー: 0件
}
```

**修正後**:
```php
public function test_報酬送付()
{
    $user = $this->createUsrUser();
    $this->rewardService->apply($user->getId(), 'item_1', 5);
    $this->sendRewards($user->getId());  // ✅ 追加
    $this->saveAll();

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    $this->assertCount(1, $items);  // 成功
}
```

## 修正パターン5: トランザクション関連

### ケース5-1: 複数リクエストでのデータ共有

**エラー**:
```
Model already exists in manager
```

**原因**: Scenarioテストで`resetAppForNextRequest()`を実行していない

**修正前**:
```php
public function test_シナリオ()
{
    // 1回目のリクエスト
    $response1 = $this->postJson($url, $params);
    // resetAppForNextRequest()を実行していない

    // 2回目のリクエスト
    $response2 = $this->postJson($url, $params);  // エラー
}
```

**修正後**:
```php
public function test_シナリオ()
{
    // 1回目のリクエスト
    $response1 = $this->postJson($url, $params);
    $this->resetAppForNextRequest($usrUserId);  // ✅ 追加

    // 2回目のリクエスト
    $response2 = $this->postJson($url, $params);  // 成功
}
```

## デバッグ手順

### ステップ1: SQLSTATEコードを確認

```
SQLSTATE[23000]: Integrity constraint violation:
Foreign key constraint fails (`usr_items`, CONSTRAINT `usr_items_usr_user_id_foreign`)
```

**確認事項**:
- コード: 23000（制約違反）
- テーブル: usr_items
- 制約: usr_items_usr_user_id_foreign

### ステップ2: クエリログを確認

```php
public function test_example()
{
    \DB::enableQueryLog();

    try {
        UsrItem::factory()->create([
            'usr_user_id' => 'user_123',
            'mst_item_id' => 'item_1',
        ]);
    } catch (\Exception $e) {
        dump('Exception:', $e->getMessage());
        dump('Queries:', \DB::getQueryLog());
        throw $e;
    }
}
```

### ステップ3: DBの状態を確認

```php
// 外部キー参照先のデータを確認
dump('Users:', UsrUser::all()->toArray());
dump('Master items:', MstItem::all()->toArray());
```

### ステップ4: 原因を特定

- ユーザーが存在しない → **修正パターン1-1**
- マスターが存在しない → **修正パターン1-2**
- テーブルが存在しない → **修正パターン3-1**
- saveAll()漏れ → **修正パターン4-1**

### ステップ5: 修正して確認

修正後、dump()を削除してテストを再実行。

## よくある間違い

### 間違い1: データ作成順序

```php
// ❌ 間違い: 子データを先に作成
UsrItem::factory()->create(['usr_user_id' => 'user_123']);
$user = $this->createUsrUser(['id' => 'user_123']);  // 遅い

// ✅ 正しい: 親データを先に作成
$user = $this->createUsrUser(['id' => 'user_123']);
UsrItem::factory()->create(['usr_user_id' => $user->getId()]);
```

### 間違い2: RefreshDatabaseの重複

```php
// ❌ 間違い: RefreshDatabaseを重複インポート
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;  // 親クラスで既に使用
}

// ✅ 正しい: 親クラスに任せる
class ExampleTest extends TestCase
{
    // RefreshDatabaseは親クラス(TestCase)で使用済み
}
```

### 間違い3: saveAll()の位置

```php
// ❌ 間違い: saveAll()の後にDBを確認
$this->itemService->addItem($userId, 'item_1', 10);
$items = UsrItem::where('usr_user_id', $userId)->get();  // 0件
$this->saveAll();  // 遅い

// ✅ 正しい: saveAll()の前にDBを確認
$this->itemService->addItem($userId, 'item_1', 10);
$this->saveAll();
$items = UsrItem::where('usr_user_id', $userId)->get();  // 1件
```

## マイグレーション関連

### マイグレーション実行コマンド

```bash
# apiディレクトリのマイグレーション
sail migrate

# adminディレクトリのマイグレーション
sail admin migrate

# マイグレーション状態確認
sail migrate:status
sail admin migrate:status

# マイグレーションリセット（全削除して再実行）
sail migrate:fresh
sail admin migrate:fresh
```

### マイグレーション未実行の確認

```bash
# 未実行のマイグレーションを確認
sail migrate:status | grep Pending
```

## チェックリスト

### エラー発生時
- [ ] SQLSTATEコードを確認
- [ ] エラーメッセージから制約/テーブル名を確認
- [ ] クエリログで実行されたSQLを確認

### 修正前
- [ ] 外部キー参照先のデータが存在するか確認
- [ ] データ作成順序が正しいか確認
- [ ] マイグレーションが実行済みか確認
- [ ] saveAll()/sendRewards()を実行しているか確認

### 修正後
- [ ] dump()、クエリログを削除
- [ ] テストを再実行して成功を確認
- [ ] 他のテストに影響がないか確認

### コミット前
- [ ] 全テストがPASS
- [ ] マイグレーションファイルがコミットされている
- [ ] デバッグ用コードが残っていない
