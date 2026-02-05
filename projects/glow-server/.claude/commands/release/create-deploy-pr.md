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
# コミット差分を取得（マージコミットも含める）
# 重要: --no-merges を使用しないこと（マージコミット経由のPR番号を見逃す原因になる）
git log origin/${BASE_BRANCH}..origin/${SOURCE_BRANCH} --oneline
```

この差分から以下を抽出します：

#### 3-1. PR番号の抽出

コミットメッセージから以下の2パターンでPR番号を抽出します：
- `(#数字)` パターン（通常のコミット）
- `Merge pull request #数字` パターン（マージコミット）

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
gh pr view {PR番号} --repo Wonderplanet/glow-server --json number,title,body,files
```

**除外すべきPR:**
- PRタイトルが `vX.X.X ← vX.X.X` パターンのもの（前バージョンからのマージPR）
  - 例: `v1.5.1 ← v1.5.0`, `v1.6.0 ← v1.5.0` など
  - これらは前バージョンで既に含まれている変更なので、対象から除外する

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

- #2094
- #2135
- #2138
...（箇条書きで列挙）

## 変更カテゴリ

### API
- #2138: {PRタイトル}
- #2154: {PRタイトル}

### Admin
- #2159: {PRタイトル}
- #2160: {PRタイトル}

### Migration

#### mst DB
- #2138: {PRタイトル}
  - `mst_table1`: カラム追加 (`column_name`)
  - `mst_table2`: 新規テーブル追加

#### usr DB
- #2138: {PRタイトル}
  - `usr_table1`: Enum要素変更 (`status`: 'new_value' 追加)

#### adm DB
- #2167: {PRタイトル}
  - `adm_table1`: カラム追加 (`column_name`)

### Other
- #2094: {PRタイトル}
- #2135: {PRタイトル}

---

## ● 変更内容まとめ

### • テーブル追加
- usr_webstore_infos
- usr_webstore_transactions

### • 列追加
- mst_store_products.product_id_webstore
- usr_currency_summaries.paid_amount_share

### • 列変更
- adm_bank_f002.platform_id: ENUMに'asb'を追加
- mst_box_gacha_prizes.resource_type: ENUMに'Emblem'を追加

---
🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

**変更内容まとめセクションのルール:**
- **テーブル追加**: 新規作成されたテーブル名を列挙
- **列追加**: `テーブル名.カラム名` 形式で列挙
- **列変更**: NULL許容変更、ENUM要素追加、カラム名変更などを `テーブル名.カラム名: 変更内容` 形式で記載

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

### 前バージョンマージPRの除外

PRタイトルが `vX.X.X ← vX.X.X` パターン（例: `v1.5.1 ← v1.5.0`）のものは、前バージョンからのマージPRなので**必ず除外**してください。これらは既に前バージョンで含まれている変更です。

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
