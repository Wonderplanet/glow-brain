# テスト実行・デバッグガイド

## 実行コマンド

### ❌ やってはいけないこと / ✅ 正しい実行方法

| ❌ 誤り | ✅ 正しい |
|--------|---------|
| `cd api && php artisan test` | `sail phpunit` |
| `docker compose exec php php artisan test` | `sail phpunit` |
| `./tools/bin/sail-wp test` | `sail phpunit` |

**重要:** glow-serverルートディレクトリで実行。`cd`不要。

### 基本コマンド

```bash
# 全テスト実行
sail phpunit

# 特定ディレクトリ/ファイル
sail phpunit tests/Unit
sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php

# 特定メソッド
sail phpunit --filter test_exec_正常動作

# 複数テストをOR条件で実行（|で繋ぐ）
sail phpunit --filter "test_exec_正常動作|test_exec_エラー"
sail phpunit --filter "SignUpUseCaseTest|SignInUseCaseTest"

# テストスイート指定
sail phpunit --testsuite Unit
sail phpunit --testsuite Feature
```

### 便利なオプション

| オプション | 説明 |
|----------|------|
| `--verbose` | 詳細出力 |
| `--testdox` | 失敗時の詳細表示 |
| `--stop-on-failure` | 最初の失敗で停止 |
| `--coverage-html coverage` | カバレッジHTML生成 |

## 実行フロー

```
開発中:   sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php
  ↓
確認時:   sail phpunit tests/Unit/Auth
  ↓
コミット前: sail phpunit
```

## デバッグ方法

### 1. アサーション失敗

```php
// デバッグ出力
dump($result);        // 処理継続
dd($result);          // 処理停止
dump($var1, $var2);   // 複数変数
```

### 2. データベース不整合

```php
// DB状態確認
dump(UsrItem::all()->toArray());

// クエリログ
DB::enableQueryLog();
// ... テスト実行 ...
dump(DB::getQueryLog());
```

### 3. Mock呼び出し不一致

```php
// デバッグ用に呼び出し確認
$mock->shouldReceive('apply')
    ->andReturnUsing(function (...$args) {
        dump('apply called with:', $args);
        return true;
    });
```

### 4. 例外未発生

```php
try {
    $useCase->exec($params);
    dump('No exception thrown');
} catch (GameException $e) {
    dump('Exception:', $e->getMessage());
}
```

## よくあるエラー

| エラー | 原因 | 解決策 |
|-------|------|--------|
| Memory exhausted | メモリ不足 | `sail phpunit -d memory_limit=1G` |
| テスト間データ残存 | RefreshDatabase未使用 | `use RefreshDatabase;` |
| Model already exists | 状態保持エラー | `resetAppForNextRequest($usrUserId)` |

## デバッグチェックリスト

- [ ] エラーメッセージを正確に読む
- [ ] 単独実行で成功するか確認
- [ ] デバッグ出力でデータ確認（`dump()`/`dd()`）
- [ ] データベース状態を確認
- [ ] モック呼び出しを確認
- [ ] 時間固定（`fixTime()`）を確認
- [ ] データ保存（`saveAll()`）を確認（Service/Repositoryテスト時）
- [ ] `resetAppForNextRequest()`を確認（複数リクエスト時）
- [ ] 既存の類似テストと比較
