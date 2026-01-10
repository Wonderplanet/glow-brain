---
description: 指定バージョンのdevelopブランチから、QAまたはStagingリリースブランチへのデプロイPRをdraftで作成する。
argument-hint: "[バージョン] [qa|staging]"
allowed-tools: Bash(gh:*), Bash(git:*), Read
---

# リリースデプロイPR作成コマンド

指定バージョンのdevelopブランチから、QAまたはStagingリリースブランチへのデプロイPRをdraftで作成します。

## 使用方法

```
/release:create-deploy-pr [バージョン] [qa|staging]
```

### 引数

- **バージョン**: developブランチのバージョン（例: `1.3.0`）
- **base**: マージ先のリリースブランチ種別（`qa` または `staging`）

### 例

```bash
# v1.3.0のdevelopをrelease/qaにマージするPRを作成
/release:create-deploy-pr 1.3.0 qa

# v1.4.0のdevelopをrelease/stagingにマージするPRを作成
/release:create-deploy-pr 1.4.0 staging
```

## 実行フロー

引数: $ARGUMENTS

### 1. 引数の検証

引数から以下を抽出します：

```bash
VERSION=$1  # 例: 1.3.0
BASE_TYPE=$2  # qa または staging
```

以下をチェックします：
- 引数が2つ指定されているか
- `BASE_TYPE`が`qa`または`staging`であるか
- `develop/v${VERSION}`ブランチが存在するか
- `release/${BASE_TYPE}`ブランチが存在するか

### 2. ブランチ情報の取得

```bash
# ソースブランチとベースブランチ
SOURCE_BRANCH="develop/v${VERSION}"
BASE_BRANCH="release/${BASE_TYPE}"

# 最新の情報を取得
git fetch origin ${SOURCE_BRANCH}
git fetch origin ${BASE_BRANCH}
```

### 3. コミット差分の分析

```bash
# コミット差分を取得
git log origin/${BASE_BRANCH}..origin/${SOURCE_BRANCH} --oneline --no-merges
```

この差分から以下を抽出します：

#### 3-1. PR番号の抽出

コミットメッセージから`(#数字)`パターンでPR番号を抽出します。

**重要な注意点:**
- glow-serverリポジトリのPR番号のみを対象とします
- 他リポジトリ（glow-schema等）のPR番号は除外します
- PR番号の判定方法:
  - `gh pr view {番号} --repo Wonderplanet/glow-server`を実行
  - 成功すればglow-serverのPR、失敗すれば他リポジトリのPR

```bash
# PR番号のリストを作成
PR_NUMBERS=()
for num in $(コミットメッセージから抽出した番号リスト); do
  if gh pr view $num --repo Wonderplanet/glow-server &>/dev/null; then
    PR_NUMBERS+=($num)
  fi
done
```

#### 3-2. 各PRの情報取得と分類

各PR番号について、以下の情報を取得します：

```bash
# PR情報の取得
gh pr view {PR番号} --repo Wonderplanet/glow-server --json number,title,body
```

PRのタイトル、本文、変更ファイルを確認し、以下のカテゴリに分類します：

- **api**: API関連の変更（`api/`ディレクトリ）
- **admin**: 管理画面関連の変更（`admin/`ディレクトリ）
- **migration**: マイグレーション関連の変更
- **other**: その他の変更（`.claude/`, `.ai-context/`, `docs/`等）

**注意**: 1つのPRが複数のカテゴリに該当する場合があります（例: APIとmigration両方）

#### 3-3. Migration詳細の抽出

migrationカテゴリに分類されたPRについて、さらに詳細を分析します：

```bash
# マイグレーションファイルの変更を確認
gh pr view {PR番号} --repo Wonderplanet/glow-server --json files
```

以下の情報を抽出します：

**DB接頭辞ごとに分類:**
- `mst_*`: マスターDB
- `mng_*`: 管理DB
- `usr_*`: ユーザーDB
- `log_*`: ログDB
- `sys_*`: システムDB
- `adm_*`: 管理ツールDB

**変更種別:**
- 新規テーブル追加: `create_table_xxx`
- テーブル削除: `drop_table_xxx`
- カラム追加: `add_column_xxx`
- カラム削除: `drop_column_xxx`
- カラム変更: `modify_column_xxx`
- カラム名変更: `rename_column_xxx`
- Enum要素変更: ファイル内容から`ENUM`の変更を検出

マイグレーションファイルの内容を読み取って、テーブルとカラムの具体的な変更を抽出します。

### 4. PR本文の生成

以下のフォーマットでPR本文を生成します：

```markdown
## 対象PR一覧

#{PR番号1}, #{PR番号2}, #{PR番号3}, ...

## 変更カテゴリ

### API
- #{PR番号}: {PRタイトル}
- #{PR番号}: {PRタイトル}

### Admin
- #{PR番号}: {PRタイトル}
- #{PR番号}: {PRタイトル}

### Migration

#### mst DB
- #{PR番号}: {PRタイトル}
  - `mst_table1`: カラム追加 (`column_name`)
  - `mst_table2`: 新規テーブル追加

#### usr DB
- #{PR番号}: {PRタイトル}
  - `usr_table1`: Enum要素変更 (`status`: 'new_value' 追加)

#### log DB
- #{PR番号}: {PRタイトル}
  - `log_table1`: カラム削除 (`old_column`)

### Other
- #{PR番号}: {PRタイトル}

---
🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

### 5. Draft PRの作成

```bash
gh pr create --repo Wonderplanet/glow-server \
  --draft \
  --head ${SOURCE_BRANCH} \
  --base ${BASE_BRANCH} \
  --title "${VERSION} → ${BASE_TYPE^^} デプロイPR" \
  --body "$(cat <<'EOF'
{上記で生成したPR本文}
EOF
)"
```

### 6. 結果の表示

作成されたPRのURLを表示します：

```bash
echo "Draft PRを作成しました:"
gh pr view --repo Wonderplanet/glow-server --web
```

## 注意事項

### ghコマンドの必須オプション

**必ず `--repo Wonderplanet/glow-server` を指定してください。**

```bash
# 正しい例
gh pr create --repo Wonderplanet/glow-server ...
gh pr view 123 --repo Wonderplanet/glow-server ...

# 間違い（--repoがない）
gh pr create ...
```

### PR番号の判定

他リポジトリ（glow-schema等）のPR番号を誤って含めないよう、必ず`gh pr view`で存在確認を行ってください。

### Migration分析の精度

マイグレーションファイル名だけでなく、ファイル内容も確認して正確な変更内容を抽出してください。

## トラブルシューティング

### ブランチが見つからない

```bash
# リモートブランチの確認
git ls-remote --heads origin | grep develop/v
git ls-remote --heads origin | grep release/
```

### PR情報の取得エラー

```bash
# ghコマンドの認証状態を確認
gh auth status

# 再認証が必要な場合
gh auth login
```

### コミット差分が多すぎる場合

大量のPRが含まれる場合は、処理に時間がかかることがあります。必要に応じて進捗を表示してください。
