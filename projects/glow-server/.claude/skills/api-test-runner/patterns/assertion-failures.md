# アサーション失敗の修正パターン

## 概要

アサーション失敗は「期待値」と「実際の値」が異なる場合に発生します。

**典型的なエラーメッセージ**:
```
Failed asserting that X matches expected Y.
Failed asserting that false is true.
Failed asserting that two arrays are equal.
```

## 原因分析フロー

```
1. エラーメッセージを読む
   ↓
2. 期待値と実際の値を比較
   ↓
3. 原因を特定
   ├─ テストデータの問題
   ├─ 期待値の設定ミス
   └─ 実装のバグ
   ↓
4. 適切な修正パターンを選択
```

## 修正パターン1: テストデータを修正

### ケース1-1: saveAll()の実行漏れ

**エラー**:
```
Failed asserting that 0 matches expected 1.
```

**原因**: Service/RepositoryテストでsaveAll()を実行していない

**修正前**:
```php
public function test_apply_アイテム使用()
{
    $user = $this->createUsrUser();
    $this->itemService->addItem($user->getId(), 'item_1', 10);
    // saveAll()を実行していない

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    $this->assertCount(1, $items);  // エラー: 0だが1を期待
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

**注意**: UseCase/Controllerテストでは`saveAll()`は不要（自動保存される）。

### ケース1-2: 依存データの作成漏れ

**エラー**:
```
Failed asserting that null matches expected object.
```

**原因**: マスターデータや関連データが作成されていない

**修正前**:
```php
public function test_getItem()
{
    $user = $this->createUsrUser();
    // MstItemを作成していない

    $result = $this->itemService->getItem($user->getId(), 'item_1');
    $this->assertNotNull($result);  // エラー: nullが返る
}
```

**修正後**:
```php
public function test_getItem()
{
    $user = $this->createUsrUser();
    MstItem::factory()->create(['id' => 'item_1']);  // ✅ 追加

    $result = $this->itemService->getItem($user->getId(), 'item_1');
    $this->assertNotNull($result);  // 成功
}
```

### ケース1-3: テストデータの値が間違っている

**エラー**:
```
Failed asserting that 10 matches expected 5.
```

**原因**: テストで作成したデータの値が期待値と異なる

**修正前**:
```php
public function test_getDiamond()
{
    $user = $this->createUsrUser();
    $this->createDiamond($user->getId(), 10, 0, 0);  // 10ダイヤモンド

    $result = $this->diamondService->getDiamondAmount($user->getId());
    $this->assertEquals(5, $result);  // エラー: 10だが5を期待
}
```

**修正後**:
```php
public function test_getDiamond()
{
    $user = $this->createUsrUser();
    $this->createDiamond($user->getId(), 5, 0, 0);  // ✅ 5に変更

    $result = $this->diamondService->getDiamondAmount($user->getId());
    $this->assertEquals(5, $result);  // 成功
}
```

### ケース1-4: fixTime()の使用漏れ

**エラー**:
```
Failed asserting that two DateTime objects are equal.
```

**原因**: 時間が固定されていない

**修正前**:
```php
public function test_updateLastLogin()
{
    $user = $this->createUsrUser();
    $this->userService->updateLastLogin($user->getId());

    $user->refresh();
    $this->assertEquals('2024-01-01 00:00:00', $user->last_login_at);  // エラー
}
```

**修正後**:
```php
public function test_updateLastLogin()
{
    $now = $this->fixTime('2024-01-01 00:00:00');  // ✅ 時間を固定

    $user = $this->createUsrUser();
    $this->userService->updateLastLogin($user->getId());

    $user->refresh();
    $this->assertEquals($now, $user->last_login_at);  // 成功
}
```

## 修正パターン2: 期待値を修正

### ケース2-1: 期待値の計算ミス

**エラー**:
```
Failed asserting that 15 matches expected 10.
```

**原因**: 期待値が間違っている

**修正前**:
```php
public function test_calculateTotal()
{
    $items = [
        ['amount' => 5],
        ['amount' => 10],
    ];

    $result = $this->service->calculateTotal($items);
    $this->assertEquals(10, $result);  // エラー: 5+10=15だが10を期待
}
```

**修正後**:
```php
public function test_calculateTotal()
{
    $items = [
        ['amount' => 5],
        ['amount' => 10],
    ];

    $result = $this->service->calculateTotal($items);
    $this->assertEquals(15, $result);  // ✅ 正しい期待値
}
```

### ケース2-2: アサーションメソッドの選択ミス

**エラー**:
```
Failed asserting that two arrays are identical.
```

**原因**: `assertSame()`ではなく`assertEquals()`を使うべき

**修正前**:
```php
$expected = ['id' => 1, 'name' => 'Test'];
$actual = ['id' => 1, 'name' => 'Test'];

$this->assertSame($expected, $actual);  // エラー: 型や順序が厳密に一致しない
```

**修正後**:
```php
$expected = ['id' => 1, 'name' => 'Test'];
$actual = ['id' => 1, 'name' => 'Test'];

$this->assertEquals($expected, $actual);  // ✅ 値の一致のみ確認
```

**アサーションメソッド選択ガイド**:

| メソッド | 用途 | 厳密度 |
|---------|------|--------|
| `assertEquals()` | 値の一致 | 緩い（型変換あり） |
| `assertSame()` | 値と型の一致 | 厳密（型も一致必要） |
| `assertCount()` | 配列の要素数 | - |
| `assertArrayHasKey()` | 配列キーの存在 | - |
| `assertTrue()/assertFalse()` | Boolean値 | - |
| `assertNull()/assertNotNull()` | null判定 | - |

### ケース2-3: 配列の順序を無視したい

**エラー**:
```
Failed asserting that two arrays are equal.
```

**原因**: 配列の順序が異なる

**修正前**:
```php
$expected = [1, 2, 3];
$actual = [3, 2, 1];

$this->assertEquals($expected, $actual);  // エラー: 順序が異なる
```

**修正後**:
```php
$expected = [1, 2, 3];
$actual = [3, 2, 1];

sort($expected);
sort($actual);
$this->assertEquals($expected, $actual);  // ✅ ソート後に比較
```

## 修正パターン3: 実装コードを修正

### ケース3-1: 実装のバグ

**エラー**:
```
Failed asserting that 3 matches expected 15.
```

**原因**: 実装が間違っている

**修正前（実装コード）**:
```php
public function calculateTotal(array $items): int
{
    return count($items);  // バグ: 合計ではなく個数
}
```

**修正後（実装コード）**:
```php
public function calculateTotal(array $items): int
{
    return array_sum(array_column($items, 'amount'));  // ✅ 合計を計算
}
```

**テストコード（変更なし）**:
```php
public function test_calculateTotal()
{
    $items = [
        ['amount' => 5],
        ['amount' => 10],
    ];

    $result = $this->service->calculateTotal($items);
    $this->assertEquals(15, $result);  // 成功
}
```

### ケース3-2: エッジケースの対応漏れ

**エラー**:
```
Failed asserting that null matches expected 0.
```

**原因**: null値の場合の処理が実装されていない

**修正前（実装コード）**:
```php
public function getAmount(?int $value): int
{
    return $value;  // バグ: nullをintで返せない
}
```

**修正後（実装コード）**:
```php
public function getAmount(?int $value): int
{
    return $value ?? 0;  // ✅ nullの場合は0を返す
}
```

## デバッグ手順

### ステップ1: エラーメッセージを読む

```
Failed asserting that 0 matches expected 5.

at tests/Feature/Domain/Item/ItemServiceTest.php:45
  ➜ 45▕     $this->assertEquals(5, $result);
```

**確認事項**:
- 期待値: 5
- 実際の値: 0
- 失敗箇所: 45行目

### ステップ2: dump()で変数を確認

```php
public function test_apply_アイテム使用()
{
    $user = $this->createUsrUser();
    $this->itemService->addItem($user->getId(), 'item_1', 10);
    $this->saveAll();

    $result = $this->itemService->getItemAmount($user->getId(), 'item_1');
    dump('Result:', $result);  // デバッグ出力

    $this->assertEquals(5, $result);
}
```

### ステップ3: DBの状態を確認

```php
// DBの内容を確認
$items = UsrItem::where('usr_user_id', $user->getId())->get();
dump('Items in DB:', $items->toArray());
```

### ステップ4: 原因を特定

dump()の出力から原因を特定:
- saveAll()が実行されていない → **修正パターン1-1**
- 期待値が間違っている → **修正パターン2-1**
- 実装にバグがある → **修正パターン3-1**

### ステップ5: 修正して確認

修正後、dump()を削除してテストを再実行。

## よくある間違い

### 間違い1: assertSame()とassertEquals()の混同

```php
// ❌ 厳密すぎる
$this->assertSame(10, '10');  // エラー: 型が異なる

// ✅ 適切
$this->assertEquals(10, '10');  // 成功: 値が一致
```

### 間違い2: 配列の順序を考慮していない

```php
// ❌ 順序が異なるとエラー
$expected = ['a', 'b', 'c'];
$actual = ['c', 'b', 'a'];
$this->assertEquals($expected, $actual);

// ✅ ソートして比較
sort($expected);
sort($actual);
$this->assertEquals($expected, $actual);
```

### 間違い3: 時間を固定していない

```php
// ❌ 時間が毎回変わる
$this->assertEquals('2024-01-01 00:00:00', $user->created_at);

// ✅ fixTime()で固定
$now = $this->fixTime('2024-01-01 00:00:00');
$this->assertEquals($now, $user->created_at);
```

## チェックリスト

### エラー発生時
- [ ] エラーメッセージの期待値と実際の値を確認
- [ ] 失敗箇所のファイル名・行番号を確認
- [ ] dump()で変数の内容を確認

### 修正前
- [ ] 原因を特定（テストデータ/期待値/実装）
- [ ] 適切な修正パターンを選択
- [ ] 類似エラーがないか確認

### 修正後
- [ ] dump()を削除
- [ ] テストを再実行して成功を確認
- [ ] 他のテストに影響がないか確認

### コミット前
- [ ] 全テストがPASS
- [ ] デバッグ用コードが残っていない
- [ ] コードレビュー可能な状態
