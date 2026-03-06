# イベントミッション マスタデータ設定手順書

## 概要

イベントミッション（達成条件・報酬）とデイリーログインボーナス、期間限定ミッションを設定する手順書。

- **report.md 対応セクション**: `機能別データ詳細 > ミッション`

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | MstMissionReward | 報酬グループ定義 | 必須 |
| 2 | MstMissionEvent | イベントミッション本体 | 必須 |
| 3 | MstMissionEventI18n | ミッション多言語説明 | 必須 |
| 4 | MstMissionEventDependency | ミッション解放順序 | 条件付き必須 |
| 5 | MstMissionEventDailyBonusSchedule | デイリーボーナス有効期間 | 条件付き必須 |
| 6 | MstMissionEventDailyBonus | デイリーボーナス各日報酬 | 条件付き必須 |
| 7 | MstMissionLimitedTerm | 期間限定ミッション | 任意 |
| 8 | MstMissionLimitedTermI18n | 期間限定ミッション多言語説明 | 任意 |

---

## 前提条件・依存関係

- **MstEvent の登録完了が前提**（`01_event.md` を先に実施）
- **MstUnit の登録完了が前提**（ミッション条件に特定ユニット指定がある場合）
- MstMissionReward は MstMissionEvent より先に登録
- MstMissionEventDependency は MstMissionEvent 登録後に設定

---

## report.md から読み取る情報チェックリスト

- [ ] ミッション一覧（達成条件・報酬種別）
- [ ] ミッション数（通常 30〜50 件）
- [ ] デイリーボーナス有無と日数・報酬一覧
- [ ] 期間限定ミッション（降臨バトル挑戦系）の有無
- [ ] ミッションのアンロック順序（前のミッション達成後に解放）

---

## テーブル別設定手順

### MstMissionReward（報酬グループ定義）

MstMissionEvent・MstMissionEventDailyBonus・MstMissionLimitedTerm から参照される報酬を事前に登録する。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `mission_reward_{連番}` | `mission_reward_587` |
| release_key | 今回のリリースキー | `202602015` |
| group_id | 報酬グループ ID（MstMissionEvent.mst_mission_reward_group_id と一致） | `event_you_00001_event_reward_01` |
| resource_type | リソース種別（FreeDiamond/Coin/Item/Unit） | `Item` |
| resource_id | リソース ID（Coin/Diamond は NULL） | `ticket_glo_00003` |
| resource_amount | 報酬量 | `2` |
| sort_order | 表示順（グループ内で複数報酬がある場合） | `1` |
| 備考 | 備考メモ（任意） | `幼稚園WARS いいジャン祭 特別ログインボーナス` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, release_key, group_id, resource_type, resource_id, resource_amount, sort_order
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstMissionReward.csv')
ORDER BY release_key DESC, group_id, sort_order;
```

---

### MstMissionEvent（イベントミッション本体）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{event_id}_{連番}` | `event_you_00001_1` |
| release_key | 今回のリリースキー | `202602015` |
| mst_event_id | 対応するイベント ID | `event_you_00001` |
| criterion_type | 達成条件種別（下表参照） | `SpecificUnitGradeUpCount` |
| criterion_value | 条件対象値（ユニット ID 等、不要な場合 NULL） | `chara_you_00201` |
| criterion_count | 達成必要数 | `2` |
| unlock_criterion_type | 解放条件種別（`__NULL__` = 最初から解放） | `__NULL__` |
| unlock_criterion_value | 解放条件対象値 | `NULL` |
| unlock_criterion_count | 解放条件必要数 | `0` |
| group_key | グループキー（関連ミッションのグループ） | `NULL` |
| mst_mission_reward_group_id | 報酬グループ ID | `you_00001_event_reward_01` |
| sort_order | 表示順 | `1` |
| destination_scene | 達成後の遷移先（UnitList/Quest/...） | `UnitList` |

**主要な criterion_type 一覧**

| criterion_type | 説明 | criterion_value |
|---------------|------|----------------|
| SpecificUnitGradeUpCount | 特定ユニットのグレードアップ回数 | mst_unit_id |
| QuestClearCount | クエストクリア回数 | mst_quest_id |
| AdventBattleChallengeCount | 降臨バトル挑戦回数 | NULL |
| GachaPullCount | ガチャを引いた回数 | opr_gacha_id |
| UnitGradeUpCount | ユニットグレードアップ合計回数 | NULL |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_event_id, criterion_type, criterion_value, criterion_count,
       mst_mission_reward_group_id, sort_order
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstMissionEvent.csv')
WHERE mst_event_id IS NOT NULL
ORDER BY sort_order;
```

---

### MstMissionEventI18n（ミッション多言語説明）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{mst_mission_event_id}_{language}` | `event_you_00001_1_ja` |
| mst_mission_event_id | 対応する MstMissionEvent.id | `event_you_00001_1` |
| language | 言語コード | `ja` |
| description | ミッション説明文 | `ダグ をグレード2まで強化しよう` |

---

### MstMissionEventDependency（ミッション解放順序）

前のミッションをクリアすると次のミッションが解放される順序を定義する。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `234` |
| release_key | 今回のリリースキー | `202602015` |
| group_id | グループ ID（同じグループは順序制御） | `event_you_00001_1` |
| mst_mission_event_id | 対象ミッション ID | `event_you_00001_1` |
| unlock_order | アンロック順序（1 から連番） | `1` |
| 備考 | 備考 | `NULL` |

**過去データ参照クエリ**

```duckdb
SELECT id, group_id, mst_mission_event_id, unlock_order
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstMissionEventDependency.csv')
ORDER BY group_id, unlock_order;
```

---

### MstMissionEventDailyBonusSchedule（デイリーボーナス有効期間）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{event_id}_daily_bonus` | `event_you_00001_daily_bonus` |
| release_key | 今回のリリースキー | `202602015` |
| mst_event_id | 対応するイベント ID | `event_you_00001` |
| start_at | デイリーボーナス開始日時（UTC） | `2026-02-02 15:00:00` |
| end_at | デイリーボーナス終了日時（UTC） | `2026-02-16 03:59:59` |

---

### MstMissionEventDailyBonus（デイリーボーナス各日報酬）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{event_id}_daily_bonus_{連番2桁}` | `event_you_00001_daily_bonus_01` |
| release_key | 今回のリリースキー | `202602015` |
| mst_mission_event_daily_bonus_schedule_id | 対応するスケジュール ID | `event_you_00001_daily_bonus` |
| login_day_count | 何日目のボーナスか（1 から連番） | `1` |
| mst_mission_reward_group_id | 報酬グループ ID | `event_you_00001_daily_bonus_01` |
| sort_order | 表示順（通常 `1`） | `1` |
| 備考 | 備考 | `ピックアップガシャチケット` |

**過去データ参照クエリ**

```duckdb
SELECT id, login_day_count, mst_mission_reward_group_id, 備考
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstMissionEventDailyBonus.csv')
ORDER BY login_day_count;
```

---

### MstMissionLimitedTerm（期間限定ミッション）

降臨バトルや特定イベント期間中のみ有効なミッション。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `limited_term_{連番}` | `limited_term_37` |
| release_key | 今回のリリースキー | `202602015` |
| progress_group_key | 進捗グループキー（同グループのミッションをまとめる） | `group10` |
| criterion_type | 達成条件種別 | `AdventBattleChallengeCount` |
| criterion_value | 条件値 | `NULL` |
| criterion_count | 達成必要数 | `5` |
| mission_category | ミッションカテゴリ（AdventBattle/...） | `AdventBattle` |
| mst_mission_reward_group_id | 報酬グループ ID | `you_00001_limited_term_1` |
| sort_order | 表示順 | `1` |
| destination_scene | 遷移先シーン | `AdventBattle` |
| start_at | 有効開始日時（UTC） | `2026-02-09 15:00:00` |
| end_at | 有効終了日時（UTC） | `2026-02-15 14:59:59` |

**過去データ参照クエリ**

```duckdb
SELECT id, progress_group_key, criterion_type, criterion_count,
       mission_category, start_at, end_at, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstMissionLimitedTerm.csv')
ORDER BY sort_order;
```

---

## 検証方法

- MstMissionEvent.mst_mission_reward_group_id → MstMissionReward.group_id が存在するか
- MstMissionEventDependency.mst_mission_event_id → MstMissionEvent.id が存在するか
- MstMissionEventDailyBonus.login_day_count が 1 から連番になっているか
- MstMissionEventDailyBonus の件数がデイリーボーナス日数と一致するか

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`, `masterdata-csv-validator`
- 過去リリース: `domain/raw-data/masterdata/released/202602015/tables/`
