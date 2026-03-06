# MstComebackBonusSchedule 詳細説明

> CSVパス: `projects/glow-masterdata/MstComebackBonusSchedule.csv`

---

## 概要

カムバックボーナスのスケジュール設定を管理するマスタテーブル。
カムバックボーナスとは、一定期間ログインしていなかったユーザーが復帰した際に付与される特別ボーナス機能。
このテーブルは未ログイン条件日数・有効日数・実施期間を定義し、実際のボーナス内容は `mst_comeback_bonuses` テーブルで管理する。

クライアントクラス: `MstComebackBonusScheduleData.cs`

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | カムバックボーナススケジュールID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| inactive_condition_days | int unsigned | YES | カムバックボーナスを受け取るための未ログイン期間（日数） |
| duration_days | int | YES | ボーナスを受け取れる有効日数（復帰後この日数以内に受け取れる） |
| start_at | timestamp | YES | このスケジュールの有効開始日時 |
| end_at | timestamp | YES | このスケジュールの有効終了日時 |

---

## 命名規則 / IDの生成ルール

IDは `comeback_daily_bonus_{連番}` の形式が一般的。

例:
- `comeback_daily_bonus_1` → 1番目のカムバックスケジュール設定

---

## 他テーブルとの連携

| 参照元テーブル | カラム | 内容 |
|---|---|---|
| `mst_comeback_bonuses` | `mst_comeback_bonus_schedule_id` | スケジュールに対応するボーナス内容一覧 |

---

## 実データ例

**例1: 現在運用中のカムバックボーナススケジュール**

| id | release_key | inactive_condition_days | duration_days | start_at | end_at |
|---|---|---|---|---|---|
| comeback_daily_bonus_1 | 202510010 | 14 | 8 | 2025-10-06 04:00:00 | 2034-01-01 00:00:00 |

- 14日以上ログインしていなかったユーザーが対象
- 復帰後8日間、毎日ボーナスを受け取れる
- 2025年10月6日から2034年1月1日まで有効

---

## 設定時のポイント

1. `inactive_condition_days` は復帰ボーナスの対象となる未ログイン最小日数。現在の設定は14日（2週間）未ログインが条件。
2. `duration_days` は復帰後にボーナスを受け取れる日数で、`mst_comeback_bonuses` のレコード数と一致させること。
3. `start_at` / `end_at` でスケジュールの有効期間を設定する。長期運用の場合は `end_at` を遠い将来に設定する。
4. `end_at` は `start_at` より後の日時を必ず設定する。
5. スケジュールを変更する場合は旧スケジュールの `end_at` を現在日時に更新し、新スケジュールを追加する。
6. `mst_comeback_bonuses` テーブルの `login_day_count` が1〜`duration_days` の範囲で全日分設定されていることを確認する。
7. 長期間のカムバックスケジュールを設定する場合、ゲームバランスの変化により途中で閾値変更が必要になる場合がある。
8. このテーブルはサーバー側で「現在有効なスケジュール」を取得する際に `start_at` ≤ 現在時刻 ≤ `end_at` で絞り込まれる。
