# ワークフロー: テスト実行から修正完了まで

## 全体フロー

```
┌─────────────────────┐
│ 1. テスト実行       │
│ sail phpunit        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ 2. エラー分析       │ ← エラーメッセージから失敗パターンを特定
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ 3. 修正実行         │ ← パターン別の修正方法を適用
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ 4. 再実行           │
│ sail phpunit        │
└──────────┬──────────┘
           │
           ▼
       全テスト成功？
       Yes → 完了
       No  → 2に戻る
```

## ステップ1: テスト実行

### 初回実行

```bash
# 全テスト実行
sail phpunit
```

**出力例**:
```
FAILED  Tests\Feature\Domain\Item\ItemServiceTest > test_apply_アイテム使用
  Failed asserting that 0 matches expected 5.

  at tests/Feature/Domain/Item/ItemServiceTest.php:45

FAILED  Tests\Feature\Domain\User\UserServiceTest > test_getUser
  RuntimeException: User not found

  at app/Domain/User/Services/UserService.php:23

Tests:  2 failed, 98 passed (150 assertions)
Duration: 12.34s
```

### 特定テストのみ実行（デバッグ時）

```bash
# 失敗したテストのみ実行
sail phpunit --filter ItemServiceTest
sail phpunit --filter test_apply_アイテム使用
```

## ステップ2: エラー分析

### エラーメッセージから失敗パターンを特定

| エラーメッセージキーワード | 失敗パターン | 参照ドキュメント |
|--------------------------|------------|----------------|
| `Failed asserting that X matches expected Y` | アサーション失敗 | [patterns/assertion-failures.md](patterns/assertion-failures.md) |
| `Exception:`, `Error:`, `Fatal error:` | 例外・エラー | [patterns/exception-errors.md](patterns/exception-errors.md) |
| `SQLSTATE`, `Integrity constraint violation` | DB関連エラー | [patterns/database-errors.md](patterns/database-errors.md) |
| `NoMatchingExpectationException`, `shouldReceive` | モック期待値不一致 | [patterns/mock-errors.md](patterns/mock-errors.md) |

### エラー優先順位

**優先度1（即修正）:**
- セットアップエラー（setUp()の問題）
- データベース制約違反
- 依存データ不足

**優先度2（パターン別修正）:**
- アサーション失敗
- モック期待値不一致
- 例外エラー

**優先度3（後回し可）:**
- カバレッジ不足
- パフォーマンス警告

## ステップ3: 修正実行

### 修正の基本方針

#### 方針1: テストデータを修正

**適用ケース**: 期待値は正しいがテストデータが不足/不正

```php
// 修正前
$user = $this->createUsrUser();
$this->itemService->addItem($user->getId(), 'item_1', 10);
// saveAll()が実行されていない

$items = UsrItem::where('usr_user_id', $user->getId())->get();
$this->assertCount(1, $items);  // エラー: 0件

// 修正後
$user = $this->createUsrUser();
$this->itemService->addItem($user->getId(), 'item_1', 10);
$this->saveAll();  // ✅ 追加

$items = UsrItem::where('usr_user_id', $user->getId())->get();
$this->assertCount(1, $items);  // 成功
```

#### 方針2: 期待値を修正

**適用ケース**: 実装は正しいがテストの期待値が間違っている

```php
// 修正前
$this->assertEquals(5, $result);  // エラー: 実際は10

// 修正後
$this->assertEquals(10, $result);  // 成功
```

#### 方針3: 実装コードを修正

**適用ケース**: 実装にバグがある

```php
// 修正前（実装コード）
public function calculateTotal(array $items): int
{
    return count($items);  // バグ: 合計ではなく個数を返している
}

// 修正後
public function calculateTotal(array $items): int
{
    return array_sum(array_column($items, 'amount'));
}
```

### 一括修正のコツ

#### パターン1: 同じエラーが複数ある場合

```bash
# エラーメッセージをgrepして一括確認
sail phpunit 2>&1 | grep "saveAll"
```

該当箇所を一括で修正。

#### パターン2: 依存関係のあるエラー

setUp()の問題など、共通の原因で複数のテストが失敗している場合は、
根本原因を先に修正すると複数のエラーが一度に解消される。

## ステップ4: 再実行と検証

### 修正後の確認手順

```bash
# 1. 修正したテストのみ実行
sail phpunit --filter ItemServiceTest

# 成功したら全テスト実行
sail phpunit

# 全テスト成功を確認
Tests:  100 passed (150 assertions)
Duration: 12.34s
```

### 完了判定基準

- [ ] `sail phpunit`が全テストPASSで完了
- [ ] エラー・警告が0件
- [ ] Duration（実行時間）が極端に長くない（目安: 30秒以内）

### トラブルシューティング

**問題1: 一部のテストが intermittent に失敗する**

**原因**: 時間依存のロジック、テスト間のデータ共有

**解決策**:
- `fixTime()`で時間を固定
- `RefreshDatabase`でDBをリセット
- テスト間で状態を共有しない

**問題2: 修正しても同じエラーが出る**

**原因**: キャッシュが残っている

**解決策**:
```bash
# アプリケーションキャッシュクリア
sail artisan cache:clear
sail artisan config:clear

# Dockerコンテナ再起動
docker compose restart php
```

**問題3: メモリ不足エラー**

**エラー**: `Allowed memory size exhausted`

**解決策**:
```bash
# メモリ制限を増やして実行
sail phpunit -d memory_limit=1G
```

## デバッグ時の推奨フロー

```
1. 失敗したテストを単独実行
   ↓
2. dump()/dd()でデバッグ出力
   ↓
3. 原因を特定
   ↓
4. 修正
   ↓
5. 単独テストで確認
   ↓
6. 全テストで確認
```

詳細は **[guides/debugging-methods.md](guides/debugging-methods.md)** を参照。

## チェックリスト

### テスト実行前
- [ ] 最新のマイグレーションを実行済み
- [ ] 必要なマスターデータがseeded済み
- [ ] Dockerコンテナが起動中

### エラー分析時
- [ ] エラーメッセージを正確に読む
- [ ] 失敗パターンを特定
- [ ] 該当する修正パターンドキュメントを確認

### 修正実行時
- [ ] 修正方針を決定（テストデータ/期待値/実装）
- [ ] 類似エラーを一括確認
- [ ] 依存関係を考慮

### 再実行時
- [ ] 修正したテストを単独実行
- [ ] 全テストを実行
- [ ] エラー・警告が0件を確認

### 完了時
- [ ] 全テストがPASS
- [ ] コードレビュー可能な状態
- [ ] コミットメッセージ作成
