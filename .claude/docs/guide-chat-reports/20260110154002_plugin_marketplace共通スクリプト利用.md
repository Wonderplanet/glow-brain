# Claude Code ガイド相談レポート

**日付**: 2026年01月10日
**トピック**: Plugin Marketplace内での共通スクリプト利用

## 相談内容

- Plugin Marketplace内で複数のスキルを作成する際に、共通のスクリプトファイルを`scripts/`ディレクトリに配置して、複数のスキルから呼び出すことが可能か
- そのような使い方が公式的に想定されているパターンか

## 重要な結論・学び

### 結論: 可能であり、公式推奨パターン

**はい、Plugin Marketplace内に2つのスキルを作成し、共通の`scripts/`ディレクトリにあるスクリプトを両方から呼び出すことは可能です。このパターンは公式ドキュメントで推奨されています。**

> 出典: [Plugin Marketplaces - Claude Code Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)

### 推奨ディレクトリ構造

```
plugin-marketplace/
├── .claude-plugin/
│   └── plugin.json
├── scripts/
│   └── shared-utility.py          # 共通スクリプト
└── skills/
    ├── skill-1/
    │   ├── SKILL.md
    │   └── reference.md
    └── skill-2/
        ├── SKILL.md
        └── reference.md
```

### `${CLAUDE_PLUGIN_ROOT}`変数の使用が必須

公式ドキュメントより：

> Use this variable in hooks, MCP servers, and scripts to reference files within the plugin's installation directory. This is necessary because plugins are copied to a cache location when installed.

> 出典: [Plugins Reference - Environment Variables](https://code.claude.com/docs/en/plugins-reference.md#environment-variables)

**コード例**:

```yaml
---
name: skill-one
description: First skill that uses shared utilities
allowed-tools: Bash(python:*)
---

# Skill One

To validate data, run the shared utility script:

python ${CLAUDE_PLUGIN_ROOT}/scripts/shared-utility.py input.txt
```

**重要**: プラグインはインストール時にキャッシュディレクトリにコピーされるため、相対パスでの参照が機能しません。絶対パスを取得するため、**必ず`${CLAUDE_PLUGIN_ROOT}`を使用**してください。

### パストラバーサルの禁止

公式ドキュメントより：

> Plugins cannot reference files outside their copied directory structure. Paths that traverse outside the plugin root (such as `../shared-utils`) will not work after installation because those external files are not copied to the cache.

> 出典: [Plugin Marketplaces - Files not found after installation](https://code.claude.com/docs/en/plugin-marketplaces.md#files-not-found-after-installation)

**注意点**:
- `../scripts/`のような相対パスは使用できません
- すべてのスクリプトは、プラグインのルートディレクトリ内に配置する必要があります

### スクリプトのゼロコンテキスト実行

公式ドキュメントより：

> Bundle utility scripts for zero-context execution. Scripts in your Skill directory can be executed without loading their contents into context. Claude runs the script and only the output consumes tokens.

> 出典: [Skills - Bundle utility scripts](https://code.claude.com/docs/en/skills.md#bundle-utility-scripts-for-zero-context-execution)

**ベストプラクティス**:
- スクリプトをスキルディレクトリの`scripts/`に配置することで、スクリプトの内容がコンテキストに読み込まれません
- 実行結果のみがトークン消費されます
- 大規模な共通ロジックでもコンテキストを圧迫しません

## 完全な実装例

### ファイル構成

```
marketplace/
├── .claude-plugin/
│   └── plugin.json
├── scripts/
│   └── validate_csv.py
└── skills/
    ├── csv-validator/
    │   └── SKILL.md
    └── csv-analyzer/
        └── SKILL.md
```

### `plugin.json`

```json
{
  "name": "csv-tools",
  "version": "1.0.0",
  "description": "CSV processing tools with shared validation logic",
  "skills": "./skills/"
}
```

### `skills/csv-validator/SKILL.md`

```yaml
---
name: csv-validator
description: Validates CSV files for correctness. Use when checking CSV files for errors or format issues.
allowed-tools: Bash(python:*), Read, Write
---

# CSV Validator Skill

## Validation

Use the shared validation script to check your CSV files:

```bash
python ${CLAUDE_PLUGIN_ROOT}/scripts/validate_csv.py your_file.csv
```

The script checks for:
- Valid CSV format
- Required columns
- Data type consistency
- Missing values
```

### `skills/csv-analyzer/SKILL.md`

```yaml
---
name: csv-analyzer
description: Analyzes CSV data and generates reports. Use when you need to analyze data patterns or generate summaries.
allowed-tools: Bash(python:*), Read, Write
---

# CSV Analyzer Skill

## Analysis

Use the shared validation script to prepare data before analysis:

```bash
python ${CLAUDE_PLUGIN_ROOT}/scripts/validate_csv.py your_file.csv
```

After validation, the data is ready for:
- Statistical analysis
- Pattern detection
- Report generation
```

## ベストプラクティス

### 1. `allowed-tools`で権限を明示する

```yaml
allowed-tools: Bash(python:*), Read, Write
```

- スクリプト実行に必要なツールを明示的に宣言することで、セキュリティが向上します
- ユーザーに対する権限要求ダイアログが減少します

### 2. スクリプトは実行可能にする

```bash
chmod +x scripts/shared-utility.py
```

または、スクリプトが実行されるたびに明示的にPythonを呼び出す：

```bash
python ${CLAUDE_PLUGIN_ROOT}/scripts/shared-utility.py
```

### 3. スキルで共通ロジックをドキュメント化する

- 複数のスキルが同じスクリプトを使用する場合、各スキルの`SKILL.md`で、そのスクリプトの役割と使用方法を明確に説明してください
- これにより、ユーザーが各スキルの機能を理解しやすくなります

## 参照ドキュメント

以下の公式ドキュメントを参照しました：

- [Plugin Marketplaces - Claude Code Documentation](https://code.claude.com/docs/en/plugin-marketplaces.md)
- [Plugins Reference - Environment Variables](https://code.claude.com/docs/en/plugins-reference.md#environment-variables)
- [Plugin Caching and File Resolution](https://code.claude.com/docs/en/plugins-reference.md#plugin-caching-and-file-resolution)
- [Skills Guide - Bundle utility scripts](https://code.claude.com/docs/en/skills.md#bundle-utility-scripts-for-zero-context-execution)

## 次のアクション

- 既存のマスタデータ関連スキルで共通ロジックを抽出できる箇所を特定する
- `scripts/`ディレクトリに共通スクリプトを配置する構造へリファクタリングする
- `${CLAUDE_PLUGIN_ROOT}`を使用したスクリプト呼び出しパターンを統一する

---

*このレポートは `/guide-chat` コマンドにより自動生成されました*
