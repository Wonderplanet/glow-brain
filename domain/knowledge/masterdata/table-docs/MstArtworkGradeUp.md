# MstArtworkGradeUp 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtworkGradeUp.csv`

---

## 概要

原画のグレードアップ定義を管理するテーブル。原画はグレードレベル1（初期）から5（最大）までレベルアップすることで効果が強化される仕組みで、このテーブルでグレードレベル2〜5の定義を行う。

各グレードアップレコードはシリーズ（`mst_series_id`）とレアリティの組み合わせで管理され、特定の原画（`mst_artwork_id`）への紐付けはオプション（NULL可）。`mst_artwork_effects` テーブルの `grade_level1_value` 〜 `grade_level5_value` と組み合わせることで、グレードに応じた効果値が決まる。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。グレードアップ定義を一意に識別するID |
| rarity | enum | YES | グレードアップ対象のレアリティ（`N` / `R` / `SR` / `SSR` / `UR`） |
| grade_level | int | YES | グレードレベル（2〜5）。このグレードに達した際に参照される |
| mst_series_id | varchar(255) | YES | 対象作品シリーズの `mst_series.id` |
| mst_artwork_id | varchar(255) | NO | 特定原画のID（NULL の場合はシリーズ全体に適用） |
| release_key | bigint | YES | リリースキー |

---

## rarity × grade_level の組み合わせ

グレードレベルは2〜5の範囲で、レアリティごとに4段階のグレードアップが定義される:

| rarity | grade_level |
|--------|------------|
| `R` | 2, 3, 4, 5 |
| `SR` | 2, 3, 4, 5 |
| `SSR` | 2, 3, 4, 5 |
| `UR` | 2, 3, 4, 5 |

グレードレベル1はデフォルト（初期状態）のため、このテーブルには登録されない。

---

## 命名規則 / IDの生成ルール

- `id`: `{シリーズ略称}_{連番（2桁）}`（例: `spy_01`、`spy_05`、`aka_09`）
- 連番はシリーズ内でレアリティ昇順 × グレードレベル昇順の順で振る

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_series` | 多対1 | 対象の作品シリーズ。`mst_series_id` で参照 |
| `mst_artworks` | 多対1 | 特定の原画を指定する場合。`mst_artwork_id` で参照（NULL可） |
| `mst_artwork_effects` | 間接参照 | グレードレベルに対応する効果値を `grade_level{N}_value` から参照 |

---

## 実データ例

### パターン1: シリーズ全体へのグレードアップ定義（Rレアリティ、グレード2〜5）

```
ENABLE: e
id: spy_01
rarity: R
grade_level: 2
mst_series_id: spy
mst_artwork_id: （空文字 = NULL）
release_key: 202603020

ENABLE: e
id: spy_02
rarity: R
grade_level: 3
mst_series_id: spy
mst_artwork_id: （空文字 = NULL）
release_key: 202603020

ENABLE: e
id: spy_03
rarity: R
grade_level: 4
mst_series_id: spy
mst_artwork_id: （空文字 = NULL）
release_key: 202603020

ENABLE: e
id: spy_04
rarity: R
grade_level: 5
mst_series_id: spy
mst_artwork_id: （空文字 = NULL）
release_key: 202603020
```

### パターン2: SRレアリティのグレードアップ定義

```
ENABLE: e
id: spy_05
rarity: SR
grade_level: 2
mst_series_id: spy
mst_artwork_id: （空文字 = NULL）
release_key: 202603020

ENABLE: e
id: spy_06
rarity: SR
grade_level: 3
mst_series_id: spy
mst_artwork_id: （空文字 = NULL）
release_key: 202603020
```

---

## 設定時のポイント

1. **グレードレベル2〜5のセット作成**: グレードレベル1はデフォルトのため登録不要。新しいシリーズを追加する際は、各レアリティ（R/SR/SSR/UR）に対してグレードレベル2〜5の計16レコードを作成する。
2. **mst_artwork_id はオプション**: 通常はシリーズ全体（`mst_artwork_id = NULL`）に対して設定する。特定の原画にのみ異なるグレードアップ定義を適用したい場合のみ `mst_artwork_id` を設定する。
3. **rarity の網羅**: 1シリーズ内でN/R/SR/SSR/URのレアリティが混在する場合、存在するレアリティ分のグレードアップ定義を作成する。（N レアリティは存在しない場合が多い）
4. **id の連番管理**: シリーズ略称 + 連番で一意のIDを作成する。別シリーズとのID衝突がないよう、シリーズ略称を必ずプレフィックスとして使用する。
5. **release_key の統一**: 同一シリーズの原画リリース時に合わせて release_key を設定する。
6. **mst_artwork_effects との整合性**: このテーブルはグレードアップの「存在」を定義するものであり、実際の効果値は `mst_artwork_effects` の `grade_level{N}_value` で管理される。グレードアップを追加した場合は `mst_artwork_effects` 側の設定も確認する。
