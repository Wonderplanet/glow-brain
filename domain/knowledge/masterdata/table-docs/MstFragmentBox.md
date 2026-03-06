# MstFragmentBox 詳細説明

> CSVパス: `projects/glow-masterdata/MstFragmentBox.csv`

---

## 概要

`MstFragmentBox` は**かけらBOXアイテムとそのBOXグループ（ラインナップ設定）の対応関係を定義するテーブル**。1レコードが「あるBOXアイテム（`MstItem`）が開封されたときに参照するラインナップグループ（`MstFragmentBoxGroup`）」の紐付けを表す。

このテーブルは橋渡しテーブルとして機能し、「アイテムとしてのかけらBOX」と「BOXの中身ラインナップ設定」を分離して管理することで、BOXアイテムのラインナップを柔軟に更新できる設計になっている。

### ゲームプレイへの影響

- プレイヤーがBOXアイテムを使用する際、このテーブルの `mst_item_id` でBOXアイテムを特定し、`mst_fragment_box_group_id` でラインナップを決定する。
- `mst_item_id` は `MstItem` で `type = RandomFragmentBox` または `SelectionFragmentBox` に設定されているアイテムが対応する。
- `mst_fragment_box_group_id` が `MstFragmentBoxGroup` のグループIDと対応し、実際の中身のかけら一覧を決定する。

### 関連テーブルとの構造図

```
MstItem（type = RandomFragmentBox 等）
  └─ id → MstFragmentBox.mst_item_id（1:1）
         └─ mst_fragment_box_group_id → MstFragmentBoxGroup.mst_fragment_box_group_id（1:N）
                └─ mst_item_id → MstItem.id（CharacterFragmentタイプのかけら）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（UUID）。`mst_item_id` と同じ値を使う慣習 |
| `mst_item_id` | varchar(255) | 不可 | - | BOXアイテムのID（`mst_items.id`）。ユニーク制約あり |
| `mst_fragment_box_group_id` | varchar(255) | 不可 | - | 中身ラインナップのグループID（`mst_fragment_box_groups.mst_fragment_box_group_id`） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | `fragment_box_{番号}` | `fragment_box_1` |
| mst_fragment_box_group_id | `fragment_box_{番号}` | `fragment_box_1`（idと同じ） |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_items` | `mst_item_id` → `id` | BOXアイテムの実体（1:1） |
| `mst_fragment_box_groups` | `mst_fragment_box_group_id` → `mst_fragment_box_group_id` | 中身ラインナップ（1:N） |

---

## 実データ例

**パターン1: かけらBOXアイテムとグループの紐付け**
```
ENABLE: e
id: fragment_box_1
mst_item_id: box_glo_00001
mst_fragment_box_group_id: fragment_box_1
release_key: 202509010
```
- BOXアイテム `box_glo_00001` を使用すると `fragment_box_1` グループのかけらが入手できる
- `id` と `mst_fragment_box_group_id` が同じ値になる慣習

**パターン2: 別のかけらBOX**
```
ENABLE: e
id: fragment_box_2
mst_item_id: box_glo_00002
mst_fragment_box_group_id: fragment_box_2
release_key: 202509010
```
- BOXアイテム `box_glo_00002` は `fragment_box_2` グループのラインナップを使用

---

## 設定時のポイント

1. **mst_item_id のユニーク制約**: `mst_item_id` にはユニーク制約があるため、1つのBOXアイテムに対して複数のグループを設定することはできない。BOXごとに1対1で対応する。
2. **id とグループIDの命名統一**: `id` と `mst_fragment_box_group_id` は同じ値にする慣習。`mst_item_id` は `MstItem` 側のIDと一致させる。
3. **MstItem との対応**: `mst_item_id` に設定するアイテムは `MstItem` に存在し、type が `RandomFragmentBox` または `SelectionFragmentBox` であることを確認する。
4. **グループの先行作成**: `MstFragmentBoxGroup` でグループとかけらのラインナップを先に定義してから、このテーブルでBOXとグループを紐付ける。逆順だと参照エラーになる可能性がある。
5. **全BOXを管理**: 現在8種類のかけらBOXが登録されている。新しいBOXアイテムを追加する際は、`MstItem` への追加と同時にこのテーブルにも追加する。
6. **ラインナップ更新の柔軟性**: グループのラインナップを変更したい場合は `MstFragmentBoxGroup` を編集するだけでよく、このテーブルを変更する必要はない。
