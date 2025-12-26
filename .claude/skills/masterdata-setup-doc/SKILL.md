---
name: masterdata-setup-doc
description: GLOWゲームコンテンツのマスタデータ設定方法ドキュメントを生成。glow-client/serverコード調査、sheet_schema、master_tables_schema.json解析により、非エンジニア（プランナー）向け日本語ドキュメントを自動生成。降臨バトル、ガチャ、ミッション、イベント等のマスタデータ設定方法ドキュメント作成で使用。
allowed-tools: Read, Glob, Grep, Task, Write, TodoWrite, Bash
argument-hint: コンテンツ名（例: 降臨バトル、ガチャ、ミッション）
---

# マスタデータ設定方法ドキュメント生成

GLOWプロジェクトの各種ゲームコンテンツのマスタデータ設定方法ドキュメントを自動生成する。コンテンツ名を指定するだけで、コードベース調査から非エンジニア向けドキュメント生成まで一括実行する。

## ワークフロー

### 1. コンテンツ分析

指定されたコンテンツに関連する情報を調査：

- **sheet_schema**: `projects/glow-masterdata/sheet_schema/` 内のCSVヘッダ定義
- **DB schema**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- **Server code**: Laravel実装（モデル、UseCase、Service層）
- **Client code**: Unity/C#実装

調査には `scripts/analyze_content.py` を使用し、構造化されたJSON形式で結果を出力する。

### 2. ドキュメント生成

`scripts/generate_doc.py` で調査結果を基にMarkdownドキュメントを生成：

```markdown
# [コンテンツ名]のマスタデータ設定方法
## 概要
## [コンテンツ名]で使用するテーブル
## 各テーブルの設定方法
## 設定例
## 注意事項とチェックポイント
```

### 3. 保存

生成したドキュメントを `マスタデータ/設定方法/[コンテンツ名].md` に保存。

## 使用例

```bash
# 降臨バトルのドキュメント生成
python scripts/analyze_content.py "降臨バトル" > /tmp/analysis.json
python scripts/generate_doc.py /tmp/analysis.json "降臨バトル" > マスタデータ/設定方法/降臨バトル.md
```

スキル実行時は上記を自動化する。

## リファレンス

詳細な記述ルールやスキーマ調査方法は以下を参照：

- **[doc-writing-guide.md](references/doc-writing-guide.md)**: ドキュメント記述ルール（型表記、NULL許容、除外フィールドなど）
- **[schema-investigation.md](references/schema-investigation.md)**: スキーマ調査の詳細手順と注意点
- **[降臨バトル.md](references/降臨バトル.md)**: 実際の出力例（テンプレートとして使用）
