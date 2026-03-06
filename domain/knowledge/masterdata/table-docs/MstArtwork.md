# MstArtwork 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtwork.csv`

> i18n CSVパス: `projects/glow-masterdata/MstArtworkI18n.csv`

---

## 概要

ゲーム内の「原画」を管理するテーブル。原画とは、特定の作品（シリーズ）に紐付いたイラストカードのことで、収集・展示・効果付与などのコンテンツに使用される。

各原画は `mst_series` テーブルで定義された作品シリーズに属し、レアリティ（N〜UR）で価値が分類される。完成した原画をゲートに装備することでゲート（拠点）のHPを増加させる効果もある。名前・説明文は `mst_artworks_i18n` テーブルで多言語管理される。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。原画を一意に識別するID |
| mst_series_id | varchar(255) | YES | 紐付く作品シリーズの `mst_series.id` |
| outpost_additional_hp | bigint unsigned | YES | 原画完成時にゲート（拠点）に加算するHP |
| asset_key | varchar(255) | YES | 原画画像のアセットキー |
| sort_order | int unsigned | YES | 一覧表示時のソート順 |
| rarity | enum | YES | レアリティ（`N` / `R` / `SR` / `SSR` / `UR`） |
| release_key | bigint | YES | リリースキー |

### MstArtworkI18n カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| release_key | bigint | YES | リリースキー |
| id | varchar(255) | YES | UUID。命名規則: `{mst_artwork_id}_{language}` |
| mst_artwork_id | varchar(255) | YES | 参照先の `mst_artworks.id` |
| language | enum('ja') | YES | 言語設定。現在は `ja`（日本語）のみ |
| name | varchar(40) | YES | 原画の表示名称（最大40文字） |
| description | varchar(255) | YES | 原画の説明文（最大255文字。改行は `\n` で表現） |

---

## rarity（レアリティ）

| 値 | 説明 | 位置づけ |
|----|------|---------|
| `N` | ノーマル | 最低レアリティ |
| `R` | レア | 低レアリティ |
| `SR` | スーパーレア | 中レアリティ |
| `SSR` | スーパースーパーレア | 高レアリティ |
| `UR` | ウルトラレア | 最高レアリティ |

---

## 命名規則 / IDの生成ルール

- メインテーブル `id`: `artwork_{シリーズ略称}_{連番（4桁）}`（例: `artwork_spy_0001`、`artwork_gom_0002`）
- i18nテーブル `id`: `{mst_artwork_id}_{language}`（例: `artwork_spy_0001_ja`）
- `asset_key`: `{シリーズ略称}_{連番（4桁）}`（例: `spy_0001`）

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_series` | 多対1 | 作品シリーズ。`mst_series_id` で参照 |
| `mst_artworks_i18n` | 1対多 | 原画名・説明文の多言語管理 |
| `mst_artwork_effects` | 1対多 | 原画の効果定義 |
| `mst_artwork_fragments` | 1対多 | 原画を構成する「かけら」の定義 |
| `mst_artwork_acquisition_routes` | 1対多 | 原画の入手経路 |
| `mst_artwork_grade_ups` | 1対多 | 原画のグレードアップ設定 |

---

## 実データ例

### パターン1: Rレアリティの原画（SPY×FAMILYシリーズ）

```
ENABLE: e
id: artwork_spy_0001
mst_series_id: spy
outpost_additional_hp: 100
asset_key: spy_0001
sort_order: 01
rarity: R
release_key: 202509010
```

```
ENABLE: e
release_key: 202509010
id: artwork_spy_0001_ja
mst_artwork_id: artwork_spy_0001
language: ja
name: 秘密の家族
description: 父はスパイ、母は殺し屋、娘は超能力者！？\n全員が秘密を抱えた家族生活が始まる！
```

### パターン2: SSRレアリティの原画

```
ENABLE: e
id: artwork_spy_0003
mst_series_id: spy
outpost_additional_hp: 100
asset_key: spy_0003
sort_order: 03
rarity: SSR
release_key: 202509010
```

```
ENABLE: e
release_key: 202509010
id: artwork_spy_0003_ja
mst_artwork_id: artwork_spy_0003
language: ja
name: 明るい未来に！！
description: イーデンでの面接試験の後、\n落ち込むロイドを励ますために\nお茶で乾杯！
```

---

## 設定時のポイント

1. **mst_series_id との整合性**: 設定する `mst_series_id` が `mst_series` テーブルに存在することを確認する。存在しないシリーズIDを参照するとデータ不整合が発生する。
2. **outpost_additional_hp の設定**: 現状は全原画共通で `100` に設定されている。レアリティや作品によって異なる値を設定することも可能。
3. **sort_order の管理**: 同一シリーズ内での原画の表示順を `sort_order` で制御する。通常は `01`、`02`... と連番で設定する。
4. **asset_key の一意性**: 異なる原画に同じアセットキーを設定しないよう注意する。
5. **description の改行**: 説明文内の改行は `\n` で表現する。
6. **i18nとのセット作成**: 原画を新規追加する際は、メインテーブルと同時に i18n テーブルのレコードも必ず作成する。
7. **レアリティとかけら数の整合性**: 高レアリティの原画は通常より多くの「かけら」を必要とする設計が多い。`mst_artwork_fragments` との整合性を確認する。
