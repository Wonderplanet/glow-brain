# MstPvpReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstPvpReward.csv`

---

## 概要

`MstPvpReward` は**PvPシーズン終了時に付与される報酬を定義するテーブル**。`mst_pvp_reward_group_id` でグルーピングされた報酬セットを管理し、ランキング順位別・ランク到達別・累計スコア別の3種類の報酬グループに対応する。

1つの `mst_pvp_reward_group_id` に対して複数のレコードを紐付けることで、複数種類のリソースをまとめて1つの「報酬セット」として扱える（例: チケット + コインのセット）。

### ゲームプレイへの影響

- **ランキング報酬** (`default_reward_ranking_{順位}`): シーズン終了時のランキング上位者に付与される
- **ランク到達報酬** (`default_reward_rank_{ランクID}`): 特定ランクに到達したプレイヤー全員に付与される
- **累計スコア報酬** (`default_reward_totalscore_{番号}`): シーズン中の累計スコアが一定値に達した際に付与される
- 上位ランキングほど多くのチケット・コインが付与される（1位=チケット5枚+50000コイン）

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_pvp_reward_group_id` | varchar(255) | 不可 | - | 報酬グループID（`mst_pvp_reward_groups.id`） |
| `resource_type` | enum | 不可 | - | 報酬タイプ（後述のenum参照） |
| `resource_id` | varchar(255) | 可 | NULL | 報酬ID（resource_typeがItem等の場合に使用） |
| `resource_amount` | int unsigned | 不可 | 0 | 報酬の個数・数量 |

---

## ResourceType（報酬タイプ）

| 値 | 説明 | resource_id |
|----|------|------------|
| `Coin` | コイン | NULL |
| `FreeDiamond` | 無償ダイヤ | NULL |
| `Item` | アイテム | アイテムID |
| `Emblem` | エンブレム | エンブレムID |

---

## 報酬グループIDの命名規則

| グループIDパターン | 説明 | 件数 |
|-----------------|------|-----|
| `default_reward_ranking_{順位}` | ランキング順位別報酬（1位〜8位） | 8グループ |
| `default_reward_rank_{番号}` | ランク到達報酬（17種類のランク） | 16グループ |
| `default_reward_totalscore_{番号}` | 累計スコア到達報酬 | 15グループ |

---

## 命名規則 / IDの生成ルール

- `id`: `{グループID}_{連番}` 形式（例: `default_reward_ranking_1_1`、`default_reward_ranking_1_2`）

---

## 他テーブルとの連携

```
MstPvpRewardGroup（mst_pvp_reward_groups）
  └─ id → MstPvpReward.mst_pvp_reward_group_id（報酬グループとの紐付け）

MstPvpReward
  └─ resource_id → MstItem.id（resource_type = Item の場合）
  └─ resource_id → MstEmblem.id（resource_type = Emblem の場合）
```

インデックス `mst_pvp_rewards_mst_pvp_reward_group_id_index` が `mst_pvp_reward_group_id` に設定されており、グループIDでの高速検索が可能。

---

## 実データ例

**パターン1: ランキング順位別報酬（上位ほど多い）**

| ENABLE | id | mst_pvp_reward_group_id | resource_type | resource_id | resource_amount |
|--------|-----|------------------------|---------------|-------------|-----------------|
| e | default_reward_ranking_1_1 | default_reward_ranking_1 | Item | ticket_glo_00002 | 5 |
| e | default_reward_ranking_1_2 | default_reward_ranking_1 | Coin | NULL | 50000 |
| e | default_reward_ranking_5_1 | default_reward_ranking_5 | Item | ticket_glo_00002 | 1 |
| e | default_reward_ranking_5_2 | default_reward_ranking_5 | Coin | NULL | 15000 |

**パターン2: ランク到達報酬（無償ダイヤ）**

| ENABLE | id | mst_pvp_reward_group_id | resource_type | resource_id | resource_amount |
|--------|-----|------------------------|---------------|-------------|-----------------|
| e | default_reward_rank_1_1 | default_reward_rank_1 | FreeDiamond | NULL | 30 |

---

## 設定時のポイント

1. **3種類の報酬グループを使い分ける**: ランキング報酬（`ranking`）・ランク到達報酬（`rank`）・累計スコア報酬（`totalscore`）の3種類を目的に応じて使用する
2. **mst_pvp_reward_group_idは事前登録が必要**: `MstPvpRewardGroup`（`mst_pvp_reward_groups`）テーブルに対応するグループIDが先に登録されている必要がある
3. **複数レコードで1報酬セットを構成**: チケット+コインのように複数種類のリソースを組み合わせて1グループを作る場合は複数レコードを追加する
4. **ランキング報酬は順位ごとに逓減設計**: 1位のチケット枚数・コイン量が最も多く、順位が下がるにつれて減少する設計にする
5. **resource_typeがItemの場合はresource_idを確認**: 対象のアイテムIDが `MstItem` テーブルに存在するか確認してから設定する
6. **resource_amountのデフォルトは0**: 設定忘れがないよう、必ず正の値を設定する
7. **シーズンごとに報酬内容を変更する場合**: 新しいグループIDを作成し、対応するレコードを追加する
