# コード品質チェックコマンド実行例

glow-serverのコード品質チェック（phpcs/phpcbf/phpstan/deptrac）をsailコマンドで実行する方法。

## 重要な前提

- **実行場所**: glow-serverルートディレクトリ
- **cd禁止**: `cd api`や`cd admin`は使わない
- **API用**: `sail`、**Admin用**: `sail admin`

---

## phpcs（コーディング規約チェック）

### 基本実行

```bash
# API全体をチェック
sail phpcs

# 特定ファイルをチェック
sail phpcs app/Http/Controllers/EncyclopediaController.php

# 特定ディレクトリをチェック
sail phpcs app/Http/Controllers
sail phpcs app/Domain/User

# Admin全体をチェック
sail admin phpcs

# Admin特定ファイルをチェック
sail admin phpcs app/Http/Controllers/AdminController.php
```

### よくある使い方

```bash
# 変更したファイルのみチェック
sail phpcs app/Http/Controllers/EncyclopediaController.php app/Domain/Encyclopedia/Services/EncyclopediaService.php

# 複数ディレクトリをチェック
sail phpcs app/Http/Controllers app/Domain/User
```

### 間違い例

```bash
# ❌ cd api は不要
cd api && ../tools/bin/sail-wp exec php vendor/bin/phpcs --standard=phpcs.xml app/Http/Controllers/EncyclopediaController.php

# ❌ 相対パス不要
cd api && php vendor/bin/phpcs app/Http/Controllers/EncyclopediaController.php

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php vendor/bin/phpcs --standard=phpcs.xml app/Http/Controllers/EncyclopediaController.php

# ✅ 正しい
sail phpcs app/Http/Controllers/EncyclopediaController.php
```

---

## phpcbf（コーディング規約自動修正）

### 基本実行

```bash
# API全体を自動修正
sail phpcbf

# 特定ファイルを自動修正
sail phpcbf app/Http/Controllers/EncyclopediaController.php

# 特定ディレクトリを自動修正
sail phpcbf app/Http/Controllers
sail phpcbf app/Domain/User

# Admin全体を自動修正
sail admin phpcbf

# Admin特定ファイルを自動修正
sail admin phpcbf app/Http/Controllers/AdminController.php
```

### よくある使い方

```bash
# 自動修正してから手動確認
sail phpcbf app/Http/Controllers/EncyclopediaController.php
sail phpcs app/Http/Controllers/EncyclopediaController.php

# 変更したファイルのみ自動修正
sail phpcbf app/Http/Controllers/EncyclopediaController.php app/Domain/Encyclopedia/Services/EncyclopediaService.php
```

### 間違い例

```bash
# ❌ cd api は不要
cd api && ../tools/bin/sail-wp exec php vendor/bin/phpcbf --standard=phpcs.xml app/Http/Controllers/EncyclopediaController.php

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php vendor/bin/phpcbf --standard=phpcs.xml app/Http/Controllers/EncyclopediaController.php

# ✅ 正しい
sail phpcbf app/Http/Controllers/EncyclopediaController.php
```

---

## phpstan（静的解析）

### 基本実行

```bash
# API全体を解析
sail phpstan

# 特定ディレクトリを解析
sail phpstan app/Http/Controllers
sail phpstan app/Domain/User

# 特定ファイルを解析
sail phpstan app/Http/Controllers/EncyclopediaController.php

# Admin全体を解析
sail admin phpstan

# Admin特定ディレクトリを解析
sail admin phpstan app/Http/Controllers
```

### よくある使い方

```bash
# 変更したディレクトリのみ解析
sail phpstan app/Domain/Encyclopedia

# 複数ディレクトリを解析
sail phpstan app/Http/Controllers app/Domain/User

# エラーレベルを指定（0-9、数字が大きいほど厳格）
sail phpstan --level=5 app/Domain/User
```

### 間違い例

```bash
# ❌ cd api は不要
cd api && ../tools/bin/sail-wp exec php vendor/bin/phpstan analyse --memory-limit=-1 app/Http/Controllers

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php vendor/bin/phpstan analyse --memory-limit=-1 app/Http/Controllers

# ✅ 正しい
sail phpstan app/Http/Controllers
```

### sailコマンドの仕組み

`sail phpstan`は内部的に以下を実行します：

```bash
# tools/bin/sail-wpスクリプト内（265-274行目）
docker compose exec php php vendor/bin/phpstan analyse --memory-limit=-1 [引数]
```

つまり、**--memory-limit=-1は自動付与**されるため、手動で指定不要です。

---

## deptrac（アーキテクチャチェック）

### 基本実行

```bash
# API全体のアーキテクチャチェック
sail deptrac

# 依存関係グラフ生成（画像ファイル作成）
sail deptrac graph

# Admin全体のアーキテクチャチェック
sail admin deptrac
```

### よくある使い方

```bash
# 通常のチェック実行
sail deptrac

# 詳細出力
sail deptrac --verbose

# 依存関係の可視化（.png/.svg画像生成）
sail deptrac graph
```

### 間違い例

```bash
# ❌ cd api は不要
cd api && ../tools/bin/sail-wp exec php vendor/bin/deptrac analyse

# ❌ Dockerコマンド直接実行は不要
docker compose exec php php vendor/bin/deptrac analyse

# ✅ 正しい
sail deptrac
```

### sailコマンドの仕組み

`sail deptrac`は内部的に以下を実行します：

```bash
# tools/bin/sail-wpスクリプト内（301-315行目）
# graph以外
docker compose exec php php vendor/bin/deptrac analyse [引数]

# graph指定時
./tools/deptrac_graph.sh [引数]
```

---

## code_check.shとの対応

`./tools/code_check.sh`は以下の順序で実行します：

```bash
# 1. 自動修正
./tools/bin/sail-wp phpcbf
# = sail phpcbf

# 2. コーディング規約チェック
./tools/bin/sail-wp phpcs
# = sail phpcs

# 3. 静的解析
./tools/bin/sail-wp phpstan
# = sail phpstan

# 4. アーキテクチャチェック
./tools/bin/sail-wp deptrac
# = sail deptrac

# 5. テスト実行
./tools/bin/sail-wp test --coverage
# = sail test --coverage
```

**個別実行例:**

```bash
# phpcsのみ実行
sail phpcs

# phpstanのみ実行
sail phpstan

# 特定ファイルのみチェック
sail phpcs app/Http/Controllers/EncyclopediaController.php
sail phpstan app/Http/Controllers/EncyclopediaController.php
```

---

## 実行フロー例

### 開発中の典型的なフロー

```bash
# 1. コード編集後、自動修正を実行
sail phpcbf app/Http/Controllers/EncyclopediaController.php

# 2. コーディング規約チェック
sail phpcs app/Http/Controllers/EncyclopediaController.php

# 3. 静的解析
sail phpstan app/Http/Controllers/EncyclopediaController.php

# 4. エラーが出なければ全体チェック
sail phpstan app/Http/Controllers
```

### コミット前の全体チェック

```bash
# 全チェックを一括実行
./tools/code_check.sh

# または個別に実行
sail phpcbf
sail phpcs
sail phpstan
sail deptrac
sail test
```

---

## まとめ

**全てのコマンドはglow-serverルートから実行:**

```bash
# ✅ 正しい実行方法
sail phpcs app/Http/Controllers/EncyclopediaController.php
sail phpcbf app/Http/Controllers/EncyclopediaController.php
sail phpstan app/Domain/Encyclopedia
sail deptrac

# ❌ 絶対にやってはいけないこと
cd api && ...
docker compose exec php ...
../tools/bin/sail-wp exec ...
```

**API用とAdmin用の使い分け:**

```bash
# API用
sail phpcs
sail phpcbf
sail phpstan
sail deptrac

# Admin用
sail admin phpcs
sail admin phpcbf
sail admin phpstan
sail admin deptrac
```
