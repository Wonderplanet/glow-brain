---
name: masterdata-doc-generator
description: GLOWゲームプロジェクトのマスタデータ設定方法ドキュメントを自動生成します。サーバーコードからENUM値を抽出し、実際のマスタデータから設定例をサンプリングして、プランナー（非エンジニア）向けの正確なドキュメントを生成します。ミッション、ガチャ、クエスト、アイテムなどのマスタデータ設定ドキュメント作成、GLOW、マスターデータで使用します。
---

# Masterdata Doc Generator

## 概要

GLOWゲームプロジェクトのマスタデータ設定方法ドキュメントを自動生成するスキルです。手動作成で発生しがちなENUM値の間違い（キャメルケース/スネークケースの混同）や、フィールド定義の不正確さを防ぎ、サーバーコードと実マスタデータに基づいた正確なドキュメントを生成します。

## ワークフロー

### ステップ1: コンテンツの特定と関連ファイル調査

コンテンツ名（例: "ミッション"）から関連するファイルを特定します。

1. **sheet_schemaの検索**

```bash
ls projects/glow-masterdata/sheet_schema/ | grep -i [keyword]
```

キーワードマッピング例:
- ミッション → mission
- ガチャ → gacha
- クエスト → quest
- アイテム → item

見つかったシート名（例: `MstMissionDaily.csv`, `MstMissionAchievement.csv`）を記録します。

2. **DBテーブルの特定**

masterdata-schema-inspectorスキルを使用、またはmaster_tables_schema.jsonを直接読み取ります。

```bash
# テーブル一覧を取得
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '.databases.mst.tables | keys[]' | grep -i [keyword]
```

詳細な手順は`references/schema-query-guide.md`を参照してください。

### ステップ2: スキーマ情報の収集

各テーブルのスキーマ情報を収集します。

**方法A: masterdata-schema-inspectorスキルを使用**

```
Skill(skill: "masterdata-schema-inspector", args: "[テーブル名]")
```

**方法B: master_tables_schema.jsonを直接読み取り**

```bash
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '.databases.mst.tables.[テーブル名]'
```

収集する情報:
- 列名、型、NULL許容、デフォルト値、コメント
- ENUM型の列がある場合は、その選択肢

### ステップ3: ENUM値の抽出

サーバーコードからENUM定義を抽出します。これが**最も重要**なステップです。

1. **ENUM定義ファイルを検索**

```bash
find projects/glow-server/api/app/Domain -name "*.php" -path "*/Enums/*"
```

コンテンツに関連するENUMを特定（例: ミッション→MissionCriterionType.php）

2. **extract_enums.pyスクリプトを使用**

```bash
python3 scripts/extract_enums.py \
  projects/glow-server/api/app/Domain/Mission/Enums/MissionCriterionType.php
```

または、直接Readツールで読み取り、`case XXX = 'ActualValue';`パターンから値を抽出します。

**重要**: ENUM値はキャメルケース（`LoginCount`, `StageClearCount`）です。スネークケース（`login_count`）は間違いです。

### ステップ4: 実マスタデータからの設定例サンプリング

実際のマスタデータCSVから設定例を抽出します。

1. **sample_masterdata.pyスクリプトを使用**

```bash
python3 scripts/sample_masterdata.py \
  projects/glow-masterdata/[FileName].csv --limit 5
```

2. **手動サンプリング**

```
Read(file_path: "projects/glow-masterdata/[FileName].csv", limit: 10)
```

GLOWのマスタデータCSV形式:
- 1行目: 列名（ヘッダー）
- 2行目以降: データ

注意: `sheet_schema/` ディレクトリのCSVファイルは異なる形式（memo, TABLE, ヘッダー, データの4行形式）

選択基準:
- 典型的なパターン（最初の数行）
- 多様性のあるサンプル
- 空欄や特殊値（`__NULL__`）を含む例

### ステップ5: ドキュメント生成

`references/doc-template.md`のテンプレートに従ってMarkdownドキュメントを生成します。

**構成**:

```markdown
# [コンテンツ名] マスタデータ設定方法

## 目次
## 概要
## [コンテンツ名]で使用するテーブル
## 各テーブルの設定方法
## 設定例
## 注意事項とチェックポイント
## 付録：主要なENUM一覧（必要に応じて）
```

**重要な記述ルール**:

- **型の表記**: 非エンジニア向けに変換
  - `varchar(255)` → **文字列**
  - `int` → **整数**
  - `enum` → **列挙型**（選択肢を併記）

- **ENUM値**: キャメルケースで正確に記載
  - ✓ `LoginCount`, `SpecificStageClearCount`
  - ✗ `login_count`, `specific_stage_clear_count`

- **NULL許容**: `nullable: true` → ○、`nullable: false` → （空欄）

- **設定例**: Markdownテーブル形式、実データベース
  - 空欄は「（空欄）」と明記
  - `__NULL__`はそのまま記載
  - ENABLE、release_keyは除外

- **外部キー**: 「（外部参照）」または「（外部キー）」で明示

詳細は`references/doc-template.md`を参照してください。

### ステップ6: 保存と検証

生成したドキュメントを保存します。

**保存先**: `マスタデータ/設定方法/[コンテンツ名].md`

**検証項目**:
- [ ] すべてのENUM値がキャメルケースで記載されているか
- [ ] 実際のマスタデータに基づく設定例が含まれているか
- [ ] 型の表記が非エンジニア向けに変換されているか
- [ ] `__NULL__`の使い方が説明されているか
- [ ] よくあるミスのセクションに、キャメルケース/スネークケースの混同が含まれているか

## リソース

### scripts/

- **extract_enums.py**: PHPのENUMファイルから値を抽出
- **sample_masterdata.py**: CSVファイルから設定例を抽出

### references/

- **schema-query-guide.md**: スキーマ情報の取得方法の詳細ガイド
  - masterdata-schema-inspectorスキルの使い方
  - master_tables_schema.jsonの読み方
  - ENUM値の抽出方法
  - 実マスタデータの確認方法

- **doc-template.md**: ドキュメント生成のテンプレートと記述ルール
  - セクション別ガイドライン
  - ENUM値の記載ルール
  - i18nフィールドの記載方法
  - よくあるミスの記載例

## よくある問題と解決策

### ENUM値がスネークケースで記載されてしまう

**問題**: ドキュメントに`quest_clear`と記載してしまう

**解決**: サーバーコードのENUM定義を必ず確認する。`case XXX = 'ActualValue';`の`ActualValue`が正しい値です。

### 設定例が抽象的（XXX等）

**問題**: 設定例に`xxx_001`のような抽象的な値を使用

**解決**: 実際のマスタデータCSVから具体例を抽出する。sample_masterdata.pyを使用するか、直接CSVを読み取ります。

### `__NULL__`の扱いが不明確

**問題**: NULL許容フィールドで空欄にすべきか`__NULL__`にすべきか不明

**解決**: 実マスタデータを確認する。GLOWプロジェクトでは、開放条件を設定しない場合に`unlock_criterion_type`に`__NULL__`を使用します。

## 使用例

**例1: ミッションのドキュメント生成**

```
ユーザー: "ミッションのマスタデータ設定方法ドキュメントを作成して"

→ Step 1: sheet_schemaで"mission"を検索
→ Step 2: DBテーブルを特定（mst_mission_dailies, mst_mission_achievements等）
→ Step 3: MissionCriterionType.phpからENUM値を抽出
→ Step 4: MstMissionDaily.csv等から設定例をサンプリング
→ Step 5: テンプレートに従ってドキュメント生成
→ Step 6: マスタデータ/設定方法/ミッション.md に保存
```

**例2: ガチャのドキュメント生成**

```
ユーザー: "ガチャの設定ドキュメントを生成"

→ 同様のワークフローでガチャ関連テーブルを分析
→ GachaType等のENUM値を抽出
→ マスタデータ/設定方法/ガチャ.md に保存
```
