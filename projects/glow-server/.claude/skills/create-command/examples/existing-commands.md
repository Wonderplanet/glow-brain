# プロジェクトの既存コマンド一覧

本プロジェクトで使用されているカスタムスラッシュコマンドの実例です。

## generalディレクトリ（汎用コマンド）

### create-skill.md
スキル作成を支援するコマンド

```markdown
---
description: 新しいClaude Codeスキルを作成する。公式ベストプラクティスに従い、Progressive Disclosureパターンでスキルを構築する。
---
```

**特徴:**
- 対応するスキル（`create-skill`）を呼び出す
- ユーザーとの対話形式

### create-subagent.md
サブエージェント作成を支援するコマンド

```markdown
---
description: 新しいClaude Code subagentを作成する
allowed-tools: Write, Read, Glob, Skill
argument-hint: [エージェント名] [エージェントの説明]
model: sonnet
---
```

**特徴:**
- 位置指定引数を使用
- モデル指定あり
- ツール制限あり

### create-github-action.md
GitHub Actionsワークフロー作成

```markdown
---
description: GitHub Actionsワークフローファイルを正しい構文で作成する。
---
```

**特徴:**
- 詳細なガイドラインを含む
- 公式ドキュメント参照を促す

## apiディレクトリ（API開発コマンド）

### fix-pr-comments.md
PRコメント対応コマンド

### api-fix-sail-check-errors.md
sail checkエラー修正コマンド

### generate-sequence-diagram.md
シーケンス図生成コマンド

## sddディレクトリ（SDD関連コマンド）

SDDワークフローの各ステップに対応したコマンド群

```
00-sdd-run-full-flow.md    # フルフロー実行
01-extract-server-requirements.md
02-investigate-code-requirements.md
...
```

**特徴:**
- 番号付きで順序を表現
- 一連のワークフローを構成

## master-dataディレクトリ

### generate-master-csv.md
マスタデータCSV生成

## コマンド設計のポイント

### 1. 名前空間の活用
- `general/`: プロジェクト横断の汎用コマンド
- `api/`: API開発に特化
- `sdd/`: SDD関連
- `master-data/`: マスタデータ関連

### 2. 命名規則
- kebab-case: `create-skill.md`
- 動詞で始める: `create-`, `fix-`, `generate-`
- 番号付き: `01-xxx.md`（順序がある場合）

### 3. descriptionの書き方
- 何ができるか明記
- いつ使うか示す
- 日本語/英語どちらでもOK
