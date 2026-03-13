---
name: masterdata-ingame-enemy-explainer
description: GLOWインゲーム敵キャラの詳細解説ドキュメント生成。指定した敵キャラのステータスバリエーション・出現シーケンスパターン・行動傾向・コマ効果使用実績をDuckDBで分析し、1キャラ1MDファイルを生成します。「敵キャラ解説」「enemy character」「キャラ詳細解説」「敵キャラ詳細」などのキーワードで使用します。
argument-hint: "[--output-dir <出力先>] <キャラクターID or キャラクター名>"
---

# GLOWインゲーム敵キャラ詳細解説生成スキル

引数で指定された敵キャラクターを分析し、詳細解説MDドキュメントを生成して保存します。

**分析元データ**: `projects/glow-masterdata/*.csv`（過去リリース済みデータ）
**対象テーブル**: MstEnemyCharacter / MstEnemyCharacterI18n / MstEnemyStageParameter / MstAttack / MstAttackElement / MstAutoPlayerSequence / MstInGame / MstPage / MstKomaLine

---

## 引数

### 書式

```
[--output-dir <出力先ディレクトリ>] <キャラクターID or キャラクター名>
```

### 引数一覧

| 引数 | 必須 | 説明 | 例 |
|------|------|------|----|
| **キャラクターID** | ✅（名前と排他） | MstEnemyCharacter.id で対象を特定 | `enemy_kai_00001` |
| **キャラクター名** | ✅（IDと排他） | MstEnemyCharacterI18n.name（日本語）で対象を特定 | `怪獣 本獣` |
| **--output-dir** | ❌ | MDファイルの保存先ディレクトリ。省略時はカレントディレクトリ | `--output-dir specs/enemy-docs/` |

### 呼び出し例

```
# IDで指定
enemy_kai_00001 の詳細解説を作って

# 名前で指定
怪獣 本獣の敵キャラ詳細解説

# 出力先を指定
--output-dir vd-ingame-design-creator/vd_all/docs/ enemy_kai_00001 の敵キャラ解説生成
```

---

## 分析ワークフロー

### Step 1: キャラクター特定

引数を解析して対象キャラクターを特定する。

- `--output-dir` が指定されていれば保存先ディレクトリとして記録（省略時はカレントディレクトリ）
- キャラクターID・名前以外の追加テキスト引数（例: 「メインクエストNormalのみ対象」「VD限定」）は**コンテンツフィルタ**として記録する
- フィルタが指定された場合、Step 2〜6 の全クエリに一貫して適用する
- DuckDB で MstEnemyCharacter + MstEnemyCharacterI18n(language='ja') を JOIN してキャラ情報を取得
- 出力ファイル名を決定: `{MstEnemyCharacter.id}_{MstEnemyCharacterI18n.name}.md`

DuckDBクエリは [references/duckdb-queries.md](references/duckdb-queries.md) の Step 1 クエリを使用。

### Step 2: ステータスバリエーション収集

対象キャラの全パラメータバリエーションを収集する。

- DuckDB で `MstEnemyStageParameter WHERE mst_enemy_character_id = '{target_id}'` を取得
- unit_kind別・コンテキスト別（VD/通常等）の全バリエーションを一覧化
- HP / 攻撃力 / 移動速度 / 索敵距離 / ノックバック / アビリティ / 変身設定を収集

DuckDBクエリは [references/duckdb-queries.md](references/duckdb-queries.md) の Step 2 クエリを使用。

### Step 3: 攻撃パターン収集

Step 2 で取得したパラメータIDに対応する攻撃情報を収集する。

- `MstAttack WHERE mst_unit_id IN (Step2のパラメータID一覧)` で攻撃情報を取得
- 対応する `MstAttackElement` を JOIN して攻撃詳細（attack_type / range / damage_type / effect_type 等）を一覧化
- VD専用パラメータ（`_vd_` を含む id）には MstAttack レコードが存在しないため、結果が空の場合は「データなし」と記載

DuckDBクエリは [references/duckdb-queries.md](references/duckdb-queries.md) の Step 3 クエリを使用。

### Step 4: インゲーム使用実績収集

Step 2 で取得したパラメータIDが実際にどのステージで使われているかを調査する。

- `MstAutoPlayerSequence WHERE action_type='SummonEnemy' AND action_value IN (Step2のパラメータID一覧)` で使用ステージを抽出
- 対応 MstInGame からコンテンツ種別（VD/メインクエスト/イベント/降臨バトル等）を分類

DuckDBクエリは [references/duckdb-queries.md](references/duckdb-queries.md) の Step 4 クエリを使用。

### Step 5: 出現シーケンスパターン分析

Step 4 で特定したインゲームのシーケンス構造を分析する。

- `MstAutoPlayerSequence WHERE sequence_set_id IN (Step4のインゲームID一覧)` でシーケンス全行を取得
- condition_type 別の出現タイミングパターン・出現位置・出現数の傾向を抽出
- よく使われるパターンを 2〜3 例ピックアップ

DuckDBクエリは [references/duckdb-queries.md](references/duckdb-queries.md) の Step 5 クエリを使用。

### Step 6: コマ効果使用実績分析

Step 4 のインゲームで使われているコマ効果を集計する。

- Step 4 のインゲーム ID から `mst_page_id` を取得
- `MstKomaLine` の koma1〜4 の effect_type を集計（None 除外）
- よく使われるコマ効果をランキング形式で整理

DuckDBクエリは [references/duckdb-queries.md](references/duckdb-queries.md) の Step 6 クエリを使用。

### Step 7: MDドキュメント生成・保存

[references/document-structure.md](references/document-structure.md) のテンプレートに従い Markdown を生成して保存する。

- ファイル名: `{MstEnemyCharacter.id}_{MstEnemyCharacterI18n.name}.md`
- 保存先: `--output-dir` で指定されたディレクトリ（省略時はカレントディレクトリ）
- 数値は3桁区切りカンマ表示（例: `12,000`）

---

## 参照リファレンス

| ファイル | 内容 |
|---------|------|
| [references/document-structure.md](references/document-structure.md) | 出力MDの詳細フォーマットテンプレート |
| [references/duckdb-queries.md](references/duckdb-queries.md) | Step別DuckDBクエリ集（コピペ用） |

---

## DuckDB / スキーマ確認の起動方法

```bash
# カラム名確認（クエリ前に必ず実行）
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_enemy_characters
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_enemy_stage_parameters
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_auto_player_sequences

# DuckDB起動
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc
```

---

## 注意事項

- **CSVパス**: `projects/glow-masterdata/` 直下のファイルが分析対象
- **ENABLE フィルタ**: 必ず `WHERE ENABLE = 'e'` で有効データのみ絞り込む
- **nullstr='\_\_NULL\_\_'**: DuckDB 読み込み時は常に `nullstr='__NULL__'` を指定する
- **VD敵にMstAttackなし**: `_vd_` を含む MstEnemyStageParameter.id に対応する MstAttack レコードは存在しない（設計上）
- **複数キャラヒット時**: 同名キャラが複数存在する場合は一覧を提示してユーザーに選択を求める
- **コンテンツフィルタ**: 絞り込み指示がある場合、全 Step でフィルタを貫徹する。特に Step 2 では、フィルタ対象ステージで使用されているパラメータID（MstAutoPlayerSequence 経由）のみを収集する。フィルタ外のパラメータはステータス一覧にも含めない
- **作品名の記載**: `mst_series_id` から推察した通称（「spyシリーズ」「ゴム系」等）は使わない。必ず DuckDB で `MstSeriesI18n.name (language='ja')` を引いて正式作品名を使う
