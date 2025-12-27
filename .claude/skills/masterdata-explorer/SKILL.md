---
name: masterdata-explorer
description: GLOWマスタデータの165テーブル、DBスキーマ、既存データを調査・理解するためのスキル。テーブル構造調査、データ探索、関連図参照で使用。
---

# GLOWマスタデータ調査・参照スキル

GLOWプロジェクトの165テーブル、471KBのDBスキーマ、既存マスタデータを効率的に調査・理解するためのガイド。

## スキルの目的

このスキルは以下を支援します：

1. **全体像の把握** - 165テーブルの分類・配置を俯瞰
2. **個別の深堀り** - 特定テーブルの構造・制約を詳細調査
3. **調査シナリオ** - よくある調査パターンの手順化
4. **クイックアクセス** - Q&A形式で即座に回答

**対象ユーザー**: マスタデータを調査・理解したい開発者、プランナー

## クイックスタート: よくある質問

### Q1: 「テーブル〇〇の構造を知りたい」

**回答**: DBスキーマで調査

```bash
jq '.databases.mst.tables.mst_events' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

詳細な調査方法は [schema-reference.md](references/schema-reference.md) を参照。

---

### Q2: 「イベント系のテーブルを全て知りたい」

**回答**: テーブルカタログで確認

[table-catalog.md#イベント関連](references/table-catalog.md#イベント関連) を参照。

主要テーブル:
- `MstEvent` - イベント基本情報
- `MstEventI18n` - イベント多言語情報
- `MstEventBonusUnit` - イベントボーナスユニット
- `MstEventDisplayReward` - イベント表示報酬

---

### Q3: 「既存データの例を見たい」

**回答**: 既存CSVファイルを参照

```bash
# テーブルの実データを確認
head -20 projects/glow-masterdata/MstEvent.csv

# 特定IDを検索
grep "^e,event_001" projects/glow-masterdata/MstEvent.csv
```

---

### Q4: 「ガチャの実装を理解したい」

**回答**: 調査パターンを参照

[investigation-patterns.md#ガチャシステムの調査](references/investigation-patterns.md#ガチャシステムの調査) を参照。

関連テーブル:
- `OprGacha`, `OprGachaPrize`, `OprGachaUpper`, `OprGachaUseResource`

---

### Q5: 「特定のIDがどのテーブルにあるか探したい」

**回答**: jqでテーブル横断検索

```bash
# 'event'を含むテーブルを検索
jq '.databases.mst.tables | keys | map(select(test("event"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

詳細は [investigation-patterns.md#ID所在調査](references/investigation-patterns.md#ID所在調査) を参照。

---

## 3つのデータソース

GLOWマスタデータの調査には3つの主要なデータソースがあります。

### 1. DBスキーマ（バリデーション・構造理解）

**場所**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`

**ファイルサイズ**: 471KB

**取得できる情報**:
- テーブル構造（カラム名、型）
- NULL許可/必須フィールド
- enum型の許可値
- デフォルト値
- カラムの説明（コメント）
- 外部キー関係の暗示

**用途**: テーブル構造の正式な仕様を確認

**参照方法**:
```bash
# テーブル全体の構造
jq '.databases.mst.tables.mst_events' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# カラム詳細
jq '.databases.mst.tables.mst_events.columns.start_at' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

詳細なjqコマンドパターンは [schema-reference.md](references/schema-reference.md) を参照。

---

### 2. CSVテンプレート（列順・列名の正式定義）

**場所**: `projects/glow-masterdata/sheet_schema/`

**ファイル数**: 134ファイル

**内容**: ヘッダー3行のみ（データなし）

**形式**:
```
memo
TABLE,MstEvent,MstEvent,...
ENABLE,id,mst_series_id,start_at,end_at,...
```

**用途**: CSV作成時の列順を確認

**注意点**: 列順・列名は絶対に変更禁止（データ投入システムで使用）

**参照方法**:
```bash
head -3 projects/glow-masterdata/sheet_schema/MstEvent.csv
```

---

### 3. 既存マスタデータCSV（実データ例）

**場所**: `projects/glow-masterdata/*.csv`

**ファイル数**: 165ファイル

**内容**: 実際のマスタデータ（ヘッダー + データ行）

**用途**:
- データの作り方・値の参考
- ID命名パターンの確認
- 値の傾向把握

**参照方法**:
```bash
# 全体を確認
cat projects/glow-masterdata/MstEvent.csv

# 最初の20行
head -20 projects/glow-masterdata/MstEvent.csv

# 特定ID検索
grep "^e,event_001" projects/glow-masterdata/MstEvent.csv
```

---

## データソースの使い分け

| 目的 | データソース | コマンド例 |
|------|------------|----------|
| **テーブル構造を知りたい** | DBスキーマ | `jq '.databases.mst.tables.mst_events'` |
| **列順を確認したい** | CSVテンプレート | `head -3 sheet_schema/MstEvent.csv` |
| **実データ例を見たい** | 既存マスタデータCSV | `head -20 MstEvent.csv` |
| **ID命名規則を知りたい** | 既存マスタデータCSV | `grep "^e," MstEvent.csv \| cut -d',' -f2` |
| **enum値を確認したい** | DBスキーマ | `jq '.columns.pack_type.type'` |

---

## 基本的な調査フロー

### パターン1: 新しいテーブルを調べる

**シナリオ**: 「MstAdventBattleって何？どんなカラムがある？」

**手順**:

1. **テーブルカタログで分類を確認**

   [table-catalog.md](references/table-catalog.md) を開き、該当カテゴリを探す。

   → 「降臨バトル関連」カテゴリに分類

2. **DBスキーマで詳細構造を確認**

   ```bash
   jq '.databases.mst.tables.mst_advent_battles' \
     projects/glow-server/api/database/schema/exports/master_tables_schema.json
   ```

   取得できる情報:
   - カラム一覧（id, rank_type, start_at, end_atなど）
   - 各カラムの型（varchar, int, timestampなど）
   - NULL許可の可否
   - enum値（rank_type: Bronze, Silver, Gold, Master）

3. **既存データで実例を確認**

   ```bash
   head -10 projects/glow-masterdata/MstAdventBattle.csv
   ```

   実際のデータから:
   - ID命名パターン（例: `advent_battle_001`）
   - 値の傾向（日付形式、数値範囲など）

---

### パターン2: 特定の機能を実装する

**シナリオ**: 「新しいイベントを追加したい。どのテーブルが必要？」

**手順**:

1. **調査パターンでシナリオを検索**

   [investigation-patterns.md](references/investigation-patterns.md) で「新しいイベントを追加したい」を探す。

2. **関連テーブルをリストアップ**

   [table-catalog.md#イベント関連](references/table-catalog.md#イベント関連) で関連テーブル群を確認:
   - `MstEvent` - イベント基本情報
   - `MstEventI18n` - 多言語情報
   - `MstEventBonusUnit` - ボーナスユニット
   - `MstEventDisplayReward` - 表示報酬
   - `MstEventDisplayUnit` - 表示ユニット

3. **各テーブルの構造を深掘り**

   DBスキーマで各テーブルの必須カラム、外部キー関係を確認。

   ```bash
   jq '.databases.mst.tables.mst_events.columns | to_entries | map(select(.value.nullable == false)) | map(.key)' \
     projects/glow-server/api/database/schema/exports/master_tables_schema.json
   ```

4. **既存イベントデータを参照**

   ```bash
   grep "^e," projects/glow-masterdata/MstEvent.csv | head -5
   ```

---

### パターン3: データの場所を探す

**シナリオ**: 「ガチャのマスタデータはどこ？」

**手順**:

1. **プレフィックスで絞り込み**

   - `Mst*`: 固定マスタ（キャラ、アイテム、ステージなど）
   - `Opr*`: 運営データ（ガチャ、キャンペーン、商品など）

   → ガチャは運営データなので `Opr*` を探す

2. **カテゴリで絞り込み**

   [table-catalog.md#ガチャ関連](references/table-catalog.md#ガチャ関連) で確認。

   主要テーブル:
   - `OprGacha` - ガチャ基本情報
   - `OprGachaPrize` - ガチャ景品
   - `OprGachaUpper` - 天井設定

3. **jqでスキーマ検索**

   ```bash
   jq '.databases.mst.tables | keys | map(select(test("gacha"; "i")))' \
     projects/glow-server/api/database/schema/exports/master_tables_schema.json
   ```

   結果: `["opr_gacha_display_units_i18n", "opr_gacha_use_resources", ...]`

4. **ファイル位置を確認**

   ```bash
   ls projects/glow-masterdata/OprGacha*.csv
   ```

---

### パターン4: データ構造・関連を理解する

**シナリオ**: 「ミッションと報酬の関係は？」

**手順**:

1. **テーブル関連図を参照**

   [table-catalog.md#テーブル関連図](references/table-catalog.md#テーブル関連図) で「ミッションシステム」の図を確認。

   ```
   MstMissionEvent
     ├─ MstMissionEventI18n（名称・説明文）
     ├─ MstMissionReward（報酬）
     └─ MstMissionEventDependency（依存関係）
   ```

2. **外部キーを確認**

   ```bash
   jq '.databases.mst.tables.mst_mission_rewards.columns | to_entries | map(select(.value.comment | test("mst_mission"; "i")))' \
     projects/glow-server/api/database/schema/exports/master_tables_schema.json
   ```

   → `mst_mission_event_id` カラムが外部キー

3. **既存データで関連を確認**

   ```bash
   # ミッション
   grep "^e,mission_event_001" projects/glow-masterdata/MstMissionEvent.csv

   # 対応する報酬
   grep "mission_event_001" projects/glow-masterdata/MstMissionReward.csv
   ```

---

## 主要jqコマンド例

### 全テーブル名取得

```bash
jq '.databases.mst.tables | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**結果**: `["mst_abilities", "mst_abilities_i18n", ..., "opr_gachas"]`

---

### 特定テーブルの全カラム名取得

```bash
jq '.databases.mst.tables.mst_events.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**結果**: `["id", "mst_series_id", "start_at", "end_at", ...]`

---

### enum値の確認

```bash
jq '.databases.mst.tables.mst_packs.columns.pack_type.type' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**結果**: `"enum('Daily','Normal')"`

---

### NULL許可カラムの抽出

```bash
jq '.databases.mst.tables.mst_stages.columns | to_entries | map(select(.value.nullable == true)) | map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**結果**: `["mst_artwork_fragment_drop_group_id", "prev_mst_stage_id", ...]`

---

### デフォルト値を持つカラム

```bash
jq '.databases.mst.tables.mst_stages.columns | to_entries | map(select(.value | has("default"))) | map({key: .key, default: .value.default})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**結果**: `[{"key": "stage_number", "default": "0"}, ...]`

---

### テーブル名部分一致検索

```bash
jq '.databases.mst.tables | keys | map(select(test("mission"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**結果**: `["mst_mission_achievements", "mst_mission_daily", ...]`

---

詳細なjqパターン（20-30個）は [schema-reference.md](references/schema-reference.md) を参照してください。

---

## 主要テーブル分類（概要）

全165テーブルを10カテゴリに分類。詳細は [table-catalog.md](references/table-catalog.md) を参照。

### 1. キャラクター関連（~10テーブル）

- `MstUnit`, `MstUnitI18n`, `MstUnitSkill`, `MstUnitPassive`など

詳細: [table-catalog.md#キャラクター関連](references/table-catalog.md#キャラクター関連)

---

### 2. 攻撃・スキル関連（~5テーブル）

- `MstAttack`, `MstAttackI18n`, `MstAttackElement`など

詳細: [table-catalog.md#攻撃・スキル関連](references/table-catalog.md#攻撃・スキル関連)

---

### 3. クエスト・ステージ（~8テーブル）

- `MstQuest`, `MstStage`, `MstStageReward`, `MstInGame`など

詳細: [table-catalog.md#クエスト・ステージ](references/table-catalog.md#クエスト・ステージ)

---

### 4. ガチャ関連（~6テーブル）

- `OprGacha`, `OprGachaPrize`, `OprGachaUpper`など

詳細: [table-catalog.md#ガチャ関連](references/table-catalog.md#ガチャ関連)

---

### 5. ミッション関連（~15テーブル）

- `MstMissionEvent`, `MstMissionDaily`, `MstMissionReward`など

詳細: [table-catalog.md#ミッション関連](references/table-catalog.md#ミッション関連)

---

### 6. アイテム・報酬（~8テーブル）

- `MstItem`, `MstItemI18n`, `MstFragmentBox`, `MstPack`など

詳細: [table-catalog.md#アイテム・報酬](references/table-catalog.md#アイテム・報酬)

---

### 7. イベント関連（~8テーブル）

- `MstEvent`, `MstEventI18n`, `MstEventBonusUnit`など

詳細: [table-catalog.md#イベント関連](references/table-catalog.md#イベント関連)

---

### 8. 降臨バトル（~6テーブル）

- `MstAdventBattle`, `MstAdventBattleReward`, `MstAdventBattleRank`など

詳細: [table-catalog.md#降臨バトル](references/table-catalog.md#降臨バトル)

---

### 9. 多言語対応（~40テーブル）

- `*I18n` サフィックスを持つテーブル群

詳細: [table-catalog.md#多言語対応](references/table-catalog.md#多言語対応)

---

### 10. その他（~60テーブル）

- PvP、ショップ、交換所、ログインボーナスなど

詳細: [table-catalog.md#その他](references/table-catalog.md#その他)

---

## ドキュメント位置マップ

### プロジェクト内ドキュメント

| ドキュメント | 場所 | 用途 |
|------------|------|------|
| **DBスキーマ** | `projects/glow-server/api/database/schema/exports/master_tables_schema.json` | テーブル構造の正式仕様（471KB） |
| **CSVテンプレート** | `projects/glow-masterdata/sheet_schema/` | 列順・列名の定義（134ファイル） |
| **既存マスタデータ** | `projects/glow-masterdata/*.csv` | 実データ例（165ファイル） |
| **施策マスタデータ** | `マスタデータ/施策/<施策名>/` | 施策別データ |
| **マスタデータ作成ガイド** | `.ai-context/prompts/施策マスタデータ作成ガイド.md` | CSV作成手順 |
| **基本ガイド** | `docs/マスタデータ作成/マスタデータファイル作成ガイド.md` | マスタデータ概要 |
| **配信機構** | `projects/glow-server/docs/01_project/architecture/マスタデータ配信機構.md` | S3配信、暗号化 |

### スキル内ドキュメント

| ドキュメント | 用途 |
|------------|------|
| [schema-reference.md](references/schema-reference.md) | DBスキーマ調査方法詳細（jqコマンド20-30パターン） |
| [table-catalog.md](references/table-catalog.md) | 全165テーブルのカタログ（10カテゴリ分類） |
| [investigation-patterns.md](references/investigation-patterns.md) | よくある調査シナリオ（10-15パターン） |

---

## 実践例

### 例1: イベントシステムの調査

**目的**: 新しいイベントを追加するために、イベント関連テーブルを理解する。

**手順**:

1. [table-catalog.md#イベント関連](references/table-catalog.md#イベント関連) でイベント関連テーブルを確認
2. DBスキーマで各テーブルの構造を調査:
   - `MstEvent` - イベント基本情報
   - `MstEventI18n` - 多言語情報
   - `MstEventBonusUnit` - ボーナスユニット
3. 既存データで実際のイベント設定を確認:
   ```bash
   head -10 projects/glow-masterdata/MstEvent.csv
   ```
4. 外部キー関係を追跡:
   ```bash
   jq '.databases.mst.tables.mst_events.columns.mst_series_id' \
     projects/glow-server/api/database/schema/exports/master_tables_schema.json
   ```

詳細: [investigation-patterns.md#イベントシステムの調査](references/investigation-patterns.md#イベントシステムの調査)

---

### 例2: 特定IDの所在調査

**目的**: `pack_daily_001` がどのテーブルにあるか探す。

**手順**:

1. プレフィックスから推測: `pack_` → `MstPack` の可能性
2. 既存CSVで検索:
   ```bash
   grep "pack_daily_001" projects/glow-masterdata/MstPack.csv
   ```
3. 見つからない場合、jqで全テーブルを横断検索:
   ```bash
   jq '.databases.mst.tables | keys | map(select(test("pack"; "i")))' \
     projects/glow-server/api/database/schema/exports/master_tables_schema.json
   ```
4. 候補テーブルを順次検索:
   ```bash
   grep "pack_daily_001" projects/glow-masterdata/MstPackContent.csv
   ```

詳細: [investigation-patterns.md#ID所在調査](references/investigation-patterns.md#ID所在調査)

---

## トラブルシューティング

### jqコマンドがエラーになる

**症状**: `jq '.databases.mst.tables.MstEvent'` がエラー

**原因**: テーブル名の大文字小文字、スネークケース変換

**解決方法**:
- `MstEvent` → `mst_events`（複数形、スネークケース）
- `OprGacha` → `opr_gachas`（複数形、スネークケース）

**正しいコマンド**:
```bash
jq '.databases.mst.tables.mst_events' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

---

### テーブルが見つからない

**症状**: 「〇〇テーブルが見つからない」

**解決方法**:

1. [table-catalog.md](references/table-catalog.md) で正確な名前を確認
2. jqで全テーブル名を取得して検索:
   ```bash
   jq '.databases.mst.tables | keys | map(select(test("event"; "i")))' \
     projects/glow-server/api/database/schema/exports/master_tables_schema.json
   ```

---

### enum値が分からない

**症状**: 「pack_typeに何を設定すればいい？」

**解決方法**:

DBスキーマでenum値を確認:
```bash
jq '.databases.mst.tables.mst_packs.columns.pack_type.type' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**結果**: `"enum('Daily','Normal')"`

---

## 制約事項

このスキルは以下のポリシーに従います:

- **glow-brainは参照専用** - `projects/`配下のファイルは変更禁止
- **調査・理解が目的** - データ作成・編集は行わない
- **読み取り専用コマンド** - jq, cat, head, grep のみ使用

---

## 次のステップ

1. **詳細調査**: [schema-reference.md](references/schema-reference.md) でjqコマンドを習得
2. **全体把握**: [table-catalog.md](references/table-catalog.md) で165テーブルを俯瞰
3. **シナリオ学習**: [investigation-patterns.md](references/investigation-patterns.md) でよくある調査パターンを学ぶ
