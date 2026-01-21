---
description: 現在のブランチの変更内容に基づいてPull Requestを作成または更新する。背景情報の記載をサポート。
argument-hint: "[--update] [背景情報]"
---

# PR作成コマンド

現在のブランチの変更内容に基づいてPull Requestを作成または更新します。

## 使用方法

```
/general:create-pr [オプション] [背景情報]
```

### オプション

| オプション | 説明 |
|-----------|------|
| `--update` | 既存PRのタイトル・本文を最新のコード内容で更新 |
| (なし) | 新規PR作成、または既存PRがあれば確認後に更新 |

### 例

**新規作成:**
- `/general:create-pr` - 背景情報を対話形式で入力
- `/general:create-pr CU-abc123def` - ClickUpタスクIDを指定
- `/general:create-pr https://slack.com/archives/... Slackでの依頼内容` - Slack URLと要約を指定

**既存PR更新:**
- `/general:create-pr --update` - 最新のコード内容でPRを更新（背景情報は既存PRから継承）
- `/general:create-pr --update 追加の背景情報` - 背景情報も追記して更新

## 実行フロー

### 0. オプション解析

引数に `--update` が含まれているかを確認します。

- `--update` あり → 既存PR更新モード（セクション1-A へ）
- `--update` なし → 通常モード（セクション1-B へ）

### 1-A. 既存PR更新モード（--update指定時）

```bash
# 現在のブランチ名を取得
CURRENT_BRANCH=$(git branch --show-current)

# 既存PRの確認
PR_NUMBER=$(gh pr list --repo Wonderplanet/glow-brain --head $CURRENT_BRANCH --state open --json number --jq '.[0].number')
```

**既存PRがない場合**: エラーメッセージを表示して終了
```
エラー: 現在のブランチに既存のPRがありません。
新規作成する場合は --update オプションなしで実行してください。
```

**既存PRがある場合**: 以下を実行
1. 既存PRの情報を取得（タイトル、本文、ベースブランチ）
2. 最新のコミット履歴と差分を分析
3. 既存の背景情報を保持しつつ、概要・変更内容・テスト内容を最新化
4. PRを更新（セクション3-2-Aへ）

```bash
# 既存PRの情報取得
gh pr view $PR_NUMBER --repo Wonderplanet/glow-brain --json title,body,baseRefName
```

### 1-B. 通常モード（--updateなし）

まず以下を確認します：

```bash
# 現在のブランチ名を取得
CURRENT_BRANCH=$(git branch --show-current)
echo "現在のブランチ: $CURRENT_BRANCH"

# 既存PRの確認
gh pr list --repo Wonderplanet/glow-brain --head $CURRENT_BRANCH --state open
```

#### ベースブランチの確認

**既存PRがある場合**: そのPRのベースブランチを使用
```bash
gh pr view --repo Wonderplanet/glow-brain --json baseRefName --jq '.baseRefName'
```

**既存PRがない場合**: ユーザーに質問してベースブランチを確認

AskUserQuestionツールを使用して、ユーザーにベースブランチを質問します。
未指定の場合は、デフォルトで `main` を使用します。

```bash
# ベースブランチとの差分を確認
git log --oneline $BASE_BRANCH..HEAD
git diff --name-only $BASE_BRANCH..HEAD
```

### 2. 背景情報の確認

**重要**: PRには背景情報の記載を推奨します。

ユーザーに以下を確認してください：

1. **この対応の背景・経緯は何ですか？**
   - なぜこの変更が必要になったのか
   - どのような問題を解決するのか

2. **関連するSlackスレッドやClickUpタスクはありますか？**
   - **Slackの場合**: URLと内容の要約を記載
   - **ClickUpの場合**: `CU-タスクID` 形式で記載（例: `CU-86c3xyz`）

ユーザーが「スキップ」を希望した場合のみ、背景情報なしで進めることを許容します。

### 3. PRの作成または更新

#### 3-1. 新規PR作成の場合

```bash
# $BASE_BRANCH は自動認識したベースブランチ
gh pr create --repo Wonderplanet/glow-brain \
  --base $BASE_BRANCH \
  --title "PRタイトル" \
  --body "$(cat <<'EOF'
## 概要

変更内容の概要を記載

## 背景

### 関連タスク/スレッド
- CU-タスクID または Slack URL

### 経緯
なぜこの対応が必要だったかを記載

## 変更内容

- 変更点1
- 変更点2

## テスト内容

- テスト方法や確認内容

🤖 Generated with [Claude Code](https://claude.com/claude-code)
EOF
)"
```

#### 3-2. 既存PR更新の場合（通常モード）

```bash
# PR番号を取得
PR_NUMBER=$(gh pr list --repo Wonderplanet/glow-brain --head $(git branch --show-current) --state open --json number --jq '.[0].number')

# PRのタイトルと本文を更新
gh pr edit $PR_NUMBER --repo Wonderplanet/glow-brain \
  --title "更新後のPRタイトル" \
  --body "$(cat <<'EOF'
更新後のPR本文
EOF
)"
```

#### 3-2-A. 既存PR更新の場合（--updateモード）

`--update`オプション指定時は、既存PRの背景情報を保持しつつ、最新のコード内容で更新します。

**更新の流れ:**

1. **既存PRの本文から背景セクションを抽出**
   - `## 背景` セクション全体（関連タスク/スレッド、経緯を含む）を保持

2. **最新のコード差分を分析**
   ```bash
   # ベースブランチとの差分を取得
   BASE_BRANCH=$(gh pr view $PR_NUMBER --repo Wonderplanet/glow-brain --json baseRefName --jq '.baseRefName')
   git log --oneline $BASE_BRANCH..HEAD
   git diff --name-only $BASE_BRANCH..HEAD
   git diff --stat $BASE_BRANCH..HEAD
   ```

3. **更新内容の生成**
   - **タイトル**: 最新のコミット内容を反映した簡潔なタイトル
   - **概要**: 最新の変更内容の要約
   - **背景**: 既存PRから継承（追加の背景情報があれば追記）
   - **変更内容**: 最新のファイル変更リスト
   - **テスト内容**: 必要に応じて更新

4. **PRを更新**
   ```bash
   gh pr edit $PR_NUMBER --repo Wonderplanet/glow-brain \
     --title "更新後のタイトル" \
     --body "$(cat <<'EOF'
   ## 概要

   {最新の変更内容の要約}

   ## 背景

   {既存PRから継承した背景情報}

   {追加の背景情報があればここに追記}

   ## 変更内容

   {最新のファイル変更リスト}
   - 変更1
   - 変更2

   ## テスト内容

   {テスト方法や確認した内容}

   ---
   🤖 Generated with [Claude Code](https://claude.com/claude-code)
   EOF
   )"
   ```

**注意点:**
- 既存の背景情報（関連タスクID、Slack URL、経緯など）は必ず保持する
- 追加の背景情報が引数で指定された場合は、既存の背景セクションに追記する
- 更新完了後、PRのURLを表示する

### 4. PR本文のテンプレート

```markdown
## 概要

{変更内容の簡潔な説明}

## 背景

### 関連タスク/スレッド
{以下のいずれかを記載}
- CU-{ClickUpタスクID}
- {Slack URL}
  - 要約: {Slackでのやり取りの要約}

### 経緯
{なぜこの対応が必要になったかの説明}

## 変更内容

{変更点をリスト形式で記載}
- 変更1
- 変更2

## テスト内容

{テスト方法や確認した内容}

---
🤖 Generated with [Claude Code](https://claude.com/claude-code)
```

## 注意事項

### ghコマンドの必須オプション

**必ず `--repo Wonderplanet/glow-brain` を指定してください。**

```bash
# 正しい例
gh pr create --repo Wonderplanet/glow-brain ...
gh pr list --repo Wonderplanet/glow-brain ...
gh pr edit 123 --repo Wonderplanet/glow-brain ...

# 間違い（--repoがない）
gh pr create ...
```

### 背景情報の記載形式

| 種類 | 記載形式 | 例 |
|------|----------|-----|
| ClickUp | `CU-タスクID` | `CU-86c3xyz` |
| Slack | URL + 要約 | `https://slack.com/archives/C01234/p1234567890`<br>要約: XXXの対応依頼 |

### ベースブランチの決定方法

ベースブランチは以下の方法で決定されます：

1. **既存PRがある場合**: そのPRのベースブランチを使用
2. **既存PRがない場合**: ユーザーに質問して確認（未指定の場合は `main` をデフォルトとして使用）

## トラブルシューティング

### PRが作成できない場合

1. **認証エラー**: `gh auth login` で再認証
2. **ブランチがpushされていない**: `git push -u origin ブランチ名` を実行
3. **既にPRが存在する**: `gh pr list --repo Wonderplanet/glow-brain --head ブランチ名` で確認

### 背景情報がない場合

背景情報なしでPRを作成することは可能ですが、レビュアーが変更の意図を理解しやすくするため、可能な限り記載してください。
