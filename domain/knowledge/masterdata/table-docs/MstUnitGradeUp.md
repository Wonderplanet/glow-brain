# MstUnitGradeUp 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitGradeUp.csv`

---

## 概要

キャラクター（ユニット）のグレードアップに必要なかけら数を定義するテーブル。
ユニットラベル（レアリティ）とグレードレベルの組み合わせごとに、そのグレードへアップするために消費するかけらの個数を設定する。
グレードレベル1は初期状態のため本テーブルには存在せず、2〜5の4段階分が登録される。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID（CSVでは連番整数） |
| unit_label | enum | NOT NULL | - | ユニットラベル（レアリティ種別） |
| grade_level | int unsigned | NOT NULL | - | グレードアップ後のグレードレベル（2〜5） |
| require_amount | int unsigned | NOT NULL | - | グレードアップに必要なかけら数 |
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
グレードレベル2から始まるため、各ラベルブロック内は4件（グレード2〜5）ずつ登録される。
例: DropR が 1〜4（グレード2〜5）、DropSR が 10〜13 ...

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| mst_unit_grade_coefficients | unit_label | グレードアップ後のステータス係数 |
| mst_units | unit_label | ユニット本体のレアリティ情報 |

---

## 実データ例（CSVから取得）

### パターン1: DropR（ドロップRレアリティ）のグレードアップ必要数

| id | unit_label | grade_level | require_amount | release_key |
|---|---|---|---|---|
| 1 | DropR | 2 | 50 | 202509010 |
| 2 | DropR | 3 | 50 | 202509010 |
| 3 | DropR | 4 | 50 | 202509010 |
| 4 | DropR | 5 | 50 | 202509010 |

### パターン2: DropSSR（ドロップSSRレアリティ）のグレードアップ必要数

| id | unit_label | grade_level | require_amount | release_key |
|---|---|---|---|---|
| 19 | DropSSR | 2 | 50 | 202509010 |
| 20 | DropSSR | 3 | 50 | 202509010 |
| 21 | DropSSR | 4 | 50 | 202509010 |
| 22 | DropSSR | 5 | 50 | 202509010 |

---

## 設定時のポイント

1. **グレードレベル1は登録不要**: グレード1は初期状態（アップグレードなし）のため、本テーブルには grade_level=2 から登録する。
2. **unit_label と grade_level の組み合わせはユニーク**: DBスキーマには `uk_unit_label_grade_level` ユニーク制約があるため、重複登録するとエラーになる。
3. **全ラベル × グレード2〜5分のレコードが必要**: 新しい unit_label を追加した場合は4件のレコードを必ず追加する。
4. **現行データではすべてのラベルで require_amount=50 で統一**: レアリティに関わらず同一のかけら数を要求している。
5. **mst_unit_grade_coefficients と整合性を保つ**: グレードアップの効果（係数）はmst_unit_grade_coefficientsで定義するため、同じラベル・グレードの組み合わせが両テーブルに存在することを確認する。
6. **新しいレアリティ追加時は両テーブルを同時に更新する**: グレードアップ設定と係数設定はセットで管理する。
7. **release_key はリリース管理に使用**: 新しい unit_label 追加時は適切なリリースキーを設定する。
