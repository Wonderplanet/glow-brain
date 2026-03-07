---
description: GitHub Actionsワークフローファイルを正しい構文で作成する。YAMLの構文エラーやGitHub Actions固有の記述ルールを考慮し、IDEでwarningが出ない正常なワークフローファイルを生成する。
---

# GitHub Actions ワークフロー作成コマンド

GitHub Actionsのワークフローファイルを正しい構文・記述ルールに従って作成します。

## 使用方法

```
/general:create-github-action [ワークフロー名] [ワークフローの説明]
```

例: `/general:create-github-action notify-deploy デプロイ完了時にSlackに通知`

## 引数

引数: $ARGUMENTS

---

## 実行手順

### 0. 公式ドキュメントの参照（必須）

ワークフロー作成前に、最新の公式ドキュメントを参照して正確な記述方法を確認します。

**必ず参照すべき公式ドキュメント:**

1. **ワークフロー構文リファレンス**
   - URL: https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions
   - 内容: `on`, `jobs`, `steps`などの基本構文

2. **イベントトリガー**
   - URL: https://docs.github.com/en/actions/using-workflows/events-that-trigger-workflows
   - 内容: 各イベント（push, pull_request, issue_comment等）の詳細

3. **コンテキストと式**
   - URL: https://docs.github.com/en/actions/learn-github-actions/contexts
   - 内容: `github`, `env`, `secrets`コンテキストの使い方

4. **ジョブで使用する権限**
   - URL: https://docs.github.com/en/actions/using-jobs/assigning-permissions-to-jobs
   - 内容: `permissions`の設定方法

**WebFetchツールを使って関連ドキュメントを取得し、最新の記述ルールを確認してからワークフローを作成してください。**

### 1. 要件の確認

ワークフローの目的と要件を確認します：

1. **トリガー条件**: どのイベントでワークフローを実行するか
   - `push`: プッシュ時
   - `pull_request`: PR作成・更新時
   - `issue_comment`: Issue/PRコメント時
   - `workflow_dispatch`: 手動実行
   - `schedule`: スケジュール実行（cron形式）

2. **実行条件**: 追加のフィルタリング
   - ブランチ指定
   - パス指定
   - イベントタイプ（opened, closed, etc.）

3. **必要な処理**: ワークフローで何を行うか

### 2. GitHub Actions記述ルール（必須遵守事項）

以下のルールに厳密に従ってワークフローファイルを作成します：

#### 2.1 基本構造ルール

```yaml
# ファイル先頭: name は必須
name: ワークフロー名

# on: トリガー定義（必須）
on:
  # イベントタイプを指定
  push:
    branches:
      - main
  pull_request:
    types: [opened, synchronize, closed]
```

#### 2.2 jobs定義ルール

```yaml
jobs:
  # job-idはケバブケースまたはスネークケース
  job-name:
    # runs-on は必須
    runs-on: ubuntu-latest

    # permissions: 必要な権限を明示的に指定
    permissions:
      contents: read
      pull-requests: write

    # if: 条件式（オプション）
    if: github.event_name == 'push'

    steps:
      - name: Step名
        # 各stepには name, uses, run のいずれかが必要
```

#### 2.3 よくあるエラーと正しい記述

**❌ 間違い: if条件の複数行記述**
```yaml
if: github.event_name == 'pull_request' &&
    github.event.pull_request.merged == true
```

**✅ 正しい: パイプ演算子を使用**
```yaml
if: |
  github.event_name == 'pull_request' &&
  github.event.pull_request.merged == true
```

**❌ 間違い: 文字列内での直接変数展開**
```yaml
run: echo "${{ secrets.TOKEN }}"  # 安全ではない
```

**✅ 正しい: 環境変数経由で使用**
```yaml
run: echo "$TOKEN"
env:
  TOKEN: ${{ secrets.TOKEN }}
```

**❌ 間違い: uses と run の混在**
```yaml
- name: Checkout
  uses: actions/checkout@v4
  run: echo "something"  # usesとrunは同時に使えない
```

**✅ 正しい: 別々のstepに分ける**
```yaml
- name: Checkout
  uses: actions/checkout@v4
- name: Echo
  run: echo "something"
```

#### 2.4 必須チェック項目

1. **YAMLインデント**: スペース2つで統一
2. **文字列クォート**: 特殊文字を含む場合はシングルまたはダブルクォート
3. **ブール値**: `true`/`false`（クォートなし）
4. **actions/checkout**: ほぼ全てのワークフローで必要（最初のstep）
5. **secrets参照**: `${{ secrets.NAME }}`形式
6. **env参照**: `${{ env.NAME }}`または`$NAME`（runコンテキスト内）
7. **github context**: `${{ github.event.xxx }}`形式

#### 2.5 permissionsの推奨設定

```yaml
permissions:
  contents: read          # リポジトリ読み取り
  contents: write         # リポジトリ書き込み（コミット・プッシュ）
  pull-requests: read     # PR読み取り
  pull-requests: write    # PRコメント・ラベル操作
  issues: write           # Issue操作
  actions: read           # ワークフロー情報取得
```

### 3. ワークフローテンプレート

#### 3.1 PRマージ時実行テンプレート

```yaml
name: ワークフロー名

on:
  pull_request:
    types: [closed]

jobs:
  job-name:
    if: github.event.pull_request.merged == true
    runs-on: ubuntu-latest
    permissions:
      contents: read
      pull-requests: read

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          fetch-depth: 0
          token: ${{ secrets.GITHUB_TOKEN }}

      - name: 処理
        run: |
          echo "PR #${{ github.event.pull_request.number }} was merged"
```

#### 3.2 PRコメントトリガーテンプレート

```yaml
name: ワークフロー名

on:
  issue_comment:
    types: [created]

jobs:
  job-name:
    # PRコメントのみ（Issueコメントは除外）
    if: github.event.issue.pull_request != null
    runs-on: ubuntu-latest
    permissions:
      contents: write
      pull-requests: write

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          token: ${{ secrets.GITHUB_TOKEN }}

      - name: PRブランチ取得
        run: |
          PR_NUMBER="${{ github.event.issue.number }}"
          PR_BRANCH=$(gh pr view $PR_NUMBER --json headRefName -q '.headRefName')
          echo "PR_BRANCH=${PR_BRANCH}" >> $GITHUB_ENV
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

#### 3.3 スケジュール実行テンプレート

```yaml
name: ワークフロー名

on:
  schedule:
    # UTCで指定（JSTは+9時間）
    # 例: 日本時間の毎日9時 = UTC 0時
    - cron: '0 0 * * *'
  workflow_dispatch:  # 手動実行も許可

jobs:
  job-name:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
```

#### 3.4 Slack通知テンプレート

```yaml
      - name: Notify Slack
        uses: 8398a7/action-slack@v3
        with:
          status: custom
          custom_payload: |
            {
              "text": "通知タイトル",
              "attachments": [
                {
                  "color": "good",
                  "title": "メッセージタイトル",
                  "fields": [
                    {
                      "title": "フィールド名",
                      "value": "フィールド値",
                      "short": true
                    }
                  ]
                }
              ]
            }
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK_URL }}
```

### 4. 作成フロー

1. **既存ワークフロー確認**
   - `.github/workflows/`配下の既存ファイルを確認
   - 類似の処理があれば参考にする

2. **ワークフローファイル作成**
   - 上記ルールに厳密に従う
   - 適切なテンプレートをベースにカスタマイズ

3. **構文検証**
   - YAMLの構文エラーがないか確認
   - GitHub Actions固有の記述ルールに違反していないか確認

4. **動作説明**
   - 作成したワークフローの動作を説明
   - 必要なsecretsがあれば設定方法を案内

### 5. 検証チェックリスト

作成後、以下を確認します：

#### 基本構造
- [ ] `name:`が定義されている
- [ ] `on:`が正しく定義されている
- [ ] 各jobに`runs-on:`がある
- [ ] 必要な`permissions:`が設定されている

#### 構文ルール
- [ ] インデントがスペース2つで統一されている
- [ ] `if:`条件は複数行の場合パイプ（`|`）を使用
- [ ] `uses:`と`run:`が同じstepに混在していない
- [ ] YAMLの配列記法が正しい（`- item`形式）
- [ ] 文字列内の特殊文字が適切にエスケープされている

#### コンテキスト・変数
- [ ] secretsは`${{ secrets.NAME }}`形式で参照
- [ ] 環境変数を使う場合は`env:`セクションで定義
- [ ] `github`コンテキストの参照が正しい（例: `github.event.pull_request.number`）
- [ ] 出力変数は`$GITHUB_OUTPUT`または`$GITHUB_ENV`に書き込み

#### アクション
- [ ] `actions/checkout@v4`を使用（最新バージョン）
- [ ] 必要に応じて`fetch-depth: 0`を設定（履歴が必要な場合）
- [ ] サードパーティアクションはメジャーバージョンを指定（`@v3`等）

### 6. IDEでの警告を防ぐための追加チェック

以下はIDEでwarningが出やすいポイントです：

1. **未使用の出力変数**
   - `id:`を定義したstepの出力を使っているか確認

2. **条件分岐の括弧**
   ```yaml
   # ✅ 推奨: 括弧で明示的にグループ化
   if: ${{ github.event_name == 'push' && github.ref == 'refs/heads/main' }}
   ```

3. **文字列のクォート**
   ```yaml
   # ✅ 特殊文字を含む場合はクォート
   run: echo "Branch: ${{ github.ref }}"

   # ✅ YAMLの予約語（true, false, on等）はクォート
   if: github.event.pull_request.merged == true  # booleanはクォート不要
   ```

4. **コメントの位置**
   ```yaml
   # ✅ コメントは独立した行に
   steps:
     # このステップはチェックアウトを行う
     - name: Checkout
   ```

5. **空の値**
   ```yaml
   # ❌ 空のマップは警告になる場合がある
   env:

   # ✅ 不要なら削除する
   ```

### 7. 公式ドキュメント参照の実行

**重要: 以下のURLをWebFetchで取得し、最新の記述ルールを確認してください**

```
# 必須参照
https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions
https://docs.github.com/en/actions/using-workflows/events-that-trigger-workflows

# 必要に応じて参照
https://docs.github.com/en/actions/learn-github-actions/contexts
https://docs.github.com/en/actions/using-jobs/assigning-permissions-to-jobs
https://docs.github.com/en/actions/security-guides/security-hardening-for-github-actions
```

---

それでは、要件をお聞かせください：

1. **ワークフロー名**: 何という名前にしますか？
2. **トリガー**: いつ実行しますか？（push、PR、コメント、スケジュール等）
3. **処理内容**: 何を行いますか？
4. **必要なsecrets**: 外部サービス連携に必要な認証情報はありますか？

**注意**: 複雑なワークフローの場合、まず公式ドキュメントを参照してから作成を開始します。
