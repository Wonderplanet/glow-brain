# MstArtworkFragment 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtworkFragment.csv`

> i18n CSVパス: `projects/glow-masterdata/MstArtworkFragmentI18n.csv`

---

## 概要

原画を構成する「かけら」を管理するテーブル。原画は複数のかけらを集めることで完成する仕組みで、各かけらはステージからのドロップやイベント報酬などで入手できる。

各かけらには表示位置番号（1〜16のマス目）が `mst_artwork_fragment_positions` で定義され、かけらの名前は `mst_artwork_fragments_i18n` で管理される。かけらのレアリティはアセット選択に使用され、ドロップ設定（グループとドロップ率）でステージドロップを制御する。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| release_key | bigint | YES | リリースキー |
| id | varchar(255) | YES | UUID。かけらを一意に識別するID |
| mst_artwork_id | varchar(255) | YES | 紐付く原画の `mst_artworks.id` |
| drop_group_id | varchar(255) | NO | ステージのドロップ単位ID（非ドロップはNULL） |
| drop_percentage | smallint unsigned | NO | ドロップ率（非ドロップはNULL） |
| rarity | enum | YES | レアリティ（`N` / `R` / `SR` / `SSR` / `UR`） |
| asset_num | int | YES | アセット番号（かけらのデザイン番号） |

### MstArtworkFragmentI18n カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| release_key | bigint | YES | リリースキー |
| id | varchar(255) | YES | UUID。命名規則: `{mst_artwork_fragment_id}_{language}` |
| mst_artwork_fragment_id | varchar(255) | YES | 参照先の `mst_artwork_fragments.id` |
| language | enum('ja') | YES | 言語設定。現在は `ja`（日本語）のみ |
| name | varchar(15) | YES | かけらの表示名称（最大15文字） |

---

## rarity（レアリティ）

| 値 | 説明 |
|----|------|
| `N` | ノーマル |
| `R` | レア |
| `SR` | スーパーレア |
| `SSR` | スーパースーパーレア |
| `UR` | ウルトラレア |

親の原画（`mst_artworks`）のレアリティと同じ値を設定するのが基本。

---

## 命名規則 / IDの生成ルール

- メインテーブル `id`: `artwork_fragment_{シリーズ略称}_{連番（5桁）}`（例: `artwork_fragment_aka_00101`）
  - 連番の下2桁は通し番号（01〜）
  - 連番の上3桁は基本的に親の原画番号に対応
- i18nテーブル `id`: `{mst_artwork_fragment_id}_{language}`（例: `artwork_fragment_aka_00101_ja`）
- かけらの `name`: `原画のかけら{asset_num}`（例: `原画のかけら16`、`原画のかけら8`）

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_artworks` | 多対1 | 親の原画。`mst_artwork_id` で参照 |
| `mst_artwork_fragments_i18n` | 1対多 | かけら名の多言語管理 |
| `mst_artwork_fragment_positions` | 1対1 | かけらの表示位置（1〜16）の定義 |

---

## 実データ例

### パターン1: ドロップあり（SSRかけら）

```
ENABLE: e
release_key: 202509010
id: artwork_fragment_aka_00101
mst_artwork_id: artwork_aka_0001
drop_group_id: aka_a_0001
drop_percentage: 30
rarity: SSR
asset_num: 16
```

```
ENABLE: e
release_key: 202509010
id: artwork_fragment_aka_00101_ja
mst_artwork_fragment_id: artwork_fragment_aka_00101
language: ja
name: 原画のかけら16
```

### パターン2: 別のかけら番号（同じ原画の別位置）

```
ENABLE: e
release_key: 202509010
id: artwork_fragment_aka_00102
mst_artwork_id: artwork_aka_0001
drop_group_id: aka_a_0001
drop_percentage: 30
rarity: SSR
asset_num: 11
```

```
ENABLE: e
release_key: 202509010
id: artwork_fragment_aka_00102_ja
mst_artwork_fragment_id: artwork_fragment_aka_00102
language: ja
name: 原画のかけら11
```

---

## 設定時のポイント

1. **1原画に対するかけら数**: 原画のレアリティによってかけらの必要数が異なる。実データでは1原画に対して複数の `mst_artwork_fragments` レコードを作成する。
2. **asset_num の意味**: かけらのデザイン（ピースの見た目）を示す番号。1〜16 のマス目番号に対応することが多い（`mst_artwork_fragment_positions` の `position` と関連）。
3. **drop_group_id の設定**: ステージからのドロップで入手するかけらには `drop_group_id` を設定する。イベント限定やショップ購入のみのかけらは NULL を設定する。
4. **drop_percentage の意味**: ドロップ時の確率（%）を設定する。同じ `drop_group_id` のかけらが複数ある場合、それぞれに同じ `drop_percentage` を設定するケースが多い（現状は30が多い）。
5. **rarity は親の原画と合わせる**: かけらのレアリティは通常、親の `mst_artworks.rarity` と同じ値を設定する。
6. **i18nとのセット作成**: かけらを新規追加する際は、メインテーブルと同時に i18n テーブルのレコードも必ず作成する。
7. **fragment positions との対応**: かけらを作成した後は `mst_artwork_fragment_positions` で表示位置（1〜16）も設定する。
