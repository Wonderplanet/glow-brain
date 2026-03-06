# MstUnitGradeCoefficient 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitGradeCoefficient.csv`

---

## 概要

キャラクター（ユニット）のグレードアップ後のステータス係数を定義するテーブル。
ユニットラベル（レアリティ）とグレードレベルの組み合わせごとに、体力と攻撃力に適用される上昇係数（パーセントポイント相当の整数値）を設定する。
グレードレベル1（初期状態）の係数は 0 であり、グレードアップするほど係数が増加してステータスが強化される。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID（CSVでは連番整数） |
| unit_label | enum | NOT NULL | - | ユニットラベル（レアリティ種別） |
| grade_level | int unsigned | NOT NULL | - | グレードレベル（1〜5） |
| coefficient | int unsigned | NOT NULL | - | 体力・攻撃力に係る係数（加算値） |
| release_key | bigint | NOT NULL | 1 | リリースキー |

---

## UnitLabel（unit_label の enum 値）

| 値 | 説明 |
|---|---|
| DropR | ドロップ排出のRレアリティ |
| DropSR | ドロップ排出のSRレアリティ |
| DropSSR | ドロップ排出のSSRレアリティ |
| DropUR | ドロップ排出のURレアリティ |
| PremiumR | プレミアムガチャのRレアリティ |
| PremiumSR | プレミアムガチャのSRレアリティ |
| PremiumSSR | プレミアムガチャのSSRレアリティ |
| PremiumUR | プレミアムガチャのURレアリティ |
| FestivalUR | フェスティバルガチャのURレアリティ |

---

## 命名規則 / IDの生成ルール

CSVの `id` は連番整数で管理されており、ラベルごとに10刻みのブロックに分かれている。
例: DropR が 1〜10、DropSR が 11〜20、DropSSR が 21〜30、PremiumR が 31〜40 ...

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| mst_unit_grade_ups | unit_label | グレードアップに必要なかけら数の定義 |
| mst_units | unit_label | ユニット本体のレアリティ情報 |

---

## 実データ例（CSVから取得）

### パターン1: DropR（ドロップRレアリティ）のグレード係数

| id | grade_level | unit_label | coefficient | release_key |
|---|---|---|---|---|
| 1 | 1 | DropR | 0 | 202509010 |
| 2 | 2 | DropR | 3 | 202509010 |
| 3 | 3 | DropR | 5 | 202509010 |
| 4 | 4 | DropR | 8 | 202509010 |
| 5 | 5 | DropR | 10 | 202509010 |

### パターン2: FestivalUR（フェスティバルURレアリティ）のグレード係数

| id | grade_level | unit_label | coefficient | release_key |
|---|---|---|---|---|
| 71 | 1 | FestivalUR | 0 | 202509010 |
| 72 | 2 | FestivalUR | 18 | 202509010 |
| 73 | 3 | FestivalUR | 29 | 202509010 |
| 74 | 4 | FestivalUR | 34 | 202509010 |
| 75 | 5 | FestivalUR | 38 | 202509010 |

---

## 設定時のポイント

1. **グレードレベル1の係数は必ず0**: グレードレベル1は初期状態のため係数加算なし。すべてのラベルで grade_level=1 の coefficient=0 で統一する。
2. **レアリティが高いほど係数増加量が大きい**: DropR のグレード5係数が10に対し、FestivalUR は38と高い。レアリティに応じた格差を設計する。
3. **unit_label と grade_level の組み合わせで一意にはなっていない**: DBスキーマにユニークキーはないため、CSVのIDで管理。重複登録に注意する。
4. **全ラベル × 全グレード数分のレコードが必要**: 新しい unit_label を追加した場合は、対応する全グレードレベルのレコードを必ず追加する。
5. **グレードレベルの最大値は5**: 現在の設計では5段階。グレード数を変更する場合は本テーブルとmst_unit_grade_upsを同時に変更する。
6. **係数の単位はパーセントポイント相当の整数**: ゲーム内ではこの係数をステータス計算式に組み込んで使用する。
7. **release_key はリリース管理に使用**: 新しい unit_label 追加時は適切なリリースキーを設定する。
