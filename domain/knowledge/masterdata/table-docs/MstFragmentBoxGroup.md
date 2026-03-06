# MstFragmentBoxGroup 詳細説明

> CSVパス: `projects/glow-masterdata/MstFragmentBoxGroup.csv`

---

## 概要

`MstFragmentBoxGroup` は**かけらBOXアイテムの中身（ラインナップされるキャラのかけら）を定義するテーブル**。1レコードが「あるBOXグループの中に含まれる特定のかけら」1件に対応し、`mst_fragment_box_group_id` でグループ化される。

かけらBOXとは、開封するとランダムに選ばれたキャラのかけら（`CharacterFragment` タイプのアイテム）が入手できるボックスアイテム。このテーブルで「どのBOXにどのかけらが入っているか」と「いつからいつまでそのかけらが対象か」を期間指定で管理する。

### ゲームプレイへの影響

- `mst_fragment_box_group_id` でグループ化されたレコード群が1つのBOXのラインナップになる。
- **start_at / end_at** で各かけらの対象期間を制御できるため、新キャラ追加に応じてBOXのラインナップを更新できる。
- プレイヤーがBOXを開封する際、このテーブルから有効期間内のかけらをランダム選択してアイテムが付与される。

### 関連テーブルとの構造図

```
MstFragmentBox（かけらBOXの設定）
  └─ mst_fragment_box_group_id → MstFragmentBoxGroup.mst_fragment_box_group_id（1:N）
         └─ mst_item_id → MstItem.id（かけらアイテムの定義）

MstFragmentBoxGroup
  └─ mst_fragment_box_group_id（グループID）でグループ化
  └─ mst_item_id → MstItem.id（かけらアイテム）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（UUID形式） |
| `mst_fragment_box_group_id` | varchar(255) | 不可 | - | グループID。`MstFragmentBox.mst_fragment_box_group_id` と対応 |
| `mst_item_id` | varchar(255) | 不可 | - | ラインナップされるかけらアイテムのID（`mst_items.id`） |
| `start_at` | timestamp | 不可 | - | このかけらがBOXラインナップに含まれ始める日時 |
| `end_at` | timestamp | 不可 | - | このかけらがBOXラインナップから外れる日時 |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | `{group_id}_{連番}` | `fragment_box_1_1`, `fragment_box_1_2` |
| mst_fragment_box_group_id | `fragment_box_{グループ番号}` | `fragment_box_1` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_fragment_boxes` | `mst_fragment_box_group_id` → `mst_fragment_box_group_id` | BOXとラインナップの紐付け（N:1） |
| `mst_items` | `mst_item_id` → `id` | かけらアイテムの参照（N:1） |

---

## 実データ例

**パターン1: キャラかけらをグループに追加（長期設定）**
```
ENABLE: e
id: fragment_box_1_1
mst_fragment_box_group_id: fragment_box_1
mst_item_id: piece_aka_00001
start_at: 2024-01-01 00:00:00
end_at: 2037-12-31 23:59:59
release_key: 202509010
```
- `fragment_box_1` グループの1番目のかけら
- `piece_aka_00001`（キャラ「赤」のかけら）を設定
- 終了日を遠い未来に設定して実質無期限で対象にする

**パターン2: 別キャラのかけらを同グループに追加**
```
ENABLE: e
id: fragment_box_1_3
mst_fragment_box_group_id: fragment_box_1
mst_item_id: piece_rik_00001
start_at: 2024-01-01 00:00:00
end_at: 2037-12-31 23:59:59
release_key: 202509010
```
- 同じ `fragment_box_1` グループに別キャラのかけらを追加
- グループ内の全かけらが開封時のランダム選択候補になる

---

## 設定時のポイント

1. **期間設定で動的なラインナップ更新**: `start_at / end_at` を活用することで、新キャラ追加時にBOXのラインナップに追加できる。既存レコードを変更せずに新レコードを追加するだけでよい。
2. **end_at は遠い未来**: 実質無期限で有効にしたい場合は `2037-12-31 23:59:59` など遠い未来を設定する慣習。
3. **mst_item_id の確認**: 設定するアイテムIDが `MstItem` に存在し、`type = CharacterFragment` であることを確認する。他の type を設定しても動作するが、かけらBOXの仕様としては `CharacterFragment` を想定している。
4. **グループとBOXの対応確認**: `mst_fragment_box_group_id` が `MstFragmentBox.mst_fragment_box_group_id` と一致していることを確認する。不一致だとBOXを開封しても中身が空になる。
5. **重複かけらの回避**: 同一グループ内に同じ `mst_item_id` を複数設定すると、そのかけらが当選確率的に優遇される。意図しない重複を避ける。
6. **id の連番管理**: `{group_id}_{連番}` の形式で管理し、グループ内で一意であることを確認する。
