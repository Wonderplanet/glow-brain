# イベントログインボーナス 要件テキストフォーマット

> **用途**: プランナーがヒアリング結果を記入し、Claudeに渡すことでマスタデータCSVを一括生成するための要件テキスト。
>
> **生成されるCSV**:
> - `MstMissionEventDailyBonusSchedule.csv`（スケジュール定義・1行）
> - `MstMissionEventDailyBonus.csv`（日別ボーナスレコード・N日分）
> - `MstMissionReward.csv`（報酬レコード・N行、既存CSVへの追記）

---

## テンプレート

```
# イベントログインボーナス 要件テキスト

## 基本情報

- イベントID: {mst_events.id に設定したIDをそのまま記入}
  例: event_mag_00001
- リリースキー: {このリリースのリリースキーを記入}
  例: 202511010
- ボーナス表示名（備考欄・任意）: {MstMissionReward の備考欄に入れる説明文}
  例: ○○コラボ いいジャン祭 特別ログインボーナス

## 期間

- 開始: YYYY-MM-DD HH:MM
- 終了: YYYY-MM-DD HH:MM

  ※ 開始時刻の慣例: イベント開始日の 24:00（= 翌日 0:00）
  ※ 終了時刻の慣例: イベント終了日の 12:59

## 日別報酬

{N}日目: {報酬アイテム名} × {数量}

（1日1報酬が基本。複数報酬を付与する日は複数行書く）

---
【記入欄】
1日目:
2日目:
3日目:
4日目:
5日目:
6日目:
7日目:
8日目:
9日目:
10日目:
11日目:
12日目:
13日目:
14日目:
（日数に応じて増減してください）
```

---

## 報酬アイテム名の選択肢と対応ID

Claudeがこのテキストを解釈してIDに変換します。以下の表記を使ってください。

### チケット類

| 表記 | アイテムID | 備考 |
|------|-----------|------|
| ピックアップガシャチケット | `ticket_glo_00003` | ピックアップ対象を引ける |
| スペシャルガシャチケット | `ticket_glo_00002` | どのガシャでも使える |

### 通貨・汎用アイテム

| 表記 | 報酬タイプ | 備考 |
|------|-----------|------|
| コイン | `Coin` | resource_id 不要 |
| プリズム | `FreeDiamond` | 無償ダイヤ。resource_id 不要 |

### カラーメモリー（Lv上限開放素材）

| 表記 | アイテムID | 属性 |
|------|-----------|------|
| カラーメモリー・グレー | `memory_glo_00001` | 無属性 |
| カラーメモリー・レッド | `memory_glo_00002` | 赤 |
| カラーメモリー・ブルー | `memory_glo_00003` | 青 |
| カラーメモリー・イエロー | `memory_glo_00004` | 黄 |
| カラーメモリー・グリーン | `memory_glo_00005` | 緑 |

### メモリーフラグメント（Lv上限開放素材・色共通）

| 表記 | アイテムID | レアリティ |
|------|-----------|-----------|
| メモリーフラグメント・初級 | `memoryfragment_glo_00001` | SR相当 |
| メモリーフラグメント・中級 | `memoryfragment_glo_00002` | SSR相当 |
| メモリーフラグメント・上級 | `memoryfragment_glo_00003` | UR相当 |

> **その他アイテム**: 上記にない場合はアイテムIDを直接記入するか、「アイテム名（item_xxx_xxxxx）× N個」と書く。

---

## 記入済みサンプル（実データ: event_kim_00001 より）

```
# イベントログインボーナス 要件テキスト

## 基本情報

- イベントID: event_kim_00001
- リリースキー: 202602020
- ボーナス表示名: 君のことが大大大大大好きな100人の彼女 いいジャン祭 特別ログインボーナス

## 期間

- 開始: 2026-02-17 00:00
- 終了: 2026-03-02 12:59

## 日別報酬

1日目: ピックアップガシャチケット × 2
2日目: コイン × 5000
3日目: プリズム × 50
4日目: メモリーフラグメント・初級 × 15
5日目: カラーメモリー・イエロー × 600
6日目: カラーメモリー・ブルー × 600
7日目: スペシャルガシャチケット × 2
8日目: コイン × 10000
9日目: プリズム × 50
10日目: カラーメモリー・レッド × 600
11日目: カラーメモリー・グリーン × 600
12日目: メモリーフラグメント・中級 × 4
13日目: ピックアップガシャチケット × 1
14日目: スペシャルガシャチケット × 1
```

---

## このフォーマットをClaudeに渡す際の依頼文例

```
以下の要件テキストをもとに、イベントログインボーナスのマスタデータCSVを生成してください。

【生成対象】
- MstMissionEventDailyBonusSchedule.csv（新規1行）
- MstMissionEventDailyBonus.csv（新規N行）
- MstMissionReward.csv（追記N行。既存の最大IDの続き番号から採番）

【ID採番】
- MstMissionReward の id は、既存CSVの最大連番+1から採番してください。

---
（要件テキストをここに貼り付け）
```

---

## 補足: テーブル間の関係

```
MstMissionEventDailyBonusSchedule（1行）
  ├─ id: {event_id}_daily_bonus
  ├─ mst_event_id: event_id
  ├─ start_at: 開始日時（JST）
  └─ end_at: 終了日時（JST）
    ↓
MstMissionEventDailyBonus（N日分）
  ├─ id: {schedule_id}_{N日目:ゼロ埋め2桁}
  ├─ mst_mission_event_daily_bonus_schedule_id: schedule_id
  ├─ login_day_count: N
  ├─ mst_mission_reward_group_id: id と同じ値
  ├─ sort_order: 1（固定）
  └─ 備考: 報酬のアイテム名（日本語）
    ↓
MstMissionReward（N行）
  ├─ id: mission_reward_{連番}
  ├─ group_id: {MstMissionEventDailyBonus の mst_mission_reward_group_id}
  ├─ resource_type: Coin / FreeDiamond / Item / ...
  ├─ resource_id: アイテムID（Coin/FreeDiamond は空）
  ├─ resource_amount: 数量
  └─ 備考: ボーナス表示名
```

---

## 注意事項

- **日数**: イベント期間と一致させる必要はない（例: 14日イベントに14日分設定が標準）
- **連番ゼロ埋め**: `MstMissionEventDailyBonus.id` の連番は2桁ゼロ埋め（`_01`, `_02`...）が最近の慣例
- **MstMissionReward の id**: `mission_reward_{N}` 形式。既存CSVの最大Nの続きから採番すること
