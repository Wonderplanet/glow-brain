---
name: masterdata-generator
description: 要件に基づいてマスタデータを生成。スキーマ調査、CSV生成、検証、レポート作成を一括実行。マスタデータを生成、マスタデータ作成で使用。
allowed-tools: Read, Write, Bash, Skill, TodoWrite, Task
argument-hint: 要件の説明
---

# マスタデータ生成スキル

要件に基づいてマスタデータCSVファイルを生成し、スキーマ検証とレポート作成を行います。

## ⚠️ 重要な原則

**タスク完遂**: 要件に含まれる全てのマスタデータを最後まで作り切ることが目的です。詳細は [references/completion-principles.md](references/completion-principles.md) を参照してください。

### 完了条件

- ✅ 全てのマスタデータが生成されている
- ✅ 全てのCSVが検証済み
- ✅ REPORTに「未作成」セクションが存在しない

## 入出力

**入力**: 要件の説明（例: 「新春ガチャを追加。期間は2026-01-01〜01-31。10連で確定報酬あり」）

**出力**:
- `マスタデータ/運用仕様書/[施策名]/[ModelName].csv` - 生成されたマスタデータ
- `マスタデータ/運用仕様書/[施策名]/REPORT.md` - 生成レポート

## ワークフロー

### 1. 要件分析

要件を分析し、必要な全てのマスタデータをリストアップします。

**重要**: この段階で全てのマスタデータを洗い出してください。後で「未作成」として残すのは禁止です。

**TodoWriteでタスク管理**:
```
TodoWrite([
  {"content": "要件を分析してマスタデータリストを作成", "status": "in_progress", ...},
  {"content": "<ModelName1>.csv を生成", "status": "pending", ...},
  {"content": "<ModelName2>.csv を生成", "status": "pending", ...},
  ...
])
```

分析項目:
- データの目的（ガチャ/イベント/ミッション等）
- 対象モデル（Mst/Opr）を**全て**リストアップ
- データ量
- 関連データの洗い出し

### 2. スキーマ調査

**必須**: 各モデルのスキーマ情報を `masterdata-schema-inspector` で調査します。

```
Skill(skill: "masterdata-schema-inspector", args: "<ModelName>")
```

取得する情報:
- テーブル名とカラム定義
- nullable制約、ENUM選択肢
- CSVテンプレートファイルのパス

**テンプレートファイルのコピー**:
```bash
cp projects/glow-masterdata/sheet_schema/[ModelName].csv マスタデータ/運用仕様書/[施策名]/[ModelName].csv
```

テンプレートファイルは3行ヘッダー構造です:
- 1行目: `memo`
- 2行目: `TABLE,モデル名,モデル名,...`
- 3行目: `ENABLE,カラム1,カラム2,...` ← **このヘッダーに完全に従う**

### 3. データ設計

スキーマ調査結果に基づき、データを設計します。

設計項目:
- データスキーマ（カラム定義と型）
- データ内容（要件を満たす具体的な値）
- 整合性（IDの重複回避、外部キー制約）
- 命名規則（asset_key、id のパターン）

**不明点の対処**: 仕様が不明確な場合も、既存データのパターンから推測して設計を続けてください。詳細は [references/csv-rules.md](references/csv-rules.md) を参照。

### 4. CSV生成

設計に基づいて**全ての**CSVファイルを生成します。

```csv
ENABLE,id,column1,column2,...
e,value1,value2,value3,...
e,value1,value2,value3,...
```

**重要な制約**:
- **`__NULL__` ルール**: nullable列のみで使用可能。NOT NULL列では空文字列を使用。
- **日時形式**: `YYYY-MM-DD HH:MM:SS`
- **release_key**: `YYYYMMDD0` 形式（例: `202601010`）
- **ID重複**: 既存データと重複しないこと

詳細なCSV作成ルールは [references/csv-rules.md](references/csv-rules.md) を参照してください。

### 5. スキーマ検証

**必須**: 全てのCSVファイルを `masterdata-validator` で検証します。

```
Skill(skill: "masterdata-validator", args: "<csv_file_path> <ModelName>")
```

検証内容:
- カラムの存在確認
- データ型の検証（ENUM、INT、DATETIME）
- 制約の検証（PRIMARY KEY、UNIQUE、NOT NULL）
- `__NULL__` の使用チェック

検証結果の修正ログは、REPORTに記載してください。

### 6. レポート作成

**全てのマスタデータ生成後に** REPORT.md を作成します。

ファイルパス: `マスタデータ/運用仕様書/[施策名]/REPORT.md`

レポートテンプレートは [references/report-template.md](references/report-template.md) を参照してください。

**重要**: レポートに「未作成のマスタデータ」セクションを作成してはいけません。

## 依存スキル

- `masterdata-schema-inspector`: スキーマ情報の調査
- `masterdata-validator`: CSV検証と自動修正

## 参照ドキュメント

必要に応じて以下のドキュメントを参照してください：

- **[references/completion-principles.md](references/completion-principles.md)** - タスク完遂の原則と完了条件
- **[references/csv-rules.md](references/csv-rules.md)** - CSV作成の詳細ルール（`__NULL__`、テンプレート、フォーマット）
- **[references/report-template.md](references/report-template.md)** - REPORTファイルのテンプレート
- **[references/best-practices.md](references/best-practices.md)** - 実践的なアドバイスとトラブルシューティング

## ディレクトリ構造

```
マスタデータ/
└── 施策/
    └── [施策名]/
        ├── 要件/                  ← ユーザーが事前に用意
        ├── 要件ファイル構成.md     ← 別スキルで生成済み
        ├── REPORT.md              ← このスキルで生成
        ├── [ModelName1].csv       ← このスキルで生成
        └── [ModelName2].csv       ← このスキルで生成
```

## 使用例

```
Skill(skill: "masterdata-generator", args: "新春限定ガチャを追加。期間は2026年1月1日〜1月31日。10連ガチャで1回確定報酬あり。")
```

期待される動作:
1. 要件分析 → TodoWriteでタスク管理
2. 各マスタデータに対して:
   - schema-inspector でスキーマ調査
   - テンプレートファイルをコピー
   - CSVデータを生成
   - validator で検証
3. REPORT.md を生成
4. 全成果物の確認

---

このスキルを使用することで、マスタデータを効率的に生成できます。
