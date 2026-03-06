# イベントクエスト・ステージ設定 マスタデータ設定手順書

## 概要

イベントクエストとその各ステージ（バトルステージ）を設定する手順書。クエスト種別・難易度設定からステージ報酬・クリア条件まで一括してカバーする。

- **report.md 対応セクション**: `## 機能別データ詳細 > クエスト・ステージ`

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | MstQuest | クエスト定義 | 必須 |
| 2 | MstQuestI18n | クエスト多言語名 | 必須 |
| 3 | MstQuestBonusUnit | クエスト別ボーナスユニット | 条件付き必須 |
| 4 | MstQuestEventBonusSchedule | イベントボーナス有効スケジュール | 条件付き必須 |
| 5 | MstStage | ステージ定義 | 必須 |
| 6 | MstStageI18n | ステージ多言語名 | 必須 |
| 7 | MstStageEventReward | ステージドロップ報酬 | 必須 |
| 8 | MstStageClearTimeReward | タイムアタック報酬 | 任意 |
| 9 | MstStageEventSetting | ステージイベント設定（リセット・制限） | 必須 |
| 10 | MstStageEndCondition | ステージ終了条件（降臨系のみ） | 条件付き必須 |

---

## 前提条件・依存関係

- **MstEvent の登録完了が前提**（`01_event.md` を先に実施）
- **MstUnit の登録完了が前提**（`02_unit.md` を先に実施）
- **インゲームデータ（MstInGame等）の登録完了が前提**（`09_ingame-battle.md` または `masterdata-ingame-creator` スキルを先に実施）
- MstStage.mst_in_game_id は登録済みの MstInGame.id を参照
- MstQuestEventBonusSchedule.event_bonus_group_id は MstEventBonusUnit と同一グループを参照

---

## report.md から読み取る情報チェックリスト

- [ ] クエスト一覧（ID・名前・カテゴリ・ステージ数）
- [ ] 各クエストの開催期間
- [ ] 推奨レベル（ステージ別）
- [ ] クエスト種別（quest_type: event/permanent/...）
- [ ] ステージ報酬（初回クリア報酬・ランダム報酬）
- [ ] タイムアタック報酬の有無とタイム設定
- [ ] デイリーリセット有無
- [ ] アートワークフラグメントドロップ設定

---

## テーブル別設定手順

### MstQuest（クエスト定義）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `quest_{type}_{series_id}{回}_{種別}` | `quest_event_you1_charaget01` |
| quest_type | クエスト種別（event/permanent/raid/...） | `event` |
| mst_event_id | 対応するイベント ID | `event_you_00001` |
| sort_order | クエスト表示順 | `1` |
| asset_key | クエスト画像キー | `you1_charaget01` |
| start_date | クエスト開始日時（UTC） | `2026-02-02 15:00:00` |
| end_date | クエスト終了日時（UTC） | `2026-03-02 10:59:59` |
| quest_group | クエストグループ ID | `event_you1_charaget_dagu` |
| difficulty | 難易度（Normal/Hard/VeryHard/...） | `Normal` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, quest_type, mst_event_id, sort_order, start_date, end_date, quest_group, difficulty
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstQuest.csv')
WHERE mst_event_id IS NOT NULL
ORDER BY sort_order;
```

---

### MstQuestI18n（クエスト多言語名）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{mst_quest_id}_{language}` | `quest_event_you1_charaget01_ja` |
| mst_quest_id | 対応する MstQuest.id | `quest_event_you1_charaget01` |
| language | 言語コード | `ja` |
| name | クエスト表示名 | `先輩は敬いたまえ` |
| category_name | カテゴリ表示名（ストーリー/チャレンジ/高難易度/デイリー等） | `ストーリー` |
| flavor_text | フレーバーテキスト（任意） | `NULL` |

---

### MstQuestBonusUnit（クエスト別ボーナスユニット）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `66` |
| mst_quest_id | ボーナス対象クエスト ID | `quest_enhance_00001` |
| mst_unit_id | ボーナス対象ユニット ID | `chara_you_00001` |
| coin_bonus_rate | コインボーナス率（0.2 = 20%） | `0.2` |
| start_at | 有効開始日時（UTC） | `2026-02-02 15:00:00` |
| end_at | 有効終了日時（UTC） | `2026-03-02 10:59:59` |
| release_key | 今回のリリースキー | `202602015` |

---

### MstQuestEventBonusSchedule（イベントボーナス有効スケジュール）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `10` |
| mst_quest_id | 対象クエスト ID | `quest_raid_you1_00001` |
| event_bonus_group_id | MstEventBonusUnit の event_bonus_group_id | `raid_you1_00001` |
| start_at | 有効開始日時（UTC） | `2026-02-09 15:00:00` |
| end_at | 有効終了日時（UTC） | `2026-02-15 14:59:59` |
| release_key | 今回のリリースキー | `202602015` |

---

### MstStage（ステージ定義）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{series}_{quest種別}_{連番}` | `event_you1_charaget01_00001` |
| mst_quest_id | 対応するクエスト ID | `quest_event_you1_charaget01` |
| mst_in_game_id | インゲームデータ ID（`09_ingame-battle.md` 参照） | `event_you1_charaget01_00001` |
| stage_number | ステージ番号（1 始まり） | `1` |
| recommended_level | 推奨レベル | `10` |
| cost_stamina | スタミナ消費 | `5` |
| exp | 獲得 EXP | `50` |
| coin | 獲得コイン | `100` |
| prev_mst_stage_id | 前のステージ ID（最初のステージは NULL） | `NULL` |
| mst_stage_tips_group_id | ヒントグループ（通常 `1`） | `1` |
| auto_lap_type | オートラップ種別（NULL/AfterClear） | `AfterClear` |
| max_auto_lap_count | 最大オートラップ回数 | `5` |
| sort_order | 表示順 | `1` |
| asset_key | アセットキー | `event_you1_00001` |
| mst_artwork_fragment_drop_group_id | アートワークフラグメントドロップグループ（なければ `__NULL__`） | `event_you_a_0001` |
| start_at | 開始日時（UTC） | `2026-02-02 15:00:00` |
| end_at | 終了日時（UTC） | `2026-03-02 10:59:59` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_quest_id, mst_in_game_id, stage_number, recommended_level,
       cost_stamina, auto_lap_type, max_auto_lap_count, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstStage.csv')
ORDER BY mst_quest_id, stage_number;
```

---

### MstStageEventReward（ステージドロップ報酬）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `609` |
| mst_stage_id | 対応するステージ ID | `event_you1_1day_00001` |
| reward_category | 報酬種別（FirstClear/Random/...） | `FirstClear` |
| resource_type | リソース種別（FreeDiamond/Coin/Item/Unit）| `FreeDiamond` |
| resource_id | リソース ID（Coin/Diamond は NULL） | `prism_glo_00001` |
| resource_amount | 報酬量 | `20` |
| percentage | ドロップ率（%）FirstClear=100 | `100` |
| sort_order | 表示順 | `1` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_stage_id, reward_category, resource_type, resource_id, resource_amount, percentage
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstStageEventReward.csv')
ORDER BY mst_stage_id, sort_order;
```

---

### MstStageClearTimeReward（タイムアタック報酬）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{mst_stage_id}_{連番}` | `event_you1_challenge_00001_1` |
| mst_stage_id | 対象ステージ ID | `event_you1_challenge_00001` |
| upper_clear_time_ms | タイム上限（ミリ秒） | `140000` |
| resource_type | 報酬種別 | `FreeDiamond` |
| resource_id | リソース ID | `NULL` |
| resource_amount | 報酬量 | `20` |
| release_key | 今回のリリースキー | `202602015` |

---

### MstStageEventSetting（ステージイベント設定）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `183` |
| mst_stage_id | 対象ステージ ID | `event_you1_1day_00001` |
| reset_type | リセット種別（Daily/Weekly/NULL=なし） | `Daily` |
| clearable_count | クリア可能回数（NULL=無制限） | `NULL` |
| ad_challenge_count | 広告追加挑戦回数 | `0` |
| mst_stage_rule_group_id | ルールグループ（なければ `__NULL__`） | `__NULL__` |
| start_at | 有効開始日時（UTC） | `2026-02-02 15:00:00` |
| end_at | 有効終了日時（UTC） | `2026-02-16 03:59:59` |
| background_asset_key | 背景アセットキー | `you_00001` |
| release_key | 今回のリリースキー | `202602015` |

---

### MstStageEndCondition（ステージ終了条件）

**カラム設定ガイド**（降臨バトルステージのみ設定）

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | mst_stage_id と同じ | `quest_raid_you1_00001` |
| mst_stage_id | 対象ステージ ID | `quest_raid_you1_00001` |
| stage_end_type | 終了種別（Defeat/...） | `Defeat` |
| condition_type | 条件種別（TimeOver/...） | `TimeOver` |
| condition_value1 | 条件値（秒数等） | `120` |
| condition_value2 | 条件値2（使用頻度低） | `NULL` |
| release_key | 今回のリリースキー | `202602015` |

---

## 検証方法

- MstStage.mst_quest_id → MstQuest.id が存在するか
- MstStage.mst_in_game_id → MstInGame.id が存在するか
- MstStageEventReward.mst_stage_id → MstStage.id が存在するか
- MstQuestEventBonusSchedule.event_bonus_group_id → MstEventBonusUnit.event_bonus_group_id が存在するか
- ステージ番号が連番になっているか（スキップなし）

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`, `masterdata-csv-validator`, `masterdata-ingame-creator`
- 過去リリース: `domain/raw-data/masterdata/released/202602015/tables/`
- インゲーム設定: `09_ingame-battle.md`
