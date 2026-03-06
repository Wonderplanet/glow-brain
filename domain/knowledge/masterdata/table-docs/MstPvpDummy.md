# MstPvpDummy 詳細説明

> CSVパス: `projects/glow-masterdata/MstPvpDummy.csv`

---

## 概要

`MstPvpDummy` は**PvPマッチングに使用するダミーユーザー（CPU対戦相手）のマスタテーブル**。ランク区分・ランクレベル・マッチングタイプ（格上/同格/格下）に応じて、対戦相手として出現するダミーユーザーを定義する。

実際のプレイヤーとのマッチングが成立しない場合や、特定のランク帯でのマッチング相手として、このテーブルで定義されたダミーユーザーが使用される。各ランク・マッチングタイプに対して複数のダミーユーザーが登録されており、バリエーションを持たせている。

### ゲームプレイへの影響

- ランク区分（`rank_class_type`）とレベル（`rank_class_level`）の組み合わせでマッチングするダミーを絞り込む
- `matching_type` で格上/同格/格下のカテゴリを指定し、ランキングポイント計算に影響する
- `mst_dummy_user_id` が参照するダミーユーザーのデッキ・パーティが実際の対戦相手として使用される

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `rank_class_type` | enum('Bronze','Silver','Gold','Platinum') | 不可 | - | PVPランク区分 |
| `rank_class_level` | int unsigned | 不可 | 1 | PVPランクレベル（0始まり） |
| `matching_type` | enum('Upper','Same','Lower') | 不可 | - | マッチングタイプ（格上/同格/格下） |
| `mst_dummy_user_id` | varchar(255) | 不可 | - | ダミーユーザーID（`mst_dummy_users.id`） |

---

## 主要なEnum値

### rank_class_type（PVPランク区分）

| 値 | 説明 |
|----|------|
| `Bronze` | ブロンズ |
| `Silver` | シルバー |
| `Gold` | ゴールド |
| `Platinum` | プラチナ |

### matching_type（マッチングタイプ）

| 値 | 説明 | ボーナスポイントの影響 |
|----|------|------------------|
| `Upper` | 格上（プレイヤーより強いランク相手） | 勝利時に多くのボーナスポイント（WinUpperBonus） |
| `Same` | 同格（プレイヤーと同ランク相手） | 勝利時に中程度のボーナスポイント（WinSameBonus） |
| `Lower` | 格下（プレイヤーより弱いランク相手） | 勝利時に少ないボーナスポイント（WinLowerBonus） |

---

## 命名規則 / IDの生成ルール

- `id`: `pvp_dummy_{連番}` 形式（例: `pvp_dummy_13`、`pvp_dummy_22`）
- `mst_dummy_user_id`: `{ランク区分（小文字)}_{レベル}_user{番号}` 形式（例: `bronze_0_user1`、`bronze_1_user3`）

---

## 他テーブルとの連携

```
MstPvpDummy
  └─ mst_dummy_user_id → MstDummyUser.id（ダミーユーザーの詳細設定）

PvPマッチングサーバーロジック
  └─ プレイヤーのランク + マッチングタイプ → MstPvpDummy で対象ダミーを選択
```

インデックス `mst_pvp_dummies_rank_class_type_rank_class_level_index` が `(rank_class_type, rank_class_level)` に設定されており、ランク絞り込みの高速化が図られている。

---

## 実データ例

**パターン1: Bronze Lv.0 のダミー一覧（格上/同格/格下 2体ずつ）**

| ENABLE | id | release_key | rank_class_type | rank_class_level | matching_type | mst_dummy_user_id |
|--------|-----|-------------|----------------|----------------|--------------|-----------------|
| e | pvp_dummy_13 | 202509010 | Bronze | 0 | Lower | bronze_0_user1 |
| e | pvp_dummy_14 | 202509010 | Bronze | 0 | Lower | bronze_0_user2 |
| e | pvp_dummy_15 | 202509010 | Bronze | 0 | Same | bronze_0_user3 |
| e | pvp_dummy_16 | 202509010 | Bronze | 0 | Same | bronze_0_user4 |
| e | pvp_dummy_17 | 202509010 | Bronze | 0 | Upper | bronze_0_user5 |
| e | pvp_dummy_18 | 202509010 | Bronze | 0 | Upper | bronze_0_user6 |

**パターン2: Bronze Lv.1 のダミー**

| ENABLE | id | release_key | rank_class_type | rank_class_level | matching_type | mst_dummy_user_id |
|--------|-----|-------------|----------------|----------------|--------------|-----------------|
| e | pvp_dummy_19 | 202509010 | Bronze | 1 | Lower | bronze_1_user1 |
| e | pvp_dummy_21 | 202509010 | Bronze | 1 | Same | bronze_1_user3 |

---

## 設定時のポイント

1. **各ランク・レベル・マッチングタイプに複数ダミーを設定**: バリエーションを持たせるため、同一の (rank_class_type, rank_class_level, matching_type) の組み合わせに複数のダミーユーザーを割り当てる
2. **matching_typeの3種類をすべて設定**: Upper/Same/Lowerを各ランク・レベルに設定しないとマッチング時にエラーになる可能性がある
3. **mst_dummy_user_idは既存ダミーユーザーと一致させる**: `MstDummyUser` テーブルに存在しないIDを指定すると正常に動作しない
4. **rank_class_levelは0始まり**: `MstPvpRank` テーブルの `rank_class_level` と対応している（Bronze Lv.0が最低ランク）
5. **インデックスが設定済み**: `rank_class_type` + `rank_class_level` のインデックスにより高速に絞り込まれる
