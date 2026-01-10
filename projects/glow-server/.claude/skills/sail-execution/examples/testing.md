# テスト実行コマンド実行例

glow-serverのPHPUnitテストをsailコマンドで実行する方法。

## 重要な前提

- **実行場所**: glow-serverルートディレクトリ
- **cd禁止**: `cd api`や`cd admin`は使わない
- **API用**: `sail test`または`sail phpunit`
- **Admin用**: `sail admin test`または`sail admin phpunit`

---

## 基本実行

### 全テスト実行

```bash
# API全テスト実行
sail test

# または
sail phpunit

# Admin全テスト実行
sail admin test

# または
sail admin phpunit
```

### カバレッジ付き実行

```bash
# API全テスト実行（カバレッジ付き）
sail test --coverage

# Admin全テスト実行（カバレッジ付き）
sail admin test --coverage
```

---

## フィルタ実行

### 特定ディレクトリのみ実行

```bash
# Unitテストのみ
sail phpunit tests/Unit

# Featureテストのみ
sail phpunit tests/Feature

# 特定ディレクトリのみ
sail phpunit tests/Unit/Auth
sail phpunit tests/Feature/Encyclopedia
```

### 特定ファイルのみ実行

```bash
# 特定テストファイル
sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php

# 複数ファイル
sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php tests/Unit/Auth/SignInUseCaseTest.php
```

### 特定メソッドのみ実行

```bash
# --filterオプションで特定メソッド
sail phpunit --filter test_exec_正常動作

# 複数メソッドをOR条件で実行（|で繋ぐ）
sail phpunit --filter "test_exec_正常動作|test_exec_エラー"

# クラス名でフィルタ
sail phpunit --filter SignUpUseCaseTest
sail phpunit --filter "SignUpUseCaseTest|SignInUseCaseTest"
```

### テストスイート指定

```bash
# Unitテストスイート
sail phpunit --testsuite Unit

# Featureテストスイート
sail phpunit --testsuite Feature
```

---

## よくある使い方

### 開発中の典型的なフロー

```bash
# 1. 特定メソッドのみテスト（開発中）
sail phpunit --filter test_exec_正常動作 tests/Unit/Auth/SignUpUseCaseTest.php

# 2. 特定ファイル全体をテスト
sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php

# 3. 特定ディレクトリ全体をテスト
sail phpunit tests/Unit/Auth

# 4. コミット前に全テスト実行
sail test
```

### エラー発生時のデバッグ

```bash
# 詳細出力
sail phpunit --verbose tests/Unit/Auth/SignUpUseCaseTest.php

# 失敗時の詳細表示
sail phpunit --testdox tests/Unit/Auth/SignUpUseCaseTest.php

# 最初の失敗で停止
sail phpunit --stop-on-failure

# メモリ制限を増やす
sail phpunit -d memory_limit=1G
```

---

## 間違い例

### よくある間違い

```bash
# ❌ cd api は不要
cd api && php artisan test

# ❌ cd api してから相対パス実行は不要
cd api && ../tools/bin/sail-wp test

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php artisan test

# ❌ ./tools/bin/sail-wp でも動くが、sailエイリアスを推奨
./tools/bin/sail-wp test

# ✅ 正しい
sail test
```

### パス指定の間違い

```bash
# ❌ api/を含めてしまう
sail phpunit api/tests/Unit/Auth/SignUpUseCaseTest.php

# ❌ 絶対パス
sail phpunit /Users/username/.../glow-server/api/tests/Unit/Auth/SignUpUseCaseTest.php

# ✅ 正しい（glow-serverルートからの相対パス）
sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php
```

---

## sailコマンドの仕組み

### sail test の内部動作

`sail test`は内部的に以下を実行します：

```bash
# tools/bin/sail-wpスクリプト内（238-250行目）
docker compose exec \
  -e XDEBUG_MODE=debug,coverage \
  -e TEST_RECEIPT -e APPSTORE_BUNDLE_ID_PRODUCTION -e APPSTORE_BUNDLE_ID_SANDBOX \
  -e GOOGLEPLAY_PACKAGE_NAME -e GOOGLEPLAY_PURCHASE_CREDENTIAL -e GOOGLEPLAY_PUBKEY \
  -e GOOGLEPLAY_PURCHASE_CREDENTIAL_ENV -e GOOGLEPLAY_PUBKEY_ENV \
  php php artisan test [引数]
```

重要な点：
- **XDEBUG_MODE=debug,coverage**: カバレッジ計測用の環境変数が自動設定
- **各種環境変数**: テストに必要な環境変数が自動注入
- **phpコンテナ**: 自動的にphpまたはphp-datadogコンテナで実行

### sail phpunit の内部動作

`sail phpunit`は内部的に以下を実行します：

```bash
# tools/bin/sail-wpスクリプト内（253-262行目）
docker compose exec \
  -e XDEBUG_MODE=debug,coverage \
  php php vendor/bin/phpunit [引数]
```

### sail test vs sail phpunit

| コマンド | 内部実行 | 用途 |
|---------|---------|------|
| `sail test` | `php artisan test` | Laravel標準のテストランナー（推奨） |
| `sail phpunit` | `php vendor/bin/phpunit` | PHPUnitを直接実行 |

**推奨**: 基本的には`sail test`を使用。`sail phpunit`は特定のPHPUnitオプションが必要な場合のみ。

---

## オプション一覧

### よく使うオプション

| オプション | 説明 | 例 |
|----------|------|-----|
| `--filter` | 特定のテストメソッド/クラスのみ実行 | `sail phpunit --filter test_exec_正常動作` |
| `--testsuite` | 特定のテストスイートのみ実行 | `sail phpunit --testsuite Unit` |
| `--coverage` | カバレッジレポート表示 | `sail test --coverage` |
| `--verbose` | 詳細出力 | `sail phpunit --verbose` |
| `--testdox` | 失敗時の詳細表示 | `sail phpunit --testdox` |
| `--stop-on-failure` | 最初の失敗で停止 | `sail phpunit --stop-on-failure` |
| `-d memory_limit=` | メモリ制限変更 | `sail phpunit -d memory_limit=1G` |

### カバレッジ関連

```bash
# コンソールにカバレッジ表示
sail test --coverage

# HTMLレポート生成
sail phpunit --coverage-html coverage

# 生成されたHTMLを確認
open coverage/index.html
```

---

## 実行フロー例

### 新規テスト開発時

```bash
# 1. 特定メソッドのみテスト（最小範囲）
sail phpunit --filter test_exec_正常動作 tests/Unit/Auth/SignUpUseCaseTest.php

# 2. 同じファイルの全テストを実行
sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php

# 3. 同じディレクトリの全テストを実行
sail phpunit tests/Unit/Auth

# 4. 全Unitテストを実行
sail phpunit --testsuite Unit

# 5. 全テストを実行
sail test
```

### テスト失敗時のデバッグ

```bash
# 1. 詳細出力で再実行
sail phpunit --verbose --testdox tests/Unit/Auth/SignUpUseCaseTest.php

# 2. 単独実行で成功するか確認
sail phpunit --filter test_exec_正常動作 tests/Unit/Auth/SignUpUseCaseTest.php

# 3. メモリ不足の場合
sail phpunit -d memory_limit=1G tests/Unit/Auth/SignUpUseCaseTest.php
```

### コミット前の確認

```bash
# 全チェックを実行
./tools/code_check.sh

# または個別に実行
sail phpcbf
sail phpcs
sail phpstan
sail deptrac
sail test --coverage
```

---

## よくあるエラーと解決策

### メモリ不足エラー

```bash
# エラー: Allowed memory size exhausted
# 解決策: メモリ制限を増やす
sail phpunit -d memory_limit=1G
```

### テスト間データ残存エラー

```php
// エラー: データベースの状態が不正
// 解決策: RefreshDatabaseトレイトを使用
use Illuminate\Foundation\Testing\RefreshDatabase;

class SignUpUseCaseTest extends TestCase
{
    use RefreshDatabase;
    // ...
}
```

### Model already exists エラー

```php
// エラー: Model already exists
// 解決策: resetAppForNextRequest()を使用
resetAppForNextRequest($usrUserId);
```

---

## まとめ

**全てのコマンドはglow-serverルートから実行:**

```bash
# ✅ 正しい実行方法
sail test
sail phpunit tests/Unit/Auth/SignUpUseCaseTest.php
sail phpunit --filter test_exec_正常動作

# ❌ 絶対にやってはいけないこと
cd api && php artisan test
cd api && ../tools/bin/sail-wp test
docker compose exec php php artisan test
```

**API用とAdmin用の使い分け:**

```bash
# API用
sail test
sail phpunit tests/Unit

# Admin用
sail admin test
sail admin phpunit tests/Unit
```

**推奨フロー:**

```
開発中:   sail phpunit --filter test_exec_正常動作 tests/Unit/Auth/SignUpUseCaseTest.php
  ↓
確認時:   sail phpunit tests/Unit/Auth
  ↓
コミット前: sail test
```
