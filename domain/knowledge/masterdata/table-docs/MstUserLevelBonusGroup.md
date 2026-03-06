# MstUserLevelBonusGroup 詳細説明

> CSVパス: `projects/glow-masterdata/MstUserLevelBonusGroup.csv`

---

## 概要

プレイヤーレベルアップ報酬のグルーピングと各報酬の具体的な内容を定義するテーブル。
`mst_user_level_bonus_group_id` で複数の報酬アイテムをグループ化し、1つのグループIDに対して複数の報酬を紐付けることができる。
mst_user_level_bonuses から参照され、特定レベル到達時に付与される報酬リストとして機能する。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID（CSVでは連番整数） |
| mst_user_level_bonus_group_id | varchar(255) | NOT NULL | - | 報酬グループID（CSVでは整数） |
| resource_type | varchar(255) | NOT NULL | - | 報酬タイプ |
| resource_id | varchar(255) | NULL | - | 報酬アイテムのID |
| resource_amount | int | NOT NULL | - | 報酬の個数 |
| release_key | bigint | NOT NULL | 1 | リリースキー |

---

## resource_type（報酬タイプ）

| 値 | 説明 |
|---|---|
| Item | アイテム（メモリー・メモリーフラグメントなど） |

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| mst_user_level_bonuses | mst_user_level_bonus_group_id | どのレベルにこのグループが対応するか |
| mst_user_levels | level | プレイヤーレベルの定義 |

---

## 実データ例（CSVから取得）

### パターン1: グループID=1（メモリー5種セット）

グループ1はレベル10・15・25・35・45・55・65・75・85・95に対応する基本報酬。

| id | mst_user_level_bonus_group_id | resource_type | resource_id | resource_amount | release_key |
|---|---|---|---|---|---|
| 1 | 1 | Item | memory_glo_00001 | 100 | 202509010 |
| 2 | 1 | Item | memory_glo_00002 | 100 | 202509010 |
| 3 | 1 | Item | memory_glo_00003 | 100 | 202509010 |
| 4 | 1 | Item | memory_glo_00004 | 100 | 202509010 |
| 5 | 1 | Item | memory_glo_00005 | 100 | 202509010 |

### パターン2: グループID=3（SR+SSRメモリーフラグメントセット、レベル30対応）

| id | mst_user_level_bonus_group_id | resource_type | resource_id | resource_amount | release_key |
|---|---|---|---|---|---|
| 7 | 3 | Item | memoryfragment_glo_00001 | 5 | 202509010 |
| 8 | 3 | Item | memoryfragment_glo_00002 | 2 | 202509010 |

---

## 設定時のポイント

1. **1つのグループIDに複数の報酬アイテムを紐付けられる**: 同じ `mst_user_level_bonus_group_id` を持つ複数レコードで報酬リストを構成する。
2. **グループIDはmst_user_level_bonusesから参照される**: 本テーブルのグループIDは必ず mst_user_level_bonuses のどこかのレベルと紐付ける。
3. **グループIDの再利用が可能**: グループ1はレベル10・15・25・35など複数のレベルで共有されている（同じ報酬セットを使い回す）。
4. **同じグループ内の resource_id は重複しないようにする**: 同一グループ内に同じアイテムIDが複数あると処理が不定になる可能性がある。
5. **新しい報酬グループ追加時はIDを連番で振る**: 既存の最大グループIDに +1 して新しいグループIDを設定する。
6. **resource_id はアイテムマスタのIDと一致させる**: 存在しないアイテムIDを設定するとゲーム内でエラーが発生する。
7. **release_key はリリース管理に使用**: 新しいレベル上限追加に伴う新報酬グループ追加時は適切なリリースキーを設定する。
