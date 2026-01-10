---
description: 運営仕様書からGLOWマスタデータCSVを作成
argument-hint: <運営仕様パス> <作成したいデータの対象>
---

# 運営仕様からマスタデータ作成

運営仕様書（CSV形式）を読み取り、DBスキーマとテンプレートCSVに基づいて、DB投入可能なマスタデータCSVを作成します。作成後は自動的に検証を実行し、整合性を確認します。

## 引数

- `$ARGUMENTS`: 運営仕様ディレクトリパスまたは特定のCSVファイルパス
  - ディレクトリ例: `マスタデータ/運営仕様/100kanoイベント`
  - ファイル例: `マスタデータ/運営仕様/新規ガチャ/要件/新規ガチャ_仕様書_03_ガチャ設定.csv`

## 使用するスキル

このコマンドは以下のスキルを活用します：

### 1. masterdata-explorer
テーブル構造・カラム・enum値の調査に使用します。

**主なコマンド**:
```bash
# テーブル名検索
.claude/skills/masterdata-explorer/scripts/search_schema.sh tables <キーワード>

# カラム一覧
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns <テーブル名>

# enum値確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh enum <テーブル名> <カラム名>
```

### 2. masterdata-csv-validator
作成したマスタデータCSVの検証に使用します。

**検証コマンド**:
```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv <作成したCSVのパス>
```

## タスク

### ステップ1: 運営仕様の読み込み

提供されたパスが**ディレクトリ**の場合:
1. `要件/`サブディレクトリ内のCSVファイルをリストアップ
2. `*_01_概要.csv`があれば最初に読み込み、全体像を把握
3. その他の要件CSVを順次読み込み
4. `*_05_報酬一覧.csv`があれば、リソース情報として活用

提供されたパスが**特定のCSVファイル**の場合:
1. そのCSVファイルを直接読み込み
2. 同じディレクトリ内に関連ファイル（概要、報酬一覧等）がないか確認
3. 概要ファイルがあれば優先的に読み込む

**出力**: 必要なマスタデータテーブルを特定

### ステップ2: テーブル構造の確認

各テーブルについて、以下を確認します：

1. **DBスキーマの確認**（masterdata-explorerスキル使用）:
   ```bash
   .claude/skills/masterdata-explorer/scripts/search_schema.sh tables <キーワード>
   .claude/skills/masterdata-explorer/scripts/search_schema.sh columns <テーブル名>
   ```

2. **テンプレートCSVの確認**:
   ```bash
   ls projects/glow-masterdata/sheet_schema/<TableName>.csv
   head -3 projects/glow-masterdata/sheet_schema/<TableName>.csv
   ```

3. **既存マスタデータCSVの確認**（参考用）:
   ```bash
   head -10 projects/glow-masterdata/<TableName>.csv
   ```

**重要**: テンプレートCSVとDBスキーマが一致しない場合、**テンプレートを優先**します。

### ステップ3: リソースの存在確認

報酬として設定するリソース（キャラクター、アイテム等）のIDを抽出し、以下の順で検索:

1. 運営仕様の概要ファイル（`*_01_概要.csv`）
2. 報酬一覧ファイル（`*_05_報酬一覧.csv`）
3. その他の要件CSVファイル
4. 既存マスタデータCSV（`projects/glow-masterdata/MstUnit.csv`, `MstItem.csv`等）

**リソースが見つからない場合**:
- ユーザーに確認を求める
- 類似IDを検索して提案する

**検索コマンド例**:
```bash
grep "chara_id_12345" マスタデータ/運営仕様/<運営仕様名>/要件/*.csv
grep "chara_id_12345" projects/glow-masterdata/MstUnit.csv
```

### ステップ4: マスタデータ作成

1. **テンプレートCSVをベースにデータを作成**:
   - テンプレートの列順・列名は**絶対に変更しない**
   - 運営仕様書の内容を適切にマッピング
   - 既存マスタデータのパターンに従う

2. **CSV形式を厳密に遵守**:
   - 改行は `\n` でエスケープ（実際の改行文字は使用しない）
   - ダブルクォートのエスケープは `""`
   - NULL値は空文字列

3. **出力先**: 運営仕様ディレクトリ直下
   - 例: `マスタデータ/運営仕様/<運営仕様名>/MstEvent.csv`

4. **enum値の確認**（masterdata-explorerスキル使用）:
   ```bash
   .claude/skills/masterdata-explorer/scripts/search_schema.sh enum <テーブル名> <カラム名>
   ```

### ステップ5: 検証と修正

作成したCSVを**masterdata-csv-validatorスキル**で検証します：

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv <作成したCSVのパス>
```

**検証結果の確認**:
- `valid: true` → 成功、次のファイルへ
- `valid: false` → エラー修正が必要

**主なエラー種別と修正方法**:
- `column_order`: カラム順序不一致 → テンプレートと一致させる
- `missing_column`: カラム欠損 → 不足カラムを追加
- `extra_column`: 余分なカラム → 不要カラムを削除
- `value_validation_error`: 値の検証エラー → 型・enum値を修正

エラーがあれば修正し、再検証して `valid: true` になるまで繰り返します。

### ステップ6: 完了報告

すべてのマスタデータCSVが作成・検証完了したら、以下を報告します：

1. 作成したファイル一覧
2. 各ファイルの検証結果
3. 注意事項（リソースIDの警告等）

## 出力形式

### 作業開始時

```
運営仕様書からマスタデータを作成します。

【運営仕様】
- パス: <パス>
- 概要ファイル: <あり/なし>
- 要件ファイル: <件数>

【作成予定のマスタデータ】
1. MstEvent.csv - イベント基本情報
2. MstEventI18n.csv - イベント多言語データ
...

作業を開始します。
```

### 作業完了時

```
マスタデータの作成が完了しました。

【作成したファイル】
1. ✅ MstEvent.csv (検証: 成功)
   - パス: <パス>
   - レコード数: <件数>

2. ✅ MstEventI18n.csv (検証: 成功)
   - パス: <パス>
   - レコード数: <件数>

【注意事項】
- <注意事項があれば記載>

全ての検証が成功しました。DB投入可能な状態です。
```

### リソースが見つからない場合

```
マスタデータ作成中にリソースの存在確認でエラーが発生しました。

【問題のあるリソース】
- キャラクターID: `chara_xxx_00001`
  - 検索場所:
    ✅ 運営仕様の概要ファイル: 記載なし
    ✅ 報酬一覧ファイル: 記載なし
    ✅ 既存マスタデータ(MstUnit.csv): 見つからず
  - 類似ID検索: `chara_xxx_` で検索 → 0件

【確認が必要な事項】
1. このIDは正しいですか?
2. 新規リソースの場合、概要ファイルに追加が必要です
3. 既存リソースの場合、IDに誤りがある可能性があります

どのように対応しますか?
```

## 注意事項

### データ作成時

- CSV形式を厳密に遵守（改行は`\n`エスケープ、実際の改行文字は使用禁止）
- テンプレートCSVの列順・列名は**絶対に変更しない**
- 既存マスタデータのパターンに従う
- リソースID（キャラクター、アイテム等）は**必ず存在確認する**

### 検証時

- 検証エラーは自動的に修正を試みる
- 修正できない場合はユーザーに詳細を報告
- `valid: true` になるまで再検証を繰り返す

### ファイルパス

- glow-brain内のコードは**参照専用**
- 実際のマスタデータ変更は本来のリポジトリで行う必要がある
- このコマンドは開発支援・検証用です

### テンプレート依存

- テンプレートCSVが存在しないテーブルは作成できない
- DBスキーマのみでは作成しない（テンプレート最優先）

## テーブル命名規則

| 種類 | 命名規則 | 例 |
|------|---------|-----|
| **DBスキーマ** | snake_case + 複数形 | `mst_events`, `opr_gachas` |
| **CSVファイル** | PascalCase + 単数形 | `MstEvent.csv`, `OprGacha.csv` |

**プレフィックス**:
- `mst_*` / `Mst*` - 固定マスタデータ
- `opr_*` / `Opr*` - 運営施策・期間限定データ

## 参考ファイル

### DBスキーマ
`projects/glow-server/api/database/schema/exports/master_tables_schema.json`

### テンプレートCSV
`projects/glow-masterdata/sheet_schema/*.csv`

### 既存マスタデータ
`projects/glow-masterdata/*.csv`

## トラブルシューティング

### テンプレートファイルが見つからない

**対処法**:
1. CSVファイル名がPascalCase + 単数形になっているか確認（例: `MstEvent.csv`）
2. テンプレートディレクトリを確認:
   ```bash
   ls projects/glow-masterdata/sheet_schema/
   ```

### テーブル名が推測できない

**対処法**:
テーブル名の命名規則を確認し、DBスキーマから正しい名前を検索:
```bash
.claude/skills/masterdata-explorer/scripts/search_schema.sh tables <キーワード>
```

### 検証エラーが解決できない

**対処法**:
エラーメッセージの詳細を確認し、以下を参照:
- `.claude/skills/masterdata-csv-validator/references/error-examples.md`
- `.claude/skills/masterdata-csv-validator/references/validation-rules.md`

---

それでは、運営仕様書からマスタデータCSVを作成しましょう！
