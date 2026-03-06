# MstConfig 詳細説明

> CSVパス: `projects/glow-masterdata/MstConfig.csv`

---

## 概要

ゲーム全体で使用される定数値（設定値）をキーバリュー形式で一元管理するマスタテーブル。
ユニットレベル上限・スタミナ回復時間・ガチャコスト・バトルポイント上限・ローカル通知時間など、ゲームの基本パラメータを管理する。
コードに埋め込まず、このテーブルを参照することで設定値の変更をコードリリースなしに対応できる。

クライアントクラス: `MstConfigData.cs`（`Key` と `Value` の2フィールドのみ保持）

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | レコードID（主キー） |
| release_key | int | YES | リリースキー（デフォルト: 1） |
| key | varchar(255) | YES | 設定キー名（UNIQUE制約あり） |
| value | text | YES | 設定値（文字列として保持、型は設定キーに依存） |

ユニークキー: `key` カラム単独で一意となる。

---

## 主要な設定キー一覧

| key | value（現在値） | 説明 |
|---|---|---|
| UNIT_LEVEL_CAP | 80 | ユニットの最大レベル |
| UNIT_STATUS_EXPONENT | 1.1 | ユニットステータスの指数 |
| SPECIAL_UNIT_STATUS_EXPONENT | 1.1 | 特別ユニットのステータス指数 |
| MAX_DAILY_BUY_STAMINA_AD_COUNT | 10 | 1日のスタミナ広告購入上限回数 |
| DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES | 3 | スタミナ広告購入の間隔（分） |
| BUY_STAMINA_DIAMOND_AMOUNT | 30 | ダイヤでスタミナ購入時のコスト |
| BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA | 50 | 広告でのスタミナ回復量（最大スタミナに対する%） |
| BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA | 100 | ダイヤでのスタミナ回復量（最大スタミナに対する%） |
| STAGE_CONTINUE_DIAMOND_AMOUNT | 30 | ステージコンティニューのダイヤコスト |
| USER_FREE_DIAMOND_MAX_AMOUNT | 999999999 | 無償ダイヤの所持上限 |
| USER_PAID_DIAMOND_MAX_AMOUNT | 999999999 | 有償ダイヤの所持上限 |
| USER_ITEM_MAX_AMOUNT | 999999999 | アイテム所持上限 |
| USER_EXP_MAX_AMOUNT | 999999999 | 経験値所持上限 |
| USER_COIN_MAX_AMOUNT | 999999999 | コイン所持上限 |
| USER_STAMINA_MAX_AMOUNT | 999 | スタミナ最大値 |
| RECOVERY_STAMINA_MINUTE | 3 | スタミナ1回復に必要な時間（分） |
| ENHANCE_QUEST_CHALLENGE_LIMIT | 3 | 強化クエストの挑戦上限回数 |
| ENHANCE_QUEST_CHALLENGE_AD_LIMIT | 2 | 強化クエストの広告挑戦追加上限 |
| IN_GAME_MAX_BATTLE_POINT | 2000 | インゲームバトルポイントの上限 |
| IN_GAME_BATTLE_POINT_CHARGE_AMOUNT | 3 | 1回のチャージで増えるバトルポイント量 |
| IN_GAME_BATTLE_POINT_CHARGE_INTERVAL | 5 | バトルポイントチャージ間隔（フレーム数） |
| PARTY_SPECIAL_UNIT_ASSIGN_LIMIT | 10 | パーティに配置できる特別ユニットの上限 |
| RUSH_DAMAGE_COEFFICIENT | 0.4 | ラッシュ攻撃ダメージ係数 |
| RUSH_MAX_DAMAGE | 99999999 | ラッシュ攻撃の最大ダメージ |
| FREEZE_DAMAGE_INCREASE_PERCENTAGE | 120 | フリーズ状態の追加ダメージ率（%） |
| ADVENT_BATTLE_RANKING_UPDATE_INTERVAL_MINUTES | 5 | アドベントバトルランキング更新間隔（分） |

**ローカル通知関連キー（抜粋）:**

| key | value（現在値） | 説明 |
|---|---|---|
| LOCAL_NOTIFICATION_IDLE_INCENTIVE_HOURS | 20 | 放置報酬ローカル通知（時間） |
| LOCAL_NOTIFICATION_DAILY_MISSION_HOURS | 18 | デイリーミッション通知（時間） |
| LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_ONE | 24 | 最終ログイン後24時間で通知 |
| LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_TWO | 72 | 最終ログイン後72時間（3日）で通知 |
| LOCAL_NOTIFICATION_LOGIN_AFTER_HOURS_THREE | 168 | 最終ログイン後168時間（7日）で通知 |

---

## 他テーブルとの連携

このテーブルは他のマスタテーブルとの外部キー連携はない。サーバー・クライアント両方が直接参照する。

---

## 実データ例

**例1: ユニット関連定数**

| id | key | value |
|---|---|---|
| 1 | UNIT_LEVEL_CAP | 80 |
| 2 | UNIT_STATUS_EXPONENT | 1.1 |

**例2: バトルポイント関連定数**

| id | key | value |
|---|---|---|
| 24 | IN_GAME_MAX_BATTLE_POINT | 2000 |
| 25 | IN_GAME_BATTLE_POINT_CHARGE_AMOUNT | 3 |
| 26 | IN_GAME_BATTLE_POINT_CHARGE_INTERVAL | 5 |

---

## 設定時のポイント

1. `key` は UNIQUE 制約があるため、同一キーを重複登録できない。既存キーの値変更は `value` を更新する。
2. `value` は text型（文字列）として保存されるため、数値・真偽値・文字列などを文字列として設定する。参照側で型変換が必要。
3. 新しい定数を追加する場合は `key` 名を大文字スネークケース（`UPPER_SNAKE_CASE`）で命名する。
4. ローカル通知に関するキーは `LOCAL_NOTIFICATION_` プレフィックスで統一されている。
5. デバッグ用の設定は `DEBUG_` プレフィックスを付ける（例: `DEBUG_GRANT_ARTWORK_IDS`）。
6. バトルポイント設定はかつて `mst_battle_point_levels` テーブルで管理される予定だったが、現在このテーブルで管理している（`IN_GAME_*` キー）。
7. 所持上限系（`USER_*_MAX_AMOUNT`）は現在999999999（実質無制限）に設定されており、上限制限を加える場合は適切な値に変更する。
8. `release_key` はint型（他テーブルはbigint）であることに注意。
