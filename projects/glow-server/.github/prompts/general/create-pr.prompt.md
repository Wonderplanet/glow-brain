---
mode: 'agent'
tools: ['codebase', 'terminalCommand']
description: '現在のブランチの変更内容に基づいてPull Requestを作成または更新します'
---

# PR作成コマンド

現在のブランチの変更内容に基づいてPull Requestを作成または更新します。

## 入力

モード選択: ${input:mode:新規作成/通常更新は「new」、既存PR更新は「update」を入力}

背景情報（オプション）: ${input:backgroundInfo:背景情報を入力（ClickUpタスクID、SlackURL等）。スキップする場合は空欄}

## 実行フロー

### 0. モード判定

入力されたモード: `${input:mode}`

- `update` → 既存PR更新モード（セクション1-A へ）
- `new` または空欄 → 通常モード（セクション1-B へ）

### 1-A. 既存PR更新モード（mode=update）

```bash
# 現在のブランチ名を取得
CURRENT_BRANCH=$(git branch --show-current)

# 既存PRの確認
PR_NUMBER=$(gh pr list --repo Wonderplanet/glow-server --head $CURRENT_BRANCH --state open --json number --jq '.[0].number')
```

**既存PRがない場合**: エラーメッセージを表示して終了
```
エラー: 現在のブランチに既存のPRがありません。
新規作成する場合は mode を「new」にして実行してください。
```

**既存PRがある場合**: 以下を実行
1. 既存PRの情報を取得（タイトル、本文、ベースブランチ）
2. 最新のコミット履歴と差分を分析
3. 既存の背景情報を保持しつつ、概要・変更内容・テスト内容を最新化
4. PRを更新（セクション3-2-Aへ）

```bash
# 既存PRの情報取得
gh pr view $PR_NUMBER --repo Wonderplanet/glow-server --json title,body,baseRefName
```

### 1-B. 通常モード（mode=new または空欄）

まず以下を確認します：

```bash
# 現在のブランチ名を取得
CURRENT_BRANCH=$(git branch --show-current)
echo "現在のブランチ: $CURRENT_BRANCH"

# 既存PRの確認
gh pr list --repo Wonderplanet/glow-server --head $CURRENT_BRANCH --state open
```

#### ベースブランチの自動認識ロジック

以下の優先順位でベースブランチを決定します：

1. **既存PRがある場合**: そのPRのベースブランチを使用
   ```bash
   gh pr view --repo Wonderplanet/glow-server --json baseRefName --jq '.baseRefName'
   ```

2. **ブランチ名にバージョンプレフィックスがある場合**: 対応するdevelopブランチを使用
   - `v1.4.0/xxx` → `develop/v1.4.0`
   - `v1.3.0/xxx` → `develop/v1.3.0`
   - `admin/v1.4.0/xxx` → `develop/v1.4.0`
   - `api/v1.3.0/xxx` → `develop/v1.3.0`

3. **上記に該当しない場合**: リモートのデフォルトブランチを使用
   ```bash
   git remote show origin | grep 'HEAD branch' | sed 's/.*: //'
   ```

**ベースブランチ決定後、確認を表示:**
```
ベースブランチ: develop/v1.4.0 （ブランチ名 v1.4.0/xxx から自動認識）
```

```bash
# ベースブランチとの差分を確認
git log --oneline $BASE_BRANCH..HEAD
git diff --name-only $BASE_BRANCH..HEAD
```

### 2. 背景情報の確認

**重要**: PRには背景情報の記載を推奨します。

入力された背景情報: `${input:backgroundInfo}`

背景情報が空の場合は、ユーザーに以下を確認してください：

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
gh pr create --repo Wonderplanet/glow-server \
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

---
Generated with GitHub Copilot
EOF
)"
```

#### 3-2. 既存PR更新の場合（通常モード）

```bash
# PR番号を取得
PR_NUMBER=$(gh pr list --repo Wonderplanet/glow-server --head $(git branch --show-current) --state open --json number --jq '.[0].number')

# PRのタイトルと本文を更新
gh pr edit $PR_NUMBER --repo Wonderplanet/glow-server \
  --title "更新後のPRタイトル" \
  --body "$(cat <<'EOF'
更新後のPR本文
EOF
)"
```

#### 3-2-A. 既存PR更新の場合（mode=update）

mode=update指定時は、既存PRの背景情報を保持しつつ、最新のコード内容で更新します。

**更新の流れ:**

1. **既存PRの本文から背景セクションを抽出**
   - `## 背景` セクション全体（関連タスク/スレッド、経緯を含む）を保持

2. **最新のコード差分を分析**
   ```bash
   # ベースブランチとの差分を取得
   BASE_BRANCH=$(gh pr view $PR_NUMBER --repo Wonderplanet/glow-server --json baseRefName --jq '.baseRefName')
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
   gh pr edit $PR_NUMBER --repo Wonderplanet/glow-server \
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
   Generated with GitHub Copilot
   EOF
   )"
   ```

**注意点:**
- 既存の背景情報（関連タスクID、Slack URL、経緯など）は必ず保持する
- 追加の背景情報が入力された場合は、既存の背景セクションに追記する
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
Generated with GitHub Copilot
```

## 注意事項

### ghコマンドの必須オプション

**必ず `--repo Wonderplanet/glow-server` を指定してください。**

```bash
# 正しい例
gh pr create --repo Wonderplanet/glow-server ...
gh pr list --repo Wonderplanet/glow-server ...
gh pr edit 123 --repo Wonderplanet/glow-server ...

# 間違い（--repoがない）
gh pr create ...
```

### 背景情報の記載形式

| 種類 | 記載形式 | 例 |
|------|----------|-----|
| ClickUp | `CU-タスクID` | `CU-86c3xyz` |
| Slack | URL + 要約 | `https://slack.com/archives/C01234/p1234567890`<br>要約: XXXの対応依頼 |

### ベースブランチの自動認識

ベースブランチは以下の優先順位で自動認識されます：

1. 既存PRのベースブランチ
2. ブランチ名のバージョンプレフィックス（`v1.4.0/xxx` → `develop/v1.4.0`）
3. リモートのデフォルトブランチ（`origin/HEAD`）

自動認識されたベースブランチが正しいか確認し、必要に応じてユーザーに確認してください。

## トラブルシューティング

### PRが作成できない場合

1. **認証エラー**: `gh auth login` で再認証
2. **ブランチがpushされていない**: `git push -u origin ブランチ名` を実行
3. **既にPRが存在する**: `gh pr list --repo Wonderplanet/glow-server --head ブランチ名` で確認

### 背景情報がない場合

背景情報なしでPRを作成することは可能ですが、レビュアーが変更の意図を理解しやすくするため、可能な限り記載してください。
