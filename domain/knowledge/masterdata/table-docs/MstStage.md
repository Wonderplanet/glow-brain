# MstStage 詳細説明

> CSVパス: `projects/glow-masterdata/MstStage.csv`
> i18n CSVパス: `projects/glow-masterdata/MstStageI18n.csv`

---

## 概要

`MstStage` は**クエスト内の個別ステージ（バトルステージ）の基本設定テーブル**。`mst_stages_i18n` はステージ名の多言語設定テーブル。

1つのクエストに複数のステージが存在し、ステージを順番にクリアしていくことでクエストをクリアできる。ステージごとに消費スタミナ・獲得EXP・獲得コイン・推奨レベル・解放条件などを細かく制御できる。

### ゲームへの影響

- **ステージ進行**: `prev_mst_stage_id` で前のステージとの連鎖を定義する。前ステージをクリアしないと次のステージが解放されない。
- **消費スタミナ** (`cost_stamina`): プレイ1回あたりの消費スタミナ量。
- **報酬** (`exp`, `coin`): クリア時に獲得する経験値とコインの基本量。
- **自動周回** (`auto_lap_type`, `max_auto_lap_count`): スタミナブーストによる連続自動プレイの設定。
- **アートワークフラグメントドロップ** (`mst_artwork_fragment_drop_group_id`): クリア時に一定確率でドロップするアートワーク断片のグループ指定。
- **公開期間** (`start_at`, `end_at`): ステージの表示・プレイ可能期間を制御する。

### テーブル連携図

```
MstQuest（クエスト）
  └─ id → MstStage.mst_quest_id（1:N、複数ステージ）
              ├─ id → MstStageI18n.mst_stage_id（多言語名称）
              ├─ id → MstStageReward.mst_stage_id（報酬設定）
              ├─ id → MstStageEndCondition.mst_stage_id（終了条件）
              ├─ prev_mst_stage_id → MstStage.id（解放条件）
              └─ mst_in_game_id → MstInGame.id（インゲーム設定）
```

---

## 全カラム一覧

### mst_stages（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_quest_id` | varchar(255) | 不可 | - | 所属するクエストID（`mst_quests.id`） |
| `mst_in_game_id` | varchar(255) | 不可 | `""` | インゲーム設定ID（`mst_in_game.id`） |
| `stage_number` | int | 不可 | 0 | クエスト内のステージ番号（1始まり） |
| `recommended_level` | int | 不可 | 1 | 推奨ユーザーレベル |
| `cost_stamina` | int unsigned | 不可 | - | 消費スタミナ量 |
| `exp` | int unsigned | 不可 | - | クリア時の獲得EXP |
| `coin` | int unsigned | 不可 | - | クリア時の獲得コイン |
| `mst_artwork_fragment_drop_group_id` | varchar(255) | 可（NULL） | - | ドロップするアートワーク断片グループID |
| `prev_mst_stage_id` | varchar(255) | 可（NULL） | - | 解放条件となる前ステージID（NULL = 最初から解放） |
| `mst_stage_tips_group_id` | varchar(255) | 可（NULL） | - | ステージTipsグループID |
| `auto_lap_type` | enum | 可（NULL） | - | 自動周回タイプ。`AfterClear` / `Initial` |
| `max_auto_lap_count` | int unsigned | 不可 | 1 | 最大自動周回指定可能数 |
| `sort_order` | int unsigned | 不可 | - | ステージ一覧の表示順 |
| `start_at` | timestamp | 不可 | - | ステージ公開開始日時 |
| `end_at` | timestamp | 不可 | - | ステージ公開終了日時 |
| `asset_key` | varchar(255) | 可（NULL） | - | ステージのアセットキー |
| `release_key` | bigint unsigned | 不可 | 1 | リリースキー |

### MstStageI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_stage_id` | varchar(255) | 不可 | - | 対応するステージID（`mst_stages.id`） |
| `language` | enum | 不可 | `ja` | 言語設定。`ja` / `en` / `zh-Hant` |
| `name` | varchar(255) | 不可 | - | ステージ名（UI表示用） |
| `release_key` | int | 不可 | 1 | リリースキー |

---

## AutoLapType（enum）

| 値 | 説明 |
|----|------|
| `AfterClear` | ステージクリア後に自動周回を開始（クリア済みのステージのみ） |
| `Initial` | 最初から自動周回が設定可能 |
| NULL | 自動周回不可 |

---

## 命名規則 / IDの生成ルール

`id` は以下のパターンで命名する:

```
{難易度}_{作品略称}_{連番5桁}
```

例:
- `normal_spy_00001` → SPY×FAMILYのノーマル難易度ステージ1番
- `hard_spy_00001` → SPY×FAMILYのハード難易度ステージ1番

`MstStageI18n.id` は `{mst_stage_id}_{language}` で命名する（例: `normal_spy_00001_ja`）。

---

## 他テーブルとの連携

| 連携先テーブル | 結合キー | 用途 |
|-------------|--------|------|
| `MstQuest` | `MstStage.mst_quest_id = MstQuest.id` | クエスト情報を取得 |
| `MstStageI18n` | `MstStageI18n.mst_stage_id = MstStage.id` | ステージ名の多言語テキストを取得 |
| `MstInGame` | `MstStage.mst_in_game_id = MstInGame.id` | バトルのインゲーム設定（敵配置・ギミック等）を取得 |
| `MstStageReward` | `MstStageReward.mst_stage_id = MstStage.id` | ドロップ報酬設定を取得 |
| `MstStageEndCondition` | `MstStageEndCondition.mst_stage_id = MstStage.id` | ステージクリア条件を取得 |
| `MstArtworkFragment` | `MstStage.mst_artwork_fragment_drop_group_id = MstArtworkFragment.drop_group_id` | ドロップ候補のアートワーク断片を取得 |

---

## 実データ例

### パターン1: SPY×FAMILYノーマルクエストのステージ連鎖

```csv
ENABLE,id,mst_quest_id,mst_in_game_id,stage_number,recommended_level,cost_stamina,exp,coin,prev_mst_stage_id,mst_stage_tips_group_id,auto_lap_type,max_auto_lap_count,sort_order,asset_key,release_key,mst_artwork_fragment_drop_group_id,start_at,end_at
e,normal_spy_00001,quest_main_spy_normal_1,normal_spy_00001,1,1,5,50,50,,1,AfterClear,5,1,spy_normal_01,202509010,spy_a_0001,2025-05-01 12:00:00,2030-01-01 00:00:00
e,normal_spy_00002,quest_main_spy_normal_1,normal_spy_00002,2,1,5,50,50,normal_spy_00001,1,AfterClear,5,2,spy_normal_02,202509010,spy_a_0002,2025-05-01 12:00:00,2030-01-01 00:00:00
```

- `prev_mst_stage_id` でステージ1→ステージ2の解放連鎖を定義
- `auto_lap_type = AfterClear` + `max_auto_lap_count = 5` で最大5周の自動周回が可能

### パターン2: i18nレコード（ステージ名）

```csv
ENABLE,release_key,id,mst_stage_id,language,name,category_name
e,202509010,normal_spy_00001_ja,normal_spy_00001,ja,SPY×FAMILY,
e,202509010,normal_spy_00002_ja,normal_spy_00002,ja,SPY×FAMILY,
```

- 同一クエスト内の全ステージが同じ `name`（作品名）を使うケースが多い

---

## 設定時のポイント

1. **`prev_mst_stage_id` でステージの解放順序を定義する**。ステージ1は NULL（最初から解放）、ステージ2以降は前のステージIDを設定することで順番にアンロックされる。
2. **`mst_in_game_id` は `MstInGame` に存在するIDを必ず参照する**。インゲーム設定（敵配置・ギミック等）が先に定義されていることを確認する。
3. **`auto_lap_type = AfterClear` は初回クリア後に自動周回が解放される設定**。チュートリアルなどでは NULL を設定して自動周回を禁止する。
4. **`cost_stamina` はクエスト種別・難易度に応じて設定する**。ノーマルは低く、ハード・エクストラは高めに設定するバランスが一般的。
5. **`mst_artwork_fragment_drop_group_id` は NULL でも動作する**（ドロップなし）。アートワークのドロップが設計された場合のみ設定する。
6. **i18nレコードはステージ本体とセットで作成する**。`MstStage` 追加のタイミングで `MstStageI18n` も対応言語分作成する。
7. **`MstStageI18n` は `ja` / `en` / `zh-Hant` の3言語に対応している**。他のi18nテーブルが `ja` のみの場合でも `MstStageI18n` は多言語対応しているため、必要な言語分のレコードを用意する。
8. **`sort_order` はステージ番号と一致させるのが慣例**。ステージ1は `sort_order = 1`、ステージ2は `sort_order = 2` で設定する。
