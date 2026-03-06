# MstCheatSetting 詳細説明

> CSVパス: `projects/glow-masterdata/MstCheatSetting.csv`

---

## 概要

インゲームコンテンツごとに検出すべきチート手法と判定閾値を設定するマスタテーブル。
コンテンツタイプ（アドベントバトル・PVPなど）×チートタイプの組み合わせで判定基準を定義し、チート検出時のランキング除外フラグも設定できる。
チート対策の閾値は時期によって調整されるため、有効期間（start_at / end_at）も設定する。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | レコードID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| content_type | enum('AdventBattle','Pvp') | YES | 対象コンテンツのタイプ |
| cheat_type | enum('BattleTime','MaxDamage','BattleStatusMismatch','MasterDataStatusMismatch') | YES | チートの種別 |
| cheat_value | int | YES | チートと判定する閾値 |
| is_excluded_ranking | tinyint unsigned | YES | チート検出時に即ランキング除外するか（1=除外, 0=除外しない, デフォルト: 0） |
| start_at | timestamp | YES | このチート設定の有効開始日時 |
| end_at | timestamp | YES | このチート設定の有効終了日時 |

---

## ContentType（content_type enumの値）

| 値 | 説明 |
|---|---|
| AdventBattle | アドベントバトル（降臨バトル）コンテンツ |
| Pvp | PVP（対人戦）コンテンツ |

## CheatType（cheat_type enumの値）

| 値 | 説明 | cheat_value の意味 |
|---|---|---|
| BattleTime | バトル時間チート | 最小許容バトル時間（秒）。この値未満のクリア時間を異常と判断 |
| MaxDamage | 最大ダメージチート | 1バトルで与えられる最大ダメージ上限。この値を超えると異常と判断 |
| BattleStatusMismatch | バトルステータス不整合 | ステータス値の不一致許容範囲（1=不整合あればチート） |
| MasterDataStatusMismatch | マスタデータステータス不整合 | マスタデータ値との不整合許容範囲（1=不整合あればチート） |

---

## 他テーブルとの連携

このテーブルはサーバー側のチート検出ロジックから参照される。他のマスタテーブルとの外部キー連携はない。

---

## 実データ例

**例1: テスト用設定（アドベントバトルの時間チート）**

| id | content_type | cheat_type | cheat_value | is_excluded_ranking | start_at | end_at |
|---|---|---|---|---|---|---|
| test_data | AdventBattle | BattleTime | 120 | 1 | 2024-08-23 11:00:00 | 2025-08-30 23:59:59 |

バトル時間が120秒未満はチートと判定し、即ランキング除外する設定。

**例2: 本番設定（アドベントバトルの複数チートタイプ）**

| id | content_type | cheat_type | cheat_value | is_excluded_ranking | start_at | end_at |
|---|---|---|---|---|---|---|
| 2 | AdventBattle | MaxDamage | 2000000 | 0 | 2025-09-22 11:00:00 | 2037-12-30 23:59:59 |
| 3 | AdventBattle | BattleStatusMismatch | 1 | 0 | 2025-09-22 11:00:00 | 2037-12-30 23:59:59 |
| 4 | AdventBattle | MasterDataStatusMismatch | 1 | 0 | 2025-09-22 11:00:00 | 2037-12-30 23:59:59 |
| 5 | Pvp | BattleTime | 1 | 0 | 2025-09-22 11:00:00 | 2037-12-30 23:59:59 |

---

## 設定時のポイント

1. `content_type` と `cheat_type` の組み合わせが重複しないよう設計する（ただし期間が異なれば重複可）。
2. `cheat_value` の意味はチートタイプによって異なる（秒数・ダメージ値・不整合フラグ）。設定前に各タイプの意味を確認すること。
3. `is_excluded_ranking` を `1` にすると即座にランキングから除外されるため、誤検知のリスクが高い場合は `0` に設定してログ記録のみに留める。
4. `start_at` / `end_at` で有効期間を設定し、期間外のレコードは検出対象外となる。長期間有効にする場合は `end_at` を遠い未来（`2037-12-30`など）に設定する。
5. 閾値（`cheat_value`）の調整が必要な場合は新しいレコードを追加し、旧レコードの期間を終了させることで切り替える。
6. テスト用データは有効期間を限定して設定し、本番環境への影響を避ける。
7. PVPとアドベントバトルは別々のチート設定が必要で、同じコンテンツでも複数のチートタイプをそれぞれ個別のレコードで設定する。
8. `MaxDamage` の閾値は現在200万ダメージで設定されており、インゲームバランスに合わせて定期的に見直す。
