---
name: masterdata-releasekey-reporter
description: GLOWマスタデータのリリースキー別抽出とレポート生成。テーブル別CSV・統計JSON・DuckDBクエリで柔軟な分析が可能。「リリースキー」「release key」「マスタデータ抽出」「データ投入」「リリース内容」などのキーワードで使用します。
disable-model-invocation: false
---

# Masterdata Release Key Reporter

## 概要

GLOWプロジェクトのマスタデータから特定のリリースキーに関連するデータを抽出し、包括的なレポートを生成するスキルです。93テーブル以上のマスタデータから該当データを自動抽出し、テーブル別CSV・統計JSON・DuckDBクエリによる柔軟な分析が可能です。

## 使用タイミング

- 特定リリースで投入されたデータの全容を確認したい
- リリース前後の影響範囲を調査したい
- データ投入内容をドキュメント化したい
- 運営施策の実装内容をレビューしたい
- テーブル間の関連データをJOINして分析したい

## 出力構造

リリースキーごとに以下のディレクトリ構造で出力します:

```
domain/raw-data/masterdata/released/{RELEASE_KEY}/
├── stats/
│   ├── summary.json          # 全体統計（テーブル数、行数、カテゴリ集計）
│   └── tables.json           # テーブル別詳細統計
├── tables/
│   └── {TableName}.csv       # テーブル別CSVファイル（ヘッダー付き）
└── release_{KEY}_report.md   # Markdownレポート
```

### stats/summary.json の例

```json
{
  "release_key": "202512020",
  "extraction_date": "2026-01-10T00:35:00Z",
  "total_tables": 93,
  "total_rows": 2901,
  "categories": {
    "Mst": { "tables": 75, "rows": 2500 },
    "Opr": { "tables": 10, "rows": 300 },
    "I18n": { "tables": 8, "rows": 101 }
  },
  "largest_tables": [
    { "name": "MstAutoPlayerSequence", "rows": 354 },
    { "name": "OprGachaPrize", "rows": 247 }
  ]
}
```

### stats/tables.json の例

```json
{
  "MstAdventBattle": {
    "rows": 1,
    "columns": 22,
    "category": "Mst",
    "file_size_kb": 1
  },
  "MstAdventBattleClearReward": {
    "rows": 5,
    "columns": 10,
    "category": "Mst",
    "file_size_kb": 1
  }
}
```

## ワークフロー

### Phase 1: データ抽出

スクリプトを実行してテーブル別CSV、統計JSONを一括生成します:

```bash
.claude/skills/masterdata-releasekey-reporter/scripts/extract_release_data.sh <RELEASE_KEY>
```

**例:**
```bash
.claude/skills/masterdata-releasekey-reporter/scripts/extract_release_data.sh 202512020
```

**処理内容:**
1. `projects/glow-masterdata/*.csv` から該当リリースキーを含むデータを検索
2. テーブル別CSVファイルを `tables/` ディレクトリに生成
3. 統計JSONを `stats/` ディレクトリに生成

### Phase 2: 統計確認

`stats/summary.json` を読み込んで全体像を把握します:

```bash
cat domain/raw-data/masterdata/released/202512020/stats/summary.json | jq .
```

**確認すべき情報:**
- 総テーブル数・総行数
- カテゴリ別集計（Mst/Opr/I18n）
- 最大行数のテーブルTOP10

### Phase 3: 詳細分析

#### 方法1: 個別テーブルCSVの読み込み

各テーブルCSVは平均4KB程度で、Readツールの256KB制限内に収まります:

```bash
# Readツールで直接読み込み
/Users/.../domain/raw-data/masterdata/released/202512020/tables/MstEvent.csv
```

#### 方法2: DuckDBクエリ

柔軟なSQL分析が可能です:

**基本的なクエリ:**
```bash
# query_release.sh経由（推奨）
.claude/skills/masterdata-releasekey-reporter/scripts/query_release.sh 202512020 table MstEvent

# DuckDB直接起動
duckdb -init .claude/skills/masterdata-releasekey-reporter/.duckdbrc
```

**JOIN分析:**
```sql
-- イベントと多言語名称を結合
SELECT
  e.id,
  e.start_at,
  e.end_at,
  i.name
FROM read_csv('domain/raw-data/masterdata/released/202512020/tables/MstEvent.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__') e
LEFT JOIN read_csv('domain/raw-data/masterdata/released/202512020/tables/MstEventI18n.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__') i
  ON e.id = i.mst_event_id
WHERE i.language = 'ja';
```

**集計分析:**
```sql
-- リソースタイプ別報酬集計
SELECT
  resource_type,
  COUNT(*) as count,
  SUM(resource_amount) as total
FROM read_csv('domain/raw-data/masterdata/released/202512020/tables/MstAdventBattleReward.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
GROUP BY resource_type
ORDER BY count DESC;
```

詳細なクエリ例は `references/query-examples.md` を参照してください。

### Phase 4: レポート生成

統計JSONと分析結果をもとに、Markdownレポートを作成します。

**レポート保存先:**
`domain/raw-data/masterdata/released/{リリースキー}/release_{RELEASE_KEY}_report.md`

**レポートに含める内容:**
1. **概要セクション**: リリースキー、テーブル数、総行数、抽出日時
2. **データ投入サマリー**: このリリースで何が追加されたか
3. **主要な機能追加**: 大きな機能単位でまとめる
4. **機能別データ詳細**: カテゴリごとに整理
5. **多言語対応**: I18nテーブルの一覧
6. **まとめ**: リリースの特徴や規模感

**I18n日本語名の活用（重要）:**

レポート作成時は、IDだけでなくI18nテーブルから日本語名を取得して併記することで、内容を分かりやすくします。

**日本語名の取得方法:**
1. 該当するI18nテーブルが存在するか確認（例: `MstEvent` → `MstEventI18n`）
2. DuckDBでJOINクエリを実行して日本語名を取得（`language = 'ja'` でフィルタ）
3. レポートにID + 日本語名を併記

**併記フォーマット例:**
```markdown
- event_osh_00001（【推しの子】 いいジャン祭）
- chara_osh_00001（B小町不動のセンター アイ）
```

**よく使うI18nテーブルとカラム:**
- `MstUnitI18n`: `mst_unit_id` → `name`（キャラクター名）
- `MstEventI18n`: `mst_event_id` → `name`（イベント名）
- `MstStageI18n`: `mst_stage_id` → `name`（ステージ名）
- `MstQuestI18n`: `mst_quest_id` → `name`（クエスト名）
- `MstItemI18n`: `mst_item_id` → `name`（アイテム名）
- `MstEmblemI18n`: `mst_emblem_id` → `name`（エンブレム名）
- `MstAdventBattleI18n`: `mst_advent_battle_id` → `name`（降臨バトル名）
- `OprGachaI18n`: `opr_gacha_id` → `name`（ガチャ名）
- `MstMissionEventI18n`: `mst_mission_event_id` → `description`（ミッション説明）
- `MstAttackI18n`: `mst_attack_id` → `description`（攻撃説明）

**JOINクエリ例:**
```bash
# イベント + 日本語名
.claude/skills/masterdata-releasekey-reporter/scripts/query_release.sh {KEY} sql \
  "SELECT e.id, i.name FROM read_csv('domain/raw-data/masterdata/released/{KEY}/tables/MstEvent.csv', AUTO_DETECT=TRUE) e \
   LEFT JOIN read_csv('domain/raw-data/masterdata/released/{KEY}/tables/MstEventI18n.csv', AUTO_DETECT=TRUE) i \
   ON e.id = i.mst_event_id AND i.language = 'ja'"
```

詳細なクエリパターンは `references/query-examples.md` を参照してください。

**レポートのトーン:**
- 簡潔かつ分かりやすく
- 具体的な数値を含める
- **IDには必ず日本語名を併記する**（I18nテーブルが存在する場合）
- 投入されたデータの目的や内容を推測して記載
- 技術者と非技術者の両方が理解できる言葉で

### Phase 5: 結果の提示

ユーザーに以下を報告します:
- 抽出されたテーブル数と総行数
- レポートの要約（主要な機能追加や特徴）
- 生成されたファイルのパス

## DuckDBクエリツール

### query_release.sh

便利なクエリコマンドを提供します:

```bash
# 特定テーブルの全データ取得
./scripts/query_release.sh 202512020 table MstEvent

# カテゴリ別テーブル一覧
./scripts/query_release.sh 202512020 category Mst

# パターン検索（IDやasset_keyで）
./scripts/query_release.sh 202512020 search quest_raid

# 統計情報の表示
./scripts/query_release.sh 202512020 stats

# 任意のSQLクエリ
./scripts/query_release.sh 202512020 sql "SELECT COUNT(*) FROM read_csv(...)"
```

### DuckDB直接起動

```bash
duckdb -init .claude/skills/masterdata-releasekey-reporter/.duckdbrc
```

初期化ファイルで以下が設定されます:
- CSV出力モード
- ヘッダー表示ON
- 使用方法のヘルプメッセージ

## エラーハンドリング

### リリースキーが見つからない場合

```
エラー: リリースキー {RELEASE_KEY} を含むデータが見つかりませんでした
```

**対処方法:**
1. リリースキーの桁数や形式が正しいか確認
2. マスタデータが最新の状態か確認
3. 類似のリリースキーを検索して提案

### マスタデータディレクトリが見つからない場合

```
エラー: マスタデータディレクトリが見つかりません: projects/glow-masterdata
```

**対処方法:**
1. glow-brainリポジトリのルートディレクトリで実行しているか確認
2. `projects/glow-masterdata` ディレクトリが存在するか確認

### jqコマンドが見つからない場合

```
エラー: jqコマンドが見つかりません。インストールしてください。
```

**対処方法:**
```bash
# macOS
brew install jq

# Linux
sudo apt-get install jq
```

### DuckDBが見つからない場合

```
エラー: duckdbコマンドが見つかりません。
```

**対処方法:**
```bash
# macOS
brew install duckdb

# Linux
https://duckdb.org/docs/installation/
```

## スクリプト詳細

### extract_release_data.sh

**入力:**
- 第1引数: リリースキー（必須）

**処理:**
1. マスタデータディレクトリの存在確認
2. jqコマンドの存在確認
3. 全CSVファイルから該当リリースキーを含む行を抽出
4. テーブル別CSVファイルを生成
5. 統計JSON（summary.json / tables.json）を生成

**出力:**
- `domain/raw-data/masterdata/released/{リリースキー}/tables/{テーブル名}.csv`
- `domain/raw-data/masterdata/released/{リリースキー}/stats/summary.json`
- `domain/raw-data/masterdata/released/{リリースキー}/stats/tables.json`

### query_release.sh

**入力:**
- 第1引数: リリースキー（必須）
- 第2引数: コマンド（table/category/search/sql/stats）
- 第3引数以降: コマンド固有のパラメータ

**処理:**
- DuckDBを使用してテーブルCSVをクエリ
- コマンドに応じた出力を生成

## 使用例

### 例1: 基本的な使用

**ユーザー:**
> リリースキー202512020のデータを抽出して

**応答フロー:**
1. スクリプトを実行してテーブル別CSV・統計JSONを生成
```bash
.claude/skills/masterdata-releasekey-reporter/scripts/extract_release_data.sh 202512020
```
2. `stats/summary.json` を読み込んで全体像を把握
3. 必要なテーブルCSVを選択的に読み込んで分析
4. レポートを作成して保存
5. レポートの要約をユーザーに提示

### 例2: DuckDBでJOIN分析

**ユーザー:**
> リリースキー202512020の降臨バトルと報酬を紐付けて分析したい

**応答フロー:**
1. DuckDBで複数テーブルをJOINするクエリを実行
```sql
SELECT
  ab.id as battle_id,
  r.resource_type,
  SUM(r.resource_amount) as total
FROM read_csv('domain/raw-data/masterdata/released/202512020/tables/MstAdventBattle.csv', ...) ab
JOIN ...
GROUP BY ab.id, r.resource_type;
```
2. クエリ結果を解釈してユーザーに提示

### 例3: 統計情報の確認

**ユーザー:**
> リリースキー202512020の統計を教えて

**応答フロー:**
1. `stats/summary.json` を読み込む
2. 主要な統計情報（テーブル数、行数、カテゴリ別内訳）を提示
3. 最大行数のテーブルTOP10を表示

## 注意事項

- **projects/glow-masterdata** は参照専用です。このスキルは読み取り専用で動作します。
- 大量のデータを抽出する場合、処理に時間がかかることがあります。
- 各テーブルCSVファイルは平均4KB程度で、Readツールの256KB制限内に収まります。
- DuckDBを使用することで、大規模なデータでもメモリ効率よく分析できます。
- レポートはClaudeが統計JSONとテーブルCSVを分析して作成するため、データの内容を理解した質の高いレポートが生成されます。

## テーブル命名規則

GLOWプロジェクトのマスタデータテーブル:

| プレフィックス | 意味 | 例 |
|--------------|------|-----|
| **Mst** | 固定マスタデータ | MstUnit, MstStage, MstItem |
| **Opr** | 運営施策・期間限定データ | OprGacha, OprCampaign, OprProduct |
| **I18n** | 多言語対応テーブル | MstUnitI18n, MstEventI18n |

レポートではこれらのカテゴリ別に集計されます。

## 参考資料

- [DuckDBクエリ例集](./references/query-examples.md)
- [masterdata-explorer スキル](../.claude/skills/masterdata-explorer/SKILL.md) - DBスキーマ調査用
