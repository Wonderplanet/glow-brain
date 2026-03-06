# MstDungeonCardGroup 詳細説明

> CSVパス: `projects/glow-masterdata/MstDungeonCardGroup.csv`（未作成・将来追加予定）

---

## 概要

`MstDungeonCardGroup` は**限界チャレンジの深度帯別カード候補グループを管理するテーブル**。

限界チャレンジでは敵を倒すとルーレットが回転してカードを獲得できる。どのカードがルーレット対象になるかは深度（進行フロア数）と取得シーン（ルーレット / 選択レア / 選択ボス）によって変わる。このテーブルでは「深度 N 以上の場合はこのカードグループから選ぶ」という範囲指定でカード出現候補を管理する。

2026年3月時点でCSVファイルは未作成。

### ゲームプレイへの影響

- **`min_depth`**: 適用開始深度。次のレコードの `min_depth` 未満まで適用される（範囲区間式の設計）
- **`card_type`**: カード取得シーン。ルーレット・レアブロック選択・ボスブロック選択の3種
- **`mst_dungeon_card_ids`**: 候補カードIDのカンマ区切りリスト。この中からランダムに選ばれてルーレット候補となる

### 関連テーブルとの構造図

```
MstDungeon（開催回）
  └─ id → MstDungeonCardGroup.mst_dungeon_id（1:N、深度帯別グループ）
                └─ card_type = Roulette       （ルーレットで出現するカード候補）
                └─ card_type = SelectionRare  （レアブロックで選択するカード候補）
                └─ card_type = SelectionBoss  （ボスブロックで選択するカード候補）
                └─ mst_dungeon_card_ids → MstDungeonCard.id（カンマ区切りリスト）
```

---

## 全カラム一覧

### mst_dungeon_card_groups カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。カード候補グループID |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_dungeon_id` | varchar(255) | 不可 | - | 参照先ダンジョンID（`mst_dungeons.id`） |
| `min_depth` | int unsigned | 不可 | - | 適用深度の下限値。次レコードの `min_depth` 未満まで適用範囲となる |
| `card_type` | enum | 不可 | - | カード種別。`Roulette` / `SelectionRare` / `SelectionBoss` の3種 |
| `mst_dungeon_card_ids` | text | 不可 | - | カードIDのカンマ区切りリスト（`mst_dungeon_cards.id`） |

**ユニーク制約**: `(mst_dungeon_id, card_type, min_depth)` の組み合わせが重複不可

---

## DungeonCardType（カード種別）

| 値 | 意味 | 取得シーン |
|----|------|----------|
| `Roulette` | ルーレットカード | 通常ブロッククリア時のルーレットで出現。ランダム選択 |
| `SelectionRare` | レアブロック選択カード | レアブロッククリア時に複数候補の中からプレイヤーが選択 |
| `SelectionBoss` | ボスブロック選択カード | ボスブロッククリア時に複数候補の中からプレイヤーが選択 |

---

## `min_depth` の範囲区間の考え方

このテーブルは「次のレコードの `min_depth` 未満まで適用」という区間設計を採用している。

```
例: card_type = Roulette の場合
  レコード1: min_depth = 1   → 深度 1〜4 が適用範囲
  レコード2: min_depth = 5   → 深度 5〜9 が適用範囲
  レコード3: min_depth = 10  → 深度 10〜 が適用範囲（最終行）
```

深度が深いほど強いカードが出現するよう設計するには、`min_depth` が大きいグループに高レアリティのカードIDを追加する。

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_dungeons` | `mst_dungeon_id` → `id` | 属する開催回 |
| `mst_dungeon_cards` | `mst_dungeon_card_ids`（カンマ区切り文字列） → `id` | 候補カードの定義 |

---

## 実データ例

> 2026年3月現在、`MstDungeonCardGroup.csv` は未作成のため実データは存在しない。
> 以下は想定されるデータ形式の例。

### パターン1: ルーレットカード候補（深度1〜4）

```
ENABLE: e
id: dungeon_00001_roulette_depth1
release_key: 202601010
mst_dungeon_id: dungeon_00001
min_depth: 1
card_type: Roulette
mst_dungeon_card_ids: dungeon_card_n_01,dungeon_card_n_02,dungeon_card_r_01
```

深度1〜4ではN・Rレアリティのカードのみが候補となる。

### パターン2: ルーレットカード候補（深度10以上）

```
ENABLE: e
id: dungeon_00001_roulette_depth10
release_key: 202601010
mst_dungeon_id: dungeon_00001
min_depth: 10
card_type: Roulette
mst_dungeon_card_ids: dungeon_card_r_01,dungeon_card_sr_01,dungeon_card_ssr_01
```

深度10以上ではSR・SSRカードも候補に加わり、より強力なカードが入手可能になる。

---

## 設定時のポイント

1. **同じ `(mst_dungeon_id, card_type)` の組み合わせで `min_depth` を変えることで深度帯別設定ができる**。深度が上がるにつれてより強力なカードが出現するよう設計することが基本。

2. **`mst_dungeon_card_ids` はカンマ区切りの文字列であることに注意**。スペースを入れない形式 `card_a,card_b,card_c` で設定し、全てのIDが `mst_dungeon_cards` テーブルに存在することを確認すること。

3. **各 `card_type` に対して少なくとも `min_depth = 1` のレコードが必要**。最低1つのグループがないとゲームがそのカード種別の処理を行えない。

4. **ユニーク制約 `(mst_dungeon_id, card_type, min_depth)` があるため同一の組み合わせは登録不可**。深度帯を変えたい場合は別の `min_depth` 値で新規レコードを作成する。

5. **`SelectionRare` と `SelectionBoss` は候補数を適切に設定する**。プレイヤーがカードを選択する際に表示される候補数はクライアント側で制御するが、候補リストが少なすぎると毎回同じカードしか表示されなくなる。

6. **ダンジョン開催回（`MstDungeon`）を追加したら、対応するカードグループも必ず設定する**。グループが存在しない場合はカード取得処理が機能しない。
