# ガチャ実装 マスタデータ設定手順書

## 概要

ガチャ（ガシャ）機能のマスタデータ設定手順書。Pickup（ピックアップ）・Festival（フェス）・PaidOnly（有償限定）・Ticket（チケット）・Medal（メダル）などのタイプに対応する。

- **report.md 対応セクション**: `### 2. ガチャ機能`

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | OprGacha | ガチャ本体 | 必須 |
| 2 | OprGachaI18n | ガチャ多言語名・説明 | 必須 |
| 3 | OprGachaPrize | 景品リスト（全排出ユニット） | 必須 |
| 4 | OprGachaUpper | 天井・上限設定 | 条件付き必須 |
| 5 | OprGachaUseResource | 消費リソース（引く際のコスト） | 必須 |
| 6 | OprGachaDisplayUnitI18n | ガチャ画面表示ユニット説明 | 必須 |

---

## 前提条件・依存関係

- **MstUnit の登録完了が前提**（`02_unit.md` を先に実施）
- OprGachaPrize の resource_id にはすべての排出ユニット ID（新規＋既存）を含める
- OprGachaUpper の upper_group = OprGacha.upper_group（同一グループでまとめる）

---

## report.md から読み取る情報チェックリスト

- [ ] ガチャ ID（例: `Pickup_you_001`）
- [ ] ガチャタイプ（Pickup/Festival/PaidOnly/Ticket/Medal）
- [ ] ガチャ名（日本語）
- [ ] 開催期間（start_at / end_at）
- [ ] 10連特典（multi_draw_count, multi_fixed_prize_count）
- [ ] 説明文（ピックアップキャラ情報、特典詳細）
- [ ] 景品数（全ユニット一覧）
- [ ] ガチャチケット対応有無
- [ ] 天井設定（上限回数と天井種類）
- [ ] ピックアップキャラ（pickup=1 にするユニット）

---

## テーブル別設定手順

### OprGacha（ガチャ本体）

**ガチャタイプ一覧**

| gacha_type | 説明 | 代表例 |
|-----------|------|-------|
| Pickup | ピックアップガチャ | `Pickup_you_001` |
| Festival | フェスガチャ | `Fest_osh_001` |
| PaidOnly | 有償ダイヤ限定 | `UR_newyear_001` |
| Ticket | チケットガチャ | `UR_newyear_Ticket_001` |
| Medal | メダルガチャ | `gasho_001` |

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{gacha_type}_{series_id}_{連番3桁}` | `Pickup_you_001` |
| gacha_type | ガチャ種別（上表参照） | `Pickup` |
| upper_group | 天井グループ（id と同じ） | `Pickup_you_001` |
| enable_ad_play | 広告視聴プレイ（NULL=なし） | `NULL` |
| multi_draw_count | 10連まとめ引き数 | `10` |
| multi_fixed_prize_count | 10連確定景品数（SR以上1体確定=1） | `1` |
| daily_play_limit_count | 日次上限（NULL=無制限） | `__NULL__` |
| total_play_limit_count | 総計上限（NULL=無制限） | `__NULL__` |
| daily_ad_limit_count | 広告日次上限 | `0` |
| prize_group_id | 景品グループ ID（id と同じ） | `Pickup_you_001` |
| fixed_prize_group_id | 確定景品グループ ID | `fixd_Pickup_you_001` |
| appearance_condition | 表示条件（通常 `Always`） | `Always` |
| unlock_condition_type | アンロック条件（通常 `None`） | `None` |
| start_at | 開始日時（UTC） | `2026-02-02 15:00:00` |
| end_at | 終了日時（UTC） | `2026-03-02 10:59:59` |
| display_information_id | 表示情報 UUID | （UUIDv4 を新規生成） |
| gacha_priority | 表示優先度（数字が大きいほど上位） | `68` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, gacha_type, multi_draw_count, multi_fixed_prize_count,
       prize_group_id, fixed_prize_group_id, start_at, end_at, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/OprGacha.csv');
```

---

### OprGachaI18n（ガチャ多言語名・説明）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{opr_gacha_id}_{language}` | `Pickup_you_001_ja` |
| opr_gacha_id | 対応する OprGacha.id | `Pickup_you_001` |
| language | 言語コード | `ja` |
| name | ガチャ表示名 | `幼稚園WARS いいジャン祭ピックアップガシャ` |
| description | ガチャ説明文（`\n` で改行） | `「元殺し屋の新人教諭 リタ」と\n「ルーク」の出現率UP中!` |
| pickup_upper_description | ピックアップ天井説明 | `ピックアップURキャラ1体確定!` |
| fixed_prize_description | 確定景品説明 | `SR以上1体確定` |
| banner_url | バナー画像キー | `you_00001` |
| logo_asset_key | ロゴアセットキー | `pickup_00001` |
| gacha_background_color | 背景色（Yellow/Blue/Red/Green） | `Yellow` |
| gacha_banner_size | バナーサイズ（SizeL/SizeM/SizeS） | `SizeL` |

**過去データ参照クエリ**

```duckdb
SELECT id, opr_gacha_id, name, description, pickup_upper_description,
       fixed_prize_description, gacha_background_color, gacha_banner_size
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/OprGachaI18n.csv');
```

---

### OprGachaPrize（景品リスト）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{group_id}_{連番}` | `Pickup_you_001_1` |
| group_id | OprGacha.prize_group_id | `Pickup_you_001` |
| resource_type | 景品種別（通常 `Unit`） | `Unit` |
| resource_id | ユニット ID | `chara_you_00001` |
| resource_amount | 個数（通常 `1`） | `1` |
| weight | 排出重み（合計10000が基準） | `702` |
| pickup | ピックアップ表示（0 or 1） | `1` |
| release_key | 今回のリリースキー | `202602015` |

**よくある weight 設定パターン（UR ピックアップ）**

| レアリティ | weight 目安 |
|-----------|-----------|
| UR ピックアップ | 702 |
| SSR（既存各） | 162 |
| SR（既存各） | 100前後 |

**過去データ参照クエリ**

```duckdb
SELECT id, group_id, resource_type, resource_id, resource_amount, weight, pickup
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/OprGachaPrize.csv')
WHERE group_id = 'Pickup_you_001'
ORDER BY pickup DESC, weight DESC;
```

---

### OprGachaUpper（天井・上限設定）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `20` |
| upper_group | OprGacha.upper_group と同値 | `Pickup_you_001` |
| upper_type | 天井種別（Pickup/MaxRarity/...） | `Pickup` |
| count | 天井回数 | `100` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, upper_group, upper_type, count, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/OprGachaUpper.csv');
```

---

### OprGachaUseResource（消費リソース）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `71` |
| opr_gacha_id | 対応する OprGacha.id | `Pickup_you_001` |
| cost_type | コスト種別（Item/Diamond/...） | `Item` |
| cost_id | コストアイテム ID（Diamond の場合 NULL） | `ticket_glo_00003` |
| cost_num | 消費数 | `1` |
| draw_count | この消費で引ける回数 | `1` |
| cost_priority | 優先度（低い番号が優先） | `2` |
| release_key | 今回のリリースキー | `202602015` |

**標準コスト設定パターン**

| cost_type | cost_id | cost_num | draw_count | 説明 |
|-----------|---------|----------|-----------|------|
| Item | ticket_glo_00003 | 1 | 1 | ガチャチケット単発 |
| Diamond | NULL | 150 | 1 | ダイヤ単発 |
| Diamond | NULL | 1500 | 10 | ダイヤ10連 |

**過去データ参照クエリ**

```duckdb
SELECT id, opr_gacha_id, cost_type, cost_id, cost_num, draw_count, cost_priority
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/OprGachaUseResource.csv')
ORDER BY opr_gacha_id, cost_priority;
```

---

### OprGachaDisplayUnitI18n（ガチャ画面表示ユニット説明）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | 連番（最終 ID + 1 から） | `81` |
| opr_gacha_id | 対応する OprGacha.id | `Pickup_you_001` |
| mst_unit_id | 表示するユニット ID | `chara_you_00001` |
| language | 言語コード | `ja` |
| sort_order | 表示順 | `1` |
| description | ユニット紹介説明文（`\n` で改行） | `必殺ワザで\n連続攻撃する中距離\nアタックキャラ!\n緑属性に大ダメージ!` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, opr_gacha_id, mst_unit_id, language, sort_order, description
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/OprGachaDisplayUnitI18n.csv')
ORDER BY opr_gacha_id, sort_order;
```

---

## 検証方法

- OprGachaPrize の resource_id（Unit）→ MstUnit.id が存在するか
- OprGachaPrize の weight 合計が期待値と一致するか
- OprGachaUpper の upper_group → OprGacha.upper_group が存在するか
- OprGachaUseResource の cost_type=Item の場合、cost_id → MstItem.id が存在するか
- display_information_id が有効な UUID 形式か

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`, `masterdata-csv-validator`
- 過去リリース参照:
  - `domain/raw-data/masterdata/released/202602015/tables/OprGacha.csv`
  - `domain/raw-data/masterdata/released/202512020/tables/OprGacha.csv`（Fest/PaidOnly/Ticket/Medal 例あり）
