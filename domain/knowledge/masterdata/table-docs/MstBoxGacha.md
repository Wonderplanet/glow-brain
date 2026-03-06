# MstBoxGacha 詳細説明

> CSVパス: `projects/glow-masterdata/MstBoxGacha.csv`
> i18n CSVパス: `projects/glow-masterdata/MstBoxGachaI18n.csv`

---

## 概要

ボックスガチャ（いいジャンくじ）の基本設定を管理するマスタテーブル。
ボックスガチャとは、箱の中に入った景品を順番に引いていく形式のガチャで、景品には在庫が設定されており引ききると箱がリセットされる。
コスト・ループタイプ・表示用アセット・表示用ユニットなどの基本設定を定義する。

多言語対応の名称は `MstBoxGachaI18n` テーブルで管理する。

クライアントクラス: `MstBoxGachaData.cs` / `MstBoxGachaI18nData.cs`

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | ボックスガチャID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| mst_event_id | varchar(255) | YES | 開催イベントID（`mst_events.id`） |
| cost_id | varchar(255) | YES | 消費アイテムID（`mst_items.id`） |
| cost_num | int unsigned | YES | 1回の抽選に必要なコスト数（デフォルト: 1） |
| loop_type | enum('All','Last','First') | YES | 全景品を引き終えたときのループタイプ（デフォルト: Last） |
| asset_key | varchar(255) | YES | ガチャ表示用アセットキー |
| display_mst_unit_id1 | varchar(255) | YES | TOPページ表示用ユニットID1 |
| display_mst_unit_id2 | varchar(255) | YES | TOPページ表示用ユニットID2 |

### MstBoxGachaI18n カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| id | varchar(255) | YES | レコードID（主キー） |
| mst_box_gacha_id | varchar(255) | YES | 対応するボックスガチャID（`mst_box_gachas.id`） |
| language | varchar(10) | YES | 言語コード（例: `ja`） |
| name | varchar(255) | YES | ボックスガチャの表示名称 |

ユニークキー: `(mst_box_gacha_id, language)` の組み合わせで一意となる。

---

## LoopType（loop_type enumの値）

| 値 | 説明 |
|---|---|
| All | 全景品を引ききった後、全景品を再補充してループする |
| Last | 全景品を引ききった後、最後の箱（レベル）のみをループする |
| First | 全景品を引ききった後、最初の箱（レベル）から再スタートする |

---

## 命名規則 / IDの生成ルール

IDは `box_gacha_{イベント略称}_{連番2桁}` の形式が一般的。

例:
- `box_gacha_kim_01` → 100カノイベント（kim）の1番目のボックスガチャ
- `box_gacha_test` → テスト用データ

i18nテーブルのIDは `{mst_box_gacha_id}_{言語コード}` の形式。
例: `box_gacha_kim_01_ja`

---

## 他テーブルとの連携

| 参照先テーブル | カラム | 内容 |
|---|---|---|
| `mst_events` | `mst_event_id` | 開催イベントの参照 |
| `mst_items` | `cost_id` | 消費アイテムの参照 |
| `mst_units` | `display_mst_unit_id1`, `display_mst_unit_id2` | TOP表示用ユニットの参照 |

| 参照元テーブル | 用途 |
|---|---|
| `mst_box_gacha_groups` | ボックスガチャの箱グループ一覧 |
| `mst_box_gachas_i18n` | ボックスガチャの多言語名称 |

---

## 実データ例

**MstBoxGacha 例1: テスト用ボックスガチャ**

| id | mst_event_id | cost_id | cost_num | loop_type | asset_key | display_mst_unit_id1 | display_mst_unit_id2 |
|---|---|---|---|---|---|---|---|
| box_gacha_test | dummy_data | dummy_item | 5 | Last | glo_00001 | （空） | （空） |

**MstBoxGacha 例2: 100カノ いいジャンくじ**

| id | mst_event_id | cost_id | cost_num | loop_type | asset_key | display_mst_unit_id1 | display_mst_unit_id2 |
|---|---|---|---|---|---|---|---|
| box_gacha_kim_01 | event_kim_00001 | box_item_glo_00001 | 10 | Last | glo_00040 | chara_kim_00001 | chara_kim_00101 |

**MstBoxGachaI18n 例: 名称設定**

| id | mst_box_gacha_id | language | name |
|---|---|---|---|
| box_gacha_test_ja | box_gacha_test | ja | いいジャンくじ |
| box_gacha_kim_01_ja | box_gacha_kim_01 | ja | 100カノ いいジャンくじ券引換所 |

---

## 設定時のポイント

1. `mst_event_id` には `mst_events` に存在するイベントIDを設定し、ボックスガチャの開催期間はイベント期間に合わせる。
2. `cost_id` は消費チケットアイテムのID（`mst_items.id`）を設定する。専用チケットを用意することが多い。
3. `cost_num` は1回の抽選に必要なチケット枚数。通常10枚設定が多い。
4. `loop_type` は `Last`（最後の箱を繰り返す）が現在の主要な運用パターン。
5. `asset_key` はガチャ画面に表示するバナー・背景等のアセットキーを設定する。
6. `display_mst_unit_id1` / `display_mst_unit_id2` はTOPページのガチャ一覧に表示されるユニットIDで、空文字も許容される。
7. i18nレコードは必ず対応するガチャIDに対して言語コード（`ja` など）とセットで作成する。
8. i18nのID命名規則: `{mst_box_gacha_id}_{language}` の形式で統一する。
