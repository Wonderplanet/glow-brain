---
description: Claude Code skillsを対話的に作成
argument-hint: [スキルの要件（オプション）]
---

# Claude Code Skill作成コマンド

このコマンドは、Claude Codeの公式skill-creatorスキルを活用して、高品質なスキルを対話的に作成します。

## 概要

Claude Code skillsは、複雑なワークフローや複数のファイル、スクリプトを含む高度な機能を実装するための仕組みです。このコマンドは公式のベストプラクティスに沿った形でスキルを作成します。

## Skillsとカスタムコマンドの違い

- **Skillsを使うべき場合**:
  - 複数のファイルやスクリプトが必要
  - Claudeに自動検出させたい（ユーザーが明示的に呼び出す必要がない）
  - 複雑なワークフローやテンプレートを含む

- **カスタムコマンドを使うべき場合**:
  - シンプルなプロンプトで完結
  - ユーザーが明示的に `/command` で起動したい
  - 頻繁に使う定型的な処理

判断に迷う場合は、`.claude/docs/skills-best-practices.md` を参照してください。

## 引数の使い方

### パターン1: 引数なしで対話的に作成

```bash
/claude-code:create-skill
```

このパターンでは、skill-creatorスキルが対話的にスキルの要件をヒアリングし、最適な構成を提案します。

### パターン2: 要件を直接指定

```bash
/claude-code:create-skill Excelファイルを分析してピボットテーブルを生成するスキル
```

このパターンでは、引数として渡した要件を基にskill-creatorスキルが自動的にスキルを作成します。

## タスク

あなたのタスクは、公式のskill-creatorスキルを起動して、ユーザーの要件に基づいたスキルを作成することです。

### ステップ1: 要件の確認

引数が提供されているか確認します：

- **引数あり（$1が存在）**: その要件を使用
- **引数なし**: ユーザーに要件をヒアリング

### ステップ2: skill-creatorスキルの起動

Skillツールを使用して、公式のskill-creatorスキルを起動します：

```
Skill(skill: "example-skills:skill-creator", args: "[要件]")
```

**重要**: 要件には以下を含めることを推奨してください：
- スキルが解決する問題
- 対象となるファイル形式やユースケース
- 必要な機能の概要

### ステップ3: skill-creatorに任せる

skill-creatorスキルが自動的に以下を実行します：

1. 要件の詳細なヒアリング
2. スキルの構造設計
3. SKILL.mdの作成
4. 必要に応じて追加ファイルの作成
5. ベストプラクティスの適用
6. スキルの検証

あなたは skill-creator の指示に従い、必要な情報を提供してください。

### ステップ4: 完成後の確認

skill-creatorが完了したら、以下を確認してユーザーに報告します：

- 作成されたスキルの配置場所（`.claude/skills/[skill-name]/`）
- スキルの起動方法
- スキルの説明文（description）
- 含まれるファイルの一覧

## ベストプラクティス

### スキル名

- 小文字とハイフンのみ使用
- 最大64文字
- 例: `pdf-analyzer`, `excel-processor`, `code-reviewer`

### 説明文（description）

- 30～50文字程度が最適
- 具体的な機能を明示
- トリガーキーワードを含める
- 対象ファイル形式を明記

**良い例**:
```
PDFファイルからテキストと表を抽出し、フォームを埋入します。PDF、フォーム、ドキュメント抽出で使用します。
```

**悪い例**:
```
ファイルを処理します
```

### ツール制限（allowed-tools）

読み取り専用スキルの場合：
```yaml
allowed-tools: Read, Grep, Glob
```

特定のツールのみ許可する場合：
```yaml
allowed-tools: Bash(git status:*), Bash(git diff:*), Read, Grep
```

## 使用例

### 例1: PDF処理スキル

```bash
/claude-code:create-skill PDFファイルからテキストと表を抽出し、複数のPDFをマージする機能
```

### 例2: データ分析スキル

```bash
/claude-code:create-skill CSVやExcelファイルを分析して統計レポートを生成し、グラフを作成する
```

### 例3: コードレビュースキル

```bash
/claude-code:create-skill プルリクエストのコードをセキュリティとパフォーマンスの観点からレビューする
```

## チーム共有

作成したスキルをチームで共有する場合：

```bash
git add .claude/skills/
git commit -m "Add new skill: [skill-name]"
git push
```

チームメンバーは `git pull` を実行するだけで自動的にスキルを利用可能になります。

## 参考資料

- ベストプラクティス: `.claude/docs/skills-best-practices.md`
- 既存のスキル例: `.claude/skills/` ディレクトリ
- 公式ドキュメント: claude-code-guideエージェントに質問

## 注意事項

- **projects以下のファイルは参照禁止**: このリポジトリは参照専用であり、変更禁止です
- **シンプルな場合はカスタムコマンドを検討**: 単純なプロンプトの場合は `/claude-code:create-command` の使用を検討してください
- **スクリプトには実行権限を付与**: スキルにスクリプトを含める場合、`chmod +x` で実行権限を付与してください

## トラブルシューティング

### スキルが自動検出されない

- description に適切なトリガーキーワードが含まれているか確認
- スキル名が小文字とハイフンのみで構成されているか確認
- SKILL.md のフォーマットが正しいか確認

### スキルが起動できない

- `.claude/skills/[skill-name]/SKILL.md` が存在するか確認
- フロントマター（`---` で囲まれた部分）が正しいか確認
- name と description が必須項目として含まれているか確認

---

それでは、skill-creatorスキルを起動して、高品質なClaude Code skillを作成しましょう！
