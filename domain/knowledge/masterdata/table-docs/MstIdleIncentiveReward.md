# MstIdleIncentiveReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstIdleIncentiveReward.csv`

---

## 概要

`MstIdleIncentiveReward` は**探索（放置報酬）でステージ進捗（クリアしたステージ）に応じた報酬設定テーブル**。1レコードが「特定のステージに到達したプレイヤーへの探索報酬設定」に対応し、N分ごとのコイン・経験値・ランクアップ素材のベース獲得量とアイテムグループIDを管理する。

プレイヤーが特定のステージをクリアすることで、より高い報酬レートの設定が適用される仕組みを実現する。

### ゲームプレイへの影響

- **mst_stage_id** がこの報酬設定が適用されるステージ進捗の閾値。プレイヤーがこのステージに到達（クリア）した時点からこの設定が適用される。
- **base_coin_amount**: N分（`MstIdleIncentive.reward_increase_interval_minutes` 分）ごとに獲得できる基礎コイン量。
- **base_exp_amount**: N分ごとに獲得できる基礎経験値量。
- **base_rank_up_material_amount**: N分ごとに獲得できるリミテッドメモリーの基礎量。
- **mst_idle_incentive_item_group_id**: このステージ帯でドロップするアイテムグループ（`MstIdleIncentiveItem` のグループID）。

### 関連テーブルとの構造図

```
MstIdleIncentiveReward（ステージ別報酬設定）
  ├─ mst_stage_id → MstStage.id（報酬切り替えの閾値ステージ）
  └─ mst_idle_incentive_item_group_id → MstIdleIncentiveItem.mst_idle_incentive_item_group_id（アイテムドロップ設定）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（UUID）。`mst_idle_incentive_reward_{連番}` 形式 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_stage_id` | varchar(255) | 不可 | - | この報酬設定が適用されるステージ進捗の閾値（`mst_stages.id`） |
| `base_coin_amount` | decimal(10,4) | 不可 | - | N分ごとのコイン基礎獲得量 |
| `base_exp_amount` | decimal(10,4) | 不可 | - | N分ごとの経験値基礎獲得量 |
| `base_rank_up_material_amount` | decimal(10,2) | 不可 | 1.00 | N分ごとのリミテッドメモリー基礎獲得量 |
| `mst_idle_incentive_item_group_id` | varchar(255) | 不可 | - | ドロップするアイテムグループID（`mst_idle_incentive_items.mst_idle_incentive_item_group_id`） |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | `mst_idle_incentive_reward_{連番}` | `mst_idle_incentive_reward_1` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_stages` | `mst_stage_id` → `id` | 適用ステージの閾値（N:1） |
| `mst_idle_incentive_items` | `mst_idle_incentive_item_group_id` → `mst_idle_incentive_item_group_id` | アイテムドロップ設定（1:N） |

---

## 実データ例

**パターン1: チュートリアルステージの報酬（低報酬）**
```
ENABLE: e
id: mst_idle_incentive_reward_1
release_key: 202509010
mst_stage_id: tutorial_1
base_coin_amount: 2.0
base_exp_amount: 1.0
base_rank_up_material_amount: 0
mst_idle_incentive_item_group_id: idle_incentive_group_tutorial_1
```
- チュートリアル1段階目でのベース報酬
- コイン2.0/interval、経験値1.0/interval
- ランクアップ素材は0（初期は獲得不可）

**パターン2: 通常ステージの報酬（漸増パターン）**
```
ENABLE: e
id: mst_idle_incentive_reward_5
release_key: 202509010
mst_stage_id: normal_spy_00002
base_coin_amount: 2.051
base_exp_amount: 1.103
base_rank_up_material_amount: 0
mst_idle_incentive_item_group_id: idle_incentive_group_spy2
```
- ステージ2クリア後の報酬
- コイン・経験値がステージ1より微増（漸増設計）

---

## 設定時のポイント

1. **mst_stage_id との厳密な対応**: `mst_stage_id` が `MstStage.id` に存在するIDでないといけない。存在しないIDを設定するとサーバー側でこのレコードが適用されない。
2. **ステージ進捗の閾値設計**: 各ステージをクリアするたびに対応するレコードを作成する。ステージが増えるたびにレコードを追加する。
3. **base_amount の漸増設計**: ステージが進むにつれてコイン・経験値が徐々に増える設計になっている。急増させずに緩やかに増加させることでゲームバランスを保つ。
4. **base_rank_up_material_amount**: 初期ステージでは0に設定し、一定ステージ進捗以降から解放する設計が一般的。`decimal(10,2)` で小数2桁まで設定可能。
5. **アイテムグループとの対応**: `mst_idle_incentive_item_group_id` が `MstIdleIncentiveItem` のグループIDと一致していることを確認する。不一致だとアイテムドロップが0になる。
6. **81件のレコード管理**: 現在81件のステージ-報酬ペアが存在。新ステージ追加時は対応するレコードをこのテーブルにも追加する。
7. **id の命名**: `mst_idle_incentive_reward_{連番}` の形式で、現在の最大連番の次を使用して採番する。
