---
name: masterdata-setup-doc
description: GLOWゲームコンテンツのマスタデータ設定方法ドキュメントを生成。glow-client/serverコード調査、sheet_schema、master_tables_schema.json解析により、非エンジニア（プランナー）向け日本語ドキュメントを自動生成。降臨バトル、ガチャ、ミッション、イベント等のマスタデータ設定方法ドキュメント作成で使用。
allowed-tools: Read, Glob, Grep, Task, Write, TodoWrite
argument-hint: コンテンツ名（例: 降臨バトル、ガチャ、ミッション）
---

# マスタデータ設定方法ドキュメント生成

## 概要

GLOWプロジェクトの各種ゲームコンテンツ（降臨バトル、ガチャ、ミッション等）のマスタデータ設定方法ドキュメントを自動生成するスキル。コンテンツ名を指定するだけで、コードベース調査から非エンジニア向けドキュメント生成まで一括実行する。

## ワークフロー

### 1. コードベース調査（自動実行）

指定されたコンテンツに関連する以下の情報を徹底調査：

- **sheet_schema**: `projects/glow-masterdata/sheet_schema/` 内のCSVヘッダ定義
- **DB tables**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json` のテーブル定義
- **Client code**: `projects/glow-client/` のUnity/C#実装
- **Server code**: `projects/glow-server/` のLaravel/PHP実装（モデル、UseCase、Service層）

Task tool (Explore agent) を使用して効率的に調査を実行する。

### 2. ドキュメント生成（自動実行）

調査結果を基に、以下の構成でドキュメントを生成：

```markdown
# [コンテンツ名]のマスタデータ設定方法

## 概要
- コンテンツの説明、タイプ、仕組み

## [コンテンツ名]で使用するテーブル
| シート | 対応するDBテーブル | 用途 |

## 各テーブルの設定方法
### [シート名]
| 列名 | 型 | NULL許容 | 説明 | 設定例 |

## 設定例
### [シート名]の設定例（表形式）

## 注意事項とチェックポイント
- ID一意性、外部キー整合性など
```

### 3. 自動保存

生成したドキュメントを `マスタデータ/設定方法/[コンテンツ名].md` に自動保存する。

## ドキュメント記述ルール

### 型の表記（非エンジニア向け）

DBスキーマの型を以下のように変換：

- `string`, `varchar`, `text` → **文字列**
- `int`, `integer`, `bigint` → **整数**
- `decimal`, `float`, `double` → **少数**
- `datetime`, `timestamp` → **日時**
- `enum` → **列挙型**（選択肢を併記）

### NULL許容の表記

- nullable: **○**
- not null: （空白）

### 除外フィールド

以下のフィールドは説明・例に含めない：

- `ENABLE`
- `release_key`

### i18n（多言語）フィールドの扱い

- `.ja`, `.en` サフィックス付きフィールドは自動的に `_i18n` テーブルに格納される
- sheet_schema には `_i18n` 用の独立したCSVは存在しない（1つのシートから複数DBテーブルに分割される）

### 設定例の表記

- **CSV形式ではなく、Markdownテーブル形式**で記載
- 各列に対応する値が一目で分かるように
- ENABLE, release_key 列は除外

## 使用例

```
降臨バトルのドキュメント生成:
/masterdata-setup-doc 降臨バトル

ガチャのドキュメント生成:
/masterdata-setup-doc ガチャ

ミッションのドキュメント生成:
/masterdata-setup-doc ミッション
```

## 参考ドキュメント

実際の出力例として `references/降臨バトル.md` を参照できる。このファイルは降臨バトルのマスタデータ設定方法ドキュメントで、生成するドキュメントの構成と記述スタイルのテンプレートとして使用する。

## 調査時の重要な注意点

### Sheet Schema vs DB Tables の違い

- **Sheet Schema**: プランナーがデータ入力に使うCSVヘッダ定義（`glow-masterdata/sheet_schema/`）
- **DB Tables**: サーバー側で実際にデータが格納されるテーブル（`master_tables_schema.json`）
- **重要**: 1:1対応ではない。1つのsheet schemaが複数のDBテーブルに分割されることがある

### コードベース調査の優先順位

1. **sheet_schema CSVファイル**: まずプランナーが扱うシート定義を確認
2. **master_tables_schema.json**: 実際のDB構造を確認（フィールド型、NULL制約など）
3. **Server code**: ビジネスロジック、外部キー関係、バリデーションルールを確認
4. **Client code**: UI表示、使用パターンを確認

### 調査時の確認事項

- 各シートがどのDBテーブルに対応するか
- 列の型、NULL許容、デフォルト値
- ENUM型の選択肢
- 外部キー関係
- 実際の使用例（既存のマスタデータから）

## 実装パターン

```python
# 1. コンテンツ名を受け取る
content_name = args  # 例: "降臨バトル"

# 2. Task tool で関連コードを調査
# - sheet_schema/*.csv
# - master_tables_schema.json
# - glow-server, glow-client の関連コード

# 3. 調査結果を基にドキュメント生成
# - references/降臨バトル.md をテンプレートとして参照
# - 非エンジニア向けの分かりやすい日本語で記述
# - 表形式で視覚的に分かりやすく

# 4. 自動保存
# - マスタデータ/設定方法/[コンテンツ名].md
```
