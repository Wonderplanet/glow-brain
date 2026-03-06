# アートワーク・フラグメント マスタデータ設定手順書

## 概要

イベントで解放されるアートワーク（原画）とそのフラグメント（かけら）の設定手順書。各フラグメントのドロップ設定・位置設定を含む。

- **report.md 対応セクション**: `機能別データ詳細 > アートワーク`

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | MstArtwork | アートワーク定義 | 必須 |
| 2 | MstArtworkI18n | アートワーク多言語名・説明 | 必須 |
| 3 | MstArtworkFragment | フラグメント定義 | 必須 |
| 4 | MstArtworkFragmentI18n | フラグメント多言語名 | 必須 |
| 5 | MstArtworkFragmentPosition | フラグメント位置設定 | 必須 |
| 6 | MstArtworkAcquisitionRoute | アートワーク入手ルート | 任意 |

---

## 前提条件・依存関係

- **MstSeries の登録完了が前提**（`01_event.md` を先に実施）
- フラグメントのドロップ設定は MstStage.mst_artwork_fragment_drop_group_id で参照（`04_quest-stage.md` 参照）
- アートワーク1枚につきフラグメントは通常 16 個（4×4 グリッド、position 1〜16）

---

## report.md から読み取る情報チェックリスト

- [ ] アートワーク数・名前
- [ ] 各アートワークの説明文
- [ ] フラグメント数（通常 アートワーク数 × 16）
- [ ] 各フラグメントの drop_group_id（どのステージからドロップするか）
- [ ] フラグメントのレアリティ（通常 SSR）

---

## テーブル別設定手順

### MstArtwork（アートワーク定義）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `artwork_event_{series_id}_{連番4桁}` | `artwork_event_you_0001` |
| mst_series_id | シリーズ ID | `you` |
| outpost_additional_hp | 前哨戦への HP 付与量（通常 100） | `100` |
| asset_key | アセットキー | `event_you_0001` |
| sort_order | 表示順（文字列で 2 桁） | `01` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, mst_series_id, outpost_additional_hp, asset_key, sort_order, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstArtwork.csv')
ORDER BY sort_order;
```

---

### MstArtworkI18n（アートワーク多言語名・説明）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{mst_artwork_id}_{language}` | `artwork_event_you_0001_ja` |
| mst_artwork_id | 対応する MstArtwork.id | `artwork_event_you_0001` |
| language | 言語コード | `ja` |
| name | アートワーク名 | `これで貸しはチャラだ` |
| description | アートワーク説明文 | `攫われたダグを助けにきたリタ。...` |

---

### MstArtworkFragment（フラグメント定義）

アートワーク1枚に対して 16 個のフラグメントを登録する（4×4 グリッドの 1〜16 位置に対応）。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `artwork_fragment_event_{series_id}_{連番5桁}` | `artwork_fragment_event_you_00001` |
| mst_artwork_id | 対応する MstArtwork.id | `artwork_event_you_0001` |
| drop_group_id | ドロップグループ ID（MstStage で参照） | `event_you_a_0001` |
| drop_percentage | ドロップ率（%） | `100` |
| rarity | レアリティ（通常 SSR） | `SSR` |
| asset_num | フラグメント内アセット番号（position と対応） | `7` |

**よくある設定パターン**
- アートワーク1枚 = 16 フラグメント（position 1〜16 を網羅）
- drop_group_id は対応するステージグループ（例: `event_you_a_0001` = アートワーク0001 グループ1）
- asset_num は position と同値（フラグメントの位置番号）

**過去データ参照クエリ**

```duckdb
SELECT id, mst_artwork_id, drop_group_id, drop_percentage, rarity, asset_num
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstArtworkFragment.csv')
ORDER BY mst_artwork_id, asset_num;
```

---

### MstArtworkFragmentI18n（フラグメント多言語名）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{mst_artwork_fragment_id}_{language}` | `artwork_fragment_event_you_00001_ja` |
| mst_artwork_fragment_id | 対応する MstArtworkFragment.id | `artwork_fragment_event_you_00001` |
| language | 言語コード | `ja` |
| name | フラグメント名（`原画のかけら{position番号}`） | `原画のかけら7` |

---

### MstArtworkFragmentPosition（フラグメント位置設定）

各フラグメントのグリッド上の位置を定義する。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | MstArtworkFragment.id と同じ | `artwork_fragment_event_you_00001` |
| mst_artwork_fragment_id | 対応する MstArtworkFragment.id | `artwork_fragment_event_you_00001` |
| position | グリッド位置（1〜16） | `7` |

**重要な注意点**: position と MstArtworkFragment.asset_num は同値にすること。

**全16フラグメントの位置確認クエリ**

```duckdb
SELECT f.id, f.mst_artwork_id, p.position, f.asset_num
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstArtworkFragment.csv') f
JOIN read_csv('domain/raw-data/masterdata/released/202602015/tables/MstArtworkFragmentPosition.csv') p
  ON f.id = p.mst_artwork_fragment_id
ORDER BY f.mst_artwork_id, p.position;
```

---

## 検証方法

- MstArtworkFragment の position（= asset_num）が 1〜16 を網羅しているか（各アートワークにつき）
- MstArtworkFragment.mst_artwork_id → MstArtwork.id が存在するか
- MstStage.mst_artwork_fragment_drop_group_id → MstArtworkFragment.drop_group_id が存在するか
- MstArtworkFragmentPosition.position と MstArtworkFragment.asset_num が一致するか

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`, `masterdata-csv-validator`
- 過去リリース: `domain/raw-data/masterdata/released/202602015/tables/`
