# テストコマンドガイド

## 基本コマンド

### ✅ 正しいコマンド実行方法

**重要**: glow-serverルートディレクトリで実行。`cd`不要。

```bash
# 全テスト実行
sail phpunit

# 特定ディレクトリ/ファイル
sail phpunit tests/Unit
sail phpunit tests/Feature/Domain/Item
sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php

# 特定メソッド
sail phpunit --filter test_exec_正常動作
sail phpunit --filter "test_exec_正常動作|test_exec_エラー"

# テストスイート指定
sail phpunit --testsuite Unit
sail phpunit --testsuite Feature
```

### ❌ やってはいけないこと

| ❌ 誤り | ✅ 正しい |
|--------|---------|
| `cd api && php artisan test` | `sail phpunit` |
| `docker compose exec php php artisan test` | `sail phpunit` |
| `./tools/bin/sail-wp test` | `sail phpunit` |

## フィルタリング

### クラス名でフィルタ

```bash
# 完全一致
sail phpunit --filter ItemServiceTest

# 部分一致（複数クラスを一度に実行）
sail phpunit --filter ServiceTest
```

### メソッド名でフィルタ

```bash
# 単一メソッド
sail phpunit --filter test_apply_アイテム使用

# 複数メソッド（OR条件）
sail phpunit --filter "test_apply_アイテム使用|test_apply_エラー"
```

### ファイルパス指定

```bash
# ディレクトリ指定
sail phpunit tests/Feature/Domain/Item

# ファイル指定
sail phpunit tests/Feature/Domain/Item/ItemServiceTest.php
```

### 組み合わせ

```bash
# ファイル + メソッド
sail phpunit tests/Feature/Domain/Item/ItemServiceTest.php --filter test_apply_アイテム使用
```

## 便利なオプション

### --stop-on-failure

最初の失敗で停止（デバッグ時に便利）

```bash
sail phpunit --stop-on-failure
```

### --verbose

詳細な出力を表示

```bash
sail phpunit --verbose
```

### --testdox

失敗時の詳細を表形式で表示

```bash
sail phpunit --testdox
```

### --coverage（カバレッジ）

```bash
# カバレッジ付き実行
sail phpunit --coverage

# カバレッジ100%未満のみ表示
sail phpunit --coverage | grep -v '100.0 %'
```

### --profile

最も遅いテスト10件を表示

```bash
sail phpunit --profile
```

### メモリ制限変更

```bash
# メモリ制限を1GBに増やす
sail phpunit -d memory_limit=1G
```

## 実行戦略

### 開発中の推奨フロー

```
開発中:   sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php
  ↓
確認時:   sail phpunit tests/Unit/Auth
  ↓
コミット前: sail phpunit
```

### エラー調査時の推奨フロー

```bash
# 1. 全テスト実行してエラー確認
sail phpunit

# 2. 失敗したテストを単独実行
sail phpunit --filter ItemServiceTest

# 3. さらに絞り込み（メソッド単位）
sail phpunit --filter test_apply_アイテム使用

# 4. デバッグ用オプション追加
sail phpunit --filter test_apply_アイテム使用 --stop-on-failure --verbose
```

## 実行結果の読み方

### 成功時

```
Tests:    100 passed (150 assertions)
Duration: 12.34s
```

### 失敗時

```
FAILED  Tests\Feature\Domain\Item\ItemServiceTest > test_apply_アイテム使用
  Failed asserting that 0 matches expected 5.

  at tests/Feature/Domain/Item/ItemServiceTest.php:45
    41▕     $this->itemService->addItem($user->getId(), 'item_1', 10);
    42▕     // saveAll()を実行していない
    43▕
    44▕     $items = UsrItem::where('usr_user_id', $user->getId())->get();
  ➜ 45▕     $this->assertCount(1, $items);  // エラー
    46▕ }

Tests:    1 failed, 99 passed (149 assertions)
Duration: 12.34s
```

**読み方**:
- `FAILED` - 失敗したテストクラスとメソッド
- `Failed asserting that...` - 失敗理由
- `at tests/...` - 失敗した行番号
- コードスニペット - 失敗箇所の前後5行
- `➜` - 失敗した行
- `Tests: X failed, Y passed` - サマリー

## よくある問題

### 問題1: テストが実行されない

**原因**: メソッド名が`test_`で始まっていない

**解決策**:
```php
// ❌ 実行されない
public function example()
{
    // ...
}

// ✅ 実行される
public function test_example()
{
    // ...
}
```

### 問題2: 特定のテストのみスキップしたい

**方法1: @doesNotPerformAssertionsアノテーション**

```php
/**
 * @doesNotPerformAssertions
 */
public function test_skip_this()
{
    // スキップされる（アサーション不要の警告を抑制）
}
```

**方法2: markTestSkipped()**

```php
public function test_example()
{
    $this->markTestSkipped('This test is temporarily disabled');
}
```

### 問題3: メモリ不足エラー

**エラー**: `Allowed memory size exhausted`

**解決策**:
```bash
sail phpunit -d memory_limit=1G
```

### 問題4: テスト間でデータが残る

**原因**: `RefreshDatabase`が適切に動作していない

**解決策**:
```php
// ❌ RefreshDatabaseを重複インポート
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;  // 親クラスで既に使用済み
}

// ✅ 親クラスに任せる
class ExampleTest extends TestCase
{
    // RefreshDatabaseは親クラス(TestCase)で使用済み
}
```

## 高度な使い方

### 並列実行（※実験的機能）

```bash
# 並列実行でテスト時間を短縮
sail phpunit --parallel
```

**注意**: データベース競合が発生する可能性あり。

### カバレッジレポート生成

```bash
# HTMLレポート生成
sail phpunit --coverage-html coverage

# ブラウザで確認
open coverage/index.html
```

### データベースの再作成

```bash
# テスト実行前にDBを再作成
sail phpunit --recreate-databases

# テスト実行後にDBを削除
sail phpunit --drop-databases
```

## チェックリスト

### コマンド実行前
- [ ] glow-serverルートディレクトリにいる
- [ ] Dockerコンテナが起動中（`docker compose ps`で確認）
- [ ] 最新のマイグレーションを実行済み

### フィルタリング時
- [ ] クラス名/メソッド名のスペルミスがない
- [ ] 正規表現でOR条件を使う場合はダブルクォートで囲む

### エラー調査時
- [ ] --stop-on-failureで最初のエラーに集中
- [ ] --filterで対象を絞り込み
- [ ] --verboseで詳細情報を取得

### カバレッジ確認時
- [ ] --coverageオプション付きで実行
- [ ] 100%未満の箇所を確認
- [ ] 重要なロジックがカバーされているか確認
