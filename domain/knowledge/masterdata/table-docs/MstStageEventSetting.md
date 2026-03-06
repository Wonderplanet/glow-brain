# MstStageEventSetting 詳細説明

> CSVパス: `projects/glow-masterdata/MstStageEventSetting.csv`

---

## 概要

イベントクエストステージの追加設定を管理するテーブル。`mst_stages` にある基本設定に加えて、イベントクエスト専用の設定（開催期間・クリア可能回数・広告回数・背景アセット・イベントルールグループ）を管理する。

- `mst_stage_id` に対してユニーク制約があり、1ステージにつき1レコードのみ設定できる
- `reset_type` が `Daily` の場合、`clearable_count` に設定した回数まで1日にクリアできる（デイリーリセット）
- `reset_type` が NULL の場合、`clearable_count` で設定した回数まで期間内に総計クリアできる

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | 連番ID（整数） |
| mst_stage_id | varchar(255) | 不可 | - | 対象ステージID（`mst_stages.id`）。ユニーク制約あり |
| reset_type | enum | 可 | - | クリア回数のリセットタイプ（NULLは期間内合計） |
| clearable_count | int | 可 | - | クリア可能回数（NULLは無制限） |
| ad_challenge_count | int | 不可 | `0` | 広告視聴で追加挑戦できる回数 |
| background_asset_key | varchar(255) | 可 | - | 背景アセットキー（Addressablesキー） |
| mst_stage_rule_group_id | varchar(255) | 可 | - | イベントルールグループID（`mst_stage_event_rules.group_id`） |
| start_at | timestamp | 不可 | - | イベント開始日時（UTC） |
| end_at | timestamp | 不可 | - | イベント終了日時（UTC） |
| release_key | bigint | 不可 | - | リリースキー |

**ユニークインデックス**: `uk_mst_stage_id`（`mst_stage_id`）

---

## ResetType（リセットタイプ）

| 値 | 説明 |
|---|---|
| `Daily` | 1日ごとにクリア回数がリセットされる |
| NULL | 期間内の合計クリア回数で管理（リセットなし） |

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_stages` | `mst_stage_id` → `mst_stages.id` | 基本ステージ設定 |
| `mst_stage_event_rewards` | `mst_stage_id` | このステージのイベント報酬設定 |
| `mst_stage_event_rules`（グループ） | `mst_stage_rule_group_id` → `mst_stage_event_rules.group_id` | 特殊ルール設定 |

---

## 実データ例

### 例1: デイリークリア制限ありのイベントステージ

```
id | mst_stage_id           | reset_type | clearable_count | ad_challenge_count | background_asset_key | mst_stage_rule_group_id | start_at            | end_at              | release_key
1  | event_kai1_1day_00001  | Daily      | 1               | 0                  | kai_00001            | NULL                    | 2025-09-22 11:00:00 | 2025-10-06 03:59:59 | 202509010
```

1日1回クリア可能なデイリーステージ。2025/9/22 11:00 〜 2025/10/6 04:00 の期間で開催。

### 例2: 回数制限なし・期間内クリアし放題のイベントステージ

```
id | mst_stage_id                | reset_type | clearable_count | ad_challenge_count | background_asset_key | mst_stage_rule_group_id | start_at            | end_at              | release_key
2  | event_kai1_charaget01_00001 | NULL       | NULL            | 0                  | kai_00001            | NULL                    | 2025-09-22 11:00:00 | 2025-10-22 11:59:59 | 202509010
```

期間内（〜2025/10/22）は何度でもクリア可能なキャラ獲得ステージ。

---

## 設定時のポイント

1. **1ステージに1レコードのみ**: `mst_stage_id` にユニーク制約があるため、同一ステージに複数レコードを設定することはできない
2. **デイリーリセットの設定**: 1日1回の制限ステージは `reset_type = Daily`、`clearable_count = 1` と設定する
3. **回数制限なしの場合は NULL**: `clearable_count` を NULL にすると何度でもクリアできる
4. **日時は UTC で設定**: `start_at` / `end_at` は UTC で設定する。日本時間 (JST) は UTC+9 のためズレに注意
5. **background_asset_key でイベント背景を設定**: イベントの世界観に合った背景アセットキーを指定する。対応するアセットがAddressablesに登録されている必要がある
6. **クライアントクラス**: `MstStageEventSettingData`（`GLOW.Core.Data.Data`名前空間）。`mstStageRuleGroupId` フィールドはクライアントに含まれない。`resetType`（nullable）・`clearableCount`（nullable）・`adChallengeCount`・`backgroundAssetKey`・`startAt`・`endAt` が配信される
7. **mst_stage_event_rewards との対応**: イベントステージ設定を追加した場合、対応する報酬設定も `mst_stage_event_rewards` に必ず追加する
