# MstExchangeLineup 詳細説明

> CSVパス: `projects/glow-masterdata/MstExchangeLineup.csv`

---

## 概要

`MstExchangeLineup` は**交換所に並ぶ個々のラインナップアイテム（交換枠）を定義するテーブル**。1レコードが1つの交換枠に相当し、交換上限数と表示順を管理する。`group_id` でグループ化することで `MstExchange.lineup_group_id` と紐付く。

### ゲームプレイへの影響

- **group_id** が `MstExchange.lineup_group_id` と一致するラインナップレコード群が1つの交換所に表示されるアイテム一覧になる。
- **tradable_count** で各アイテムの交換上限を設定する。NULLの場合は無制限に交換可能。
- **display_order** で交換所内のアイテム一覧の並び順を制御する（昇順）。
- このテーブル単独では交換内容を持たず、`MstExchangeCost`（コスト）と `MstExchangeReward`（報酬）の橋渡し役として機能する。

### 関連テーブルとの構造図

```
MstExchange
  └─ lineup_group_id → MstExchangeLineup.group_id（1:N）
         └─ MstExchangeLineup.id → MstExchangeCost.mst_exchange_lineup_id（コスト）
         └─ MstExchangeLineup.id → MstExchangeReward.mst_exchange_lineup_id（報酬）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。ラインナップ枠の一意識別子 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `group_id` | varchar(255) | 不可 | - | グループID。`MstExchange.lineup_group_id` と対応する |
| `tradable_count` | int unsigned | 可 | - | 交換上限数。NULLは無制限 |
| `display_order` | int unsigned | 不可 | 0 | 交換所内の表示順（昇順） |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | `{group_id}_{5桁連番}` | `normal_01_lineup_00001` |
| group_id | `{exchange_id}_lineup` | `normal_01_lineup` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_exchanges` | `group_id` → `lineup_group_id` | 所属する交換所（N:1） |
| `mst_exchange_costs` | `id` → `mst_exchange_lineup_id` | コスト定義（1:1） |
| `mst_exchange_rewards` | `id` → `mst_exchange_lineup_id` | 報酬定義（1:1） |

---

## 実データ例

**パターン1: 交換上限あり（限定アイテム枠）**
```
ENABLE: e
id: normal_01_lineup_00001
group_id: normal_01_lineup
tradable_count: 2
display_order: 1
release_key: 202512015
```
- `tradable_count: 2` でこの枠は最大2回だけ交換可能
- `display_order: 1` で交換所の先頭に表示

**パターン2: 交換上限多め（素材枠）**
```
ENABLE: e
id: normal_01_lineup_00002
group_id: normal_01_lineup
tradable_count: 50
display_order: 2
release_key: 202512015
```
- `tradable_count: 50` で最大50回交換可能
- `display_order: 2` で2番目に表示

---

## 設定時のポイント

1. **group_id と MstExchange の対応**: `group_id` の値が `MstExchange.lineup_group_id` と完全一致していないとラインナップが表示されない。スペルミスに注意する。
2. **tradable_count の NULL**: 無制限交換にしたい場合は `tradable_count` をNULL（CSVでは空欄または `__NULL__`）にする。限定感を出したい場合は交換上限を明示的に設定する。
3. **display_order の設計**: 連番（1, 2, 3...）または10刻み（10, 20, 30...）で設定する。後から間に追加しやすいよう10刻みが推奨。
4. **コストと報酬の必須設定**: ラインナップ1件につき、`MstExchangeCost` と `MstExchangeReward` を必ず1レコードずつ作成する。どちらが欠けても交換機能が正常に動作しない。
5. **id の命名**: `{group_id}_{5桁連番}` 形式で統一する。`group_id` が `normal_01_lineup` なら `normal_01_lineup_00001` のようにする。
6. **グループ内の連番管理**: 同一 `group_id` 内でIDの連番が重複しないよう管理する。既存ラインナップに追加する場合は最大連番の次を使用する。
