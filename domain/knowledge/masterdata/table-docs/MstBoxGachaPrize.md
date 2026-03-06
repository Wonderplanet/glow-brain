# MstBoxGachaPrize 詳細説明

> CSVパス: `projects/glow-masterdata/MstBoxGachaPrize.csv`

---

## 概要

ボックスガチャ（いいジャンくじ）の各箱グループに含まれる景品を管理するマスタテーブル。
各景品は1レコードで表され、景品タイプ・報酬ID・数量・在庫数・ピックアップフラグを設定する。
同一の `mst_box_gacha_group_id` に複数景品レコードを作成することで、1つの箱に複数種類の景品を設定できる。

クライアントクラス: `MstBoxGachaPrizeData.cs`

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | 景品ID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| mst_box_gacha_group_id | varchar(255) | YES | 所属するボックスガチャグループID（`mst_box_gacha_groups.id`） |
| is_pickup | tinyint | YES | ピックアップ対象フラグ（1=ピックアップ, 0=通常, デフォルト: 0） |
| resource_type | enum('Item','Artwork','FreeDiamond','Coin','Unit','Emblem') | NO | 報酬タイプ（NULL可） |
| resource_id | varchar(255) | NO | 報酬リソースID（NULL可）。タイプによっては不要 |
| resource_amount | int unsigned | YES | 報酬数量（デフォルト: 1） |
| stock | int unsigned | YES | 景品の在庫数（デフォルト: 1） |

---

## ResourceType（resource_type enumの値）

| 値 | 説明 | resource_id |
|---|---|---|
| Item | アイテム | `mst_items.id` |
| Artwork | 原画 | `mst_artworks.id` |
| FreeDiamond | 無償ダイヤ | 不要（NULL） |
| Coin | コイン | 不要（NULL） |
| Unit | ユニット | `mst_units.id` |
| Emblem | エンブレム | `mst_emblems.id` |

---

## 命名規則 / IDの生成ルール

IDは `{mst_box_gacha_group_id}_prize_{連番3桁}` の形式が一般的。

例:
- `box_gacha_kim_01_prize_01_001` → `box_gacha_kim_01_group_001` グループの景品1番目
- `box_gacha_test_prize_101` → テスト用グループの景品

---

## 他テーブルとの連携

| 参照先テーブル | カラム | 内容 |
|---|---|---|
| `mst_box_gacha_groups` | `mst_box_gacha_group_id` | 所属箱グループの参照 |
| `mst_items` | `resource_id` | 報酬アイテムの参照（resource_type=Item時） |
| `mst_artworks` | `resource_id` | 報酬原画の参照（resource_type=Artwork時） |
| `mst_units` | `resource_id` | 報酬ユニットの参照（resource_type=Unit時） |
| `mst_emblems` | `resource_id` | 報酬エンブレムの参照（resource_type=Emblem時） |

---

## 実データ例

**例1: テスト用景品（コイン）**

| id | mst_box_gacha_group_id | is_pickup | resource_type | resource_id | resource_amount | stock |
|---|---|---|---|---|---|---|
| box_gacha_test_prize_101 | box_gacha_test_group_1 | 1 | Coin | （空） | 1 | 1 |

**例2: 100カノ くじ箱1の景品一覧（抜粋）**

| id | mst_box_gacha_group_id | is_pickup | resource_type | resource_id | resource_amount | stock |
|---|---|---|---|---|---|---|
| box_gacha_kim_01_prize_01_001 | box_gacha_kim_01_group_001 | 1 | FreeDiamond | （空） | 40 | 5 |
| box_gacha_kim_01_prize_01_002 | box_gacha_kim_01_group_001 | 1 | Emblem | emblem_event_kim_00003 | 1 | 1 |
| box_gacha_kim_01_prize_01_003 | box_gacha_kim_01_group_001 | 1 | Item | piece_kim_00101 | 5 | 1 |
| box_gacha_kim_01_prize_01_009 | box_gacha_kim_01_group_001 | 1 | Item | ticket_glo_00002 | 1 | 1 |

---

## 設定時のポイント

1. `resource_type` が `FreeDiamond` または `Coin` の場合は `resource_id` は空文字またはNULLにする。
2. `resource_type` が `Item` / `Artwork` / `Unit` / `Emblem` の場合は対応テーブルに存在するIDを `resource_id` に設定する。
3. `stock` は各景品の在庫数で、この数だけ引かれると当該景品は消滅する（箱からなくなる）。
4. `is_pickup` は `1` でピックアップ表示される。ピックアップ表示はガチャ画面での視認性向上のため重要景品に設定する。
5. 1つのグループ内の全景品の `stock` 合計が箱の総引き数となるため、意図した総引き数になるよう設計する。
6. `resource_amount` は報酬の個数。FreeDiamondやCoinの場合は付与される数量を設定する。
7. 同一グループに同種の景品を異なる数量・在庫で設定することで、出現頻度を制御できる。
8. IDは `{グループID}_prize_{3桁連番}` の形式で命名し、グループID内で連番管理する。
