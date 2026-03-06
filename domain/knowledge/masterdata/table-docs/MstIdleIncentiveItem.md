# MstIdleIncentiveItem 詳細説明

> CSVパス: `projects/glow-masterdata/MstIdleIncentiveItem.csv`

---

## 概要

`MstIdleIncentiveItem` は**探索（放置報酬）でドロップするアイテムの種類とベース獲得量をグループ単位で定義するテーブル**。`mst_idle_incentive_item_group_id` でグループ化され、1グループが「あるステージ進捗帯でドロップするアイテムセット」に対応する。

`MstIdleIncentiveReward` から `mst_idle_incentive_item_group_id` で参照され、ステージ進捗ごとのアイテムドロップ設定として機能する。

### ゲームプレイへの影響

- **mst_idle_incentive_item_group_id** で同一グループの複数アイテムをまとめて設定できる。1グループ内の全アイテムが探索報酬のドロップ候補になる。
- **mst_item_id** でドロップするアイテムを指定する。カラーメモリーなどのランクアップ素材が設定される。
- **base_amount** がN分ごとの基礎ドロップ量（小数可）。実際の獲得量はプレイヤーのユニット編成やボーナスで変動する可能性がある。`0.0` の場合は実質ドロップなし（ラインナップとしては登録されているが獲得量0）。

### 関連テーブルとの構造図

```
MstIdleIncentiveReward（ステージ別報酬設定）
  └─ mst_idle_incentive_item_group_id → MstIdleIncentiveItem.mst_idle_incentive_item_group_id（1:N）
         └─ mst_item_id → MstItem.id（ドロップアイテムの実体）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（整数連番） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_idle_incentive_item_group_id` | varchar(255) | 不可 | - | グループID。`MstIdleIncentiveReward` から参照される |
| `mst_item_id` | varchar(255) | 不可 | - | ドロップするアイテムのID（`mst_items.id`） |
| `base_amount` | decimal(10,4) | 不可 | - | N分ごとの基礎ドロップ量（小数4桁精度） |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | 整数連番（全テーブルで通し番号） | `1`, `2`, `395` |
| mst_idle_incentive_item_group_id | `idle_incentive_group_{番号}` | `idle_incentive_group_tutorial_1`, `idle_incentive_group_spy1` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_idle_incentive_rewards` | `mst_idle_incentive_item_group_id` → `mst_idle_incentive_item_group_id` | ステージ別報酬とのグループ参照（N:1） |
| `mst_items` | `mst_item_id` → `id` | ドロップアイテムの実体（N:1） |

---

## 実データ例

**パターン1: チュートリアルステージのアイテムグループ（低ドロップ量）**
```
ENABLE: e
id: 1
mst_idle_incentive_item_group_id: idle_incentive_group_tutorial_1
mst_item_id: memory_glo_00001
base_amount: 0.0
release_key: 202509010
```
```
ENABLE: e
id: 2
mst_idle_incentive_item_group_id: idle_incentive_group_tutorial_1
mst_item_id: memory_glo_00002
base_amount: 0.2
release_key: 202509010
```
- チュートリアル段階のグループ
- `memory_glo_00001`（グレーメモリー）はドロップ量0（実質ドロップなし）
- `memory_glo_00002`（レッドメモリー）は0.2/分のドロップ

**パターン2: 通常ステージのアイテムグループ**
```
ENABLE: e
id: 6
mst_idle_incentive_item_group_id: idle_incentive_group_spy1
mst_item_id: memory_glo_00001
base_amount: 0.0
release_key: 202509010
```
```
ENABLE: e
id: 7
mst_idle_incentive_item_group_id: idle_incentive_group_spy1
mst_item_id: memory_glo_00002
base_amount: 0.2
release_key: 202509010
```
- スパイ系ステージのアイテムグループ

---

## 設定時のポイント

1. **base_amount = 0.0 の意味**: ドロップ量0のアイテムをグループ内に含めるのは、将来的な拡張（ステージ進捗に応じたドロップ開放）や、全アイテムを共通のグループで管理するための設計。`0.0` のアイテムは実際にはドロップしない。
2. **グループごとの全アイテム設定**: 1グループにはカラーメモリー全種類（通常5種）を設定する慣習。ドロップしないアイテムも `base_amount = 0.0` で明示的に含める。
3. **id の整数連番**: 全レコードを通して連番で採番する。新規グループ追加時は現在の最大IDの次の値から採番する。
4. **mst_item_id の確認**: 設定するアイテムが `MstItem` に存在し、有効期間内であることを確認する。
5. **group_id の命名**: ステージやコンテンツが識別できる名前を使う。チュートリアルは `tutorial_N`、各ステージは `spy1`, `spy2` など関連するステージ名を使う慣習。
6. **大量データの管理**: 現在395件のレコードが存在する。ステージ数×アイテム数の組み合わせになるため、ステージが増えるとレコード数が増える。CSVの一括更新ツールやスクリプトでの管理が推奨される。
7. **decimal精度**: `base_amount` は `decimal(10,4)` 型で小数4桁まで設定可能。例: `0.2000`, `0.1234` 等。
