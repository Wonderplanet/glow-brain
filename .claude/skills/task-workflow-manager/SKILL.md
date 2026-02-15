---
name: task-workflow-manager
description: ユーザーのやりたいことを段階的に質問し、domain/tasks配下に作業フォルダを作成して成果を確実に積み上げるスキル。「タスク管理」「プロジェクト開始」「アイデア実現」「やりたいこと」「成果を残す」「新しいタスク」などで使用します。AIを使った作業で成果が曖昧になる問題を解決し、確実に成果を積み上げられるワークフローを提供します。
---

# Task Workflow Manager

## 概要

AIを使って作業をしていると、思いついたアイデアをどんどん試すことはできるが、以下の問題が発生している：

- 明確な成果物にまで届かず、途中で終わる
- 何をやりたかったか曖昧になる
- 優先順位が不明確になる
- 結果的に何も成果がない状態になる

このスキルは、**タスクの開始を支援**し、適切なワークフローで着実に成果を積み重ねることを目的とします。

## ワークフロー

### Step 1: タスク名の決定

まず、ユーザーにタスク名を質問します。

```
AskUserQuestion: "このタスクの名前を教えてください（例: id-numbering-analysis, create-masterdata-from-biz-ops-specs）"
```

**重要**: 既存の `domain/tasks/` 配下のフォルダと重複していないか確認してください。

```bash
ls -la domain/tasks/
```

もし重複している場合は、別の名前を提案するか、既存のタスクフォルダを使用するか確認してください。

### Step 2: 背景・目的のヒアリング

段階的に以下を質問します。1つずつ順番に質問し、回答を記録してください。

**質問1: 背景・経緯**
```
AskUserQuestion: "どんな経緯でこのタスクを始めようと思いましたか？"
```

**質問2: 解決したい問題**
```
AskUserQuestion: "現在どのような問題がありますか？"
```

**質問3: 期待する成果**
```
AskUserQuestion: "それが解決できると何が良いですか？（期待する成果）"
```

**質問4: ネクストアクション候補**
```
AskUserQuestion: "この成果はどんな次のアクションに繋がりますか？"
```

**質問5: 解決アイデア**
```
AskUserQuestion: "解決するための具体的なアイデアはありますか？（あれば）"
```

### Step 3: タスクフォルダの作成

`domain/tasks/{タスク名}/` フォルダを作成し、**最小構成**で初期化します。

```bash
mkdir -p domain/tasks/{タスク名}
```

**初期構成**（最小限）:
- `README.md` - タスク概要
- `next-actions.md` - ネクストアクション

**必要に応じて追加可能なフォルダ**:
- `inputs/` - 入力データ・要件
- `outputs/` - 成果物
- `analysis/` - 分析結果
- `scripts/` - 実行スクリプト

最初は最小構成で開始し、タスクの内容に応じて臨機応変に追加してください。

### Step 4: README.md生成

Step 2の回答を基に、`assets/README.template.md` を使用してREADME.mdを生成します。

**テンプレート変数**:
- `{TASK_NAME}` - タスク名
- `{BRIEF_SUMMARY}` - 1行サマリー（自動生成）
- `{BACKGROUND}` - 質問1の回答
- `{PROBLEM}` - 質問2の回答
- `{EXPECTED_OUTCOME}` - 質問3の回答
- `{NEXT_ACTION_IDEAS}` - 質問4の回答
- `{SOLUTION_IDEAS}` - 質問5の回答
- `{TASK_FOLDER}` - タスクフォルダ名
- `{TIMESTAMP}` - 現在時刻（YYYY-MM-DD HH:MM形式）

**README.md生成手順**:
1. `assets/README.template.md` を読み込む
2. 上記の変数を実際の値に置換
3. `domain/tasks/{タスク名}/README.md` に書き込む

**next-actions.md生成手順**:
1. `assets/next-actions.template.md` を読み込む
2. `{NEXT_ACTIONS}` を質問4の回答に置換
3. `domain/tasks/{タスク名}/next-actions.md` に書き込む

### Step 5: 次の作業の提案

タスクの内容に応じて、以下のいずれかを提案します。

#### パターンA: 既存スキル/エージェントへの委譲（自動実行を検討）

タスク名やアイデアから、明確に該当するスキル/エージェントがある場合は、**自動起動を提案**します。

**判断基準**:
- タスク名に「マスタデータ」が含まれる → `masterdata-from-bizops-*` スキル
- タスク名に「ClickUp」が含まれる → `clickup-task-analyzer` スキル
- タスク名に「プラグイン」が含まれる → `plugin-marketplace-creator` スキル

**提案例**:
```
「このタスクは{スキル名}スキルで処理できそうです。自動起動しますか？」
```

曖昧な場合は、選択肢として提示し、ユーザーの判断を仰いでください。

#### パターンB: フォルダ追加の提案

以下のような提案をします：

1. **入力データを整理する** → `inputs/` フォルダ作成を提案
2. **分析を開始する** → `analysis/` フォルダ作成を提案
3. **すぐに実装を開始する** → `outputs/` フォルダ作成を提案

#### パターンC: 次のステップの明示

特定のスキルに委譲しない場合、以下を明示します：

```
次のステップ:
1. domain/tasks/{タスク名}/README.md を確認
2. domain/tasks/{タスク名}/next-actions.md を確認
3. 必要に応じて inputs/outputs/analysis/scripts フォルダを追加
4. 作業を開始
```

## 重要な設計方針

1. **このスキルは「タスクの開始」に特化** - 実際の作業は他のスキル/エージェントに委譲する
2. **最小構成から開始、臨機応変に拡張** - README.md と next-actions.md のみで開始し、必要に応じてフォルダを追加
3. **わかりやすさを最優先** - inputs/outputs など、初見でも理解しやすい名前を使用
4. **ネクストアクションを常に明示** - 作業の継続性を担保

## テンプレート

テンプレートファイルは `assets/` フォルダに格納されています：

- `assets/README.template.md` - README.mdのテンプレート
- `assets/next-actions.template.md` - next-actions.mdのテンプレート

必要に応じてテンプレートを読み込み、変数を置換して使用してください。

## 参考構造

`domain/tasks/masterdata-entry/id-numbering-analysis/` のようなシンプルな構造を参考にしています。

## 使用例

### 例1: マスタデータ作成タスク

```
ユーザー: 「新しいタスクを開始したい」
→ スキル起動
→ タスク名: "create-gacha-masterdata"
→ 背景・問題・成果をヒアリング
→ フォルダ作成 & README.md生成
→ masterdata-from-bizops-* スキルの自動起動を提案
```

### 例2: 分析タスク

```
ユーザー: 「やりたいことがある」
→ スキル起動
→ タスク名: "user-retention-analysis"
→ 背景・問題・成果をヒアリング
→ フォルダ作成 & README.md生成
→ inputs/ フォルダ作成を提案（データ収集）
```

### 例3: 実装タスク

```
ユーザー: 「アイデアを形にしたい」
→ スキル起動
→ タスク名: "new-feature-prototype"
→ 背景・問題・成果をヒアリング
→ フォルダ作成 & README.md生成
→ outputs/ フォルダ作成を提案（実装開始）
```
