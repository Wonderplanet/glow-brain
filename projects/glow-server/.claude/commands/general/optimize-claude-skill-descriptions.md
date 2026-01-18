---
description: .claude/skills内の全スキルのfrontmatter.descriptionを日本語で最適化。公式ベストプラクティスに従い、5スキルずつ並列処理。スキル説明の自動認識改善が必要な時に使用。
allowed-tools: Read, Glob, Task
model: sonnet
---

# Claude Skill Frontmatter Description Optimization

.claude/skillsにある全てのClaude skillsのfrontmatter.descriptionを、公式のベストプラクティスに沿う形で最適化します。

**重要**: 全てのdescriptionは日本語で記述してください。

## 実行内容

### 1. スキル一覧の取得

.claude/skills ディレクトリ内の全スキルを一覧します。

### 2. スキルのグループ分け

取得したスキルを5つずつのグループに分割します。

### 3. 並行処理の実行

各グループごとに general-purpose subagent を起動し、以下のタスクを並行して実行します：

**各subagentのタスク:**
- 割り当てられた5つのスキルについて、example-skills:skill-creator スキルを使用
- frontmatter.description を公式ベストプラクティスに従って最適化
- **重要**: descriptionは必ず日本語で記述すること
- スキルの自動認識が最適化された形に修正

### 4. 最適化の観点

公式ベストプラクティスに基づき、以下の観点でdescriptionを最適化：

- **明確性**: 何ができるか明記
- **トリガーワード**: いつ使うか示す具体的なキーワードを含める
- **簡潔性**: 必要十分な情報を簡潔に記述
- **スコープ**: プロジェクト固有の文脈を含める（例: glow-server、Laravel、PHPUnit等）

## 注意事項

- **descriptionは必ず日本語で記述すること**（英語は使用しない）
- 各スキルの実際の機能を理解した上で最適化する
- 既存の機能を変更せず、説明文のみを改善する
- Progressive Disclosure パターンを維持する
