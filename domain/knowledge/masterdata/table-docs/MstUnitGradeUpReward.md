# MstUnitGradeUpReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitGradeUpReward.csv`

---

## 概要

キャラクター（ユニット）が特定のグレードレベルに達した際に獲得できる報酬を定義するテーブル。
ユニット個別（mst_unit_id）とグレードレベルの組み合わせで報酬の種類・ID・数量を設定する。
現行データでは、グレードレベル5（最大グレード）到達時にアートワーク（イラスト）が解放される設定になっている。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | ID（ユニットIDと同じ値を使用） |
| release_key | bigint | NOT NULL | 1 | リリースキー |
| mst_unit_id | varchar(255) | NOT NULL | - | 対象ユニットのID（mst_units.id） |
| grade_level | int unsigned | NOT NULL | - | 報酬獲得可能なグレードレベル |
| resource_type | enum | NOT NULL | - | 報酬タイプ |
| resource_id | varchar(255) | NULL | - | 報酬のID（resource_type に応じたID） |
| resource_amount | int unsigned | NOT NULL | - | 報酬の数量 |
| created_at | timestamp | NULL | - | 作成日時 |
| updated_at | timestamp | NULL | - | 更新日時 |

---

## ResourceType（resource_type の enum 値）

| 値 | 説明 |
|---|---|
| Artwork | アートワーク（キャラクターイラスト）の解放報酬 |

---

## 命名規則 / IDの生成ルール

`id` にはユニットIDと同じ値（例: `chara_spy_00001`）が使用される。
`resource_id` はアートワークIDの命名規則 `artwork_{mst_unit_id}` に従う。
例: ユニット `chara_spy_00001` の報酬IDは `artwork_chara_spy_00001`

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| mst_units | mst_unit_id | 報酬が付与されるユニットの参照 |
| mst_unit_grade_ups | unit_label + grade_level | グレードアップ条件の定義 |
| mst_unit_grade_coefficients | unit_label + grade_level | グレードアップ後のステータス係数 |

---

## 実データ例（CSVから取得）

### パターン1: chara_spy_00001（スパイキャラ）のグレードレベル5報酬

| id | mst_unit_id | grade_level | resource_type | resource_id | resource_amount | release_key |
|---|---|---|---|---|---|---|
| chara_spy_00001 | chara_spy_00001 | 5 | Artwork | artwork_chara_spy_00001 | 1 | 202603020 |

### パターン2: chara_dan_00002（ダンキャラ）のグレードレベル5報酬

| id | mst_unit_id | grade_level | resource_type | resource_id | resource_amount | release_key |
|---|---|---|---|---|---|---|
| chara_dan_00002 | chara_dan_00002 | 5 | Artwork | artwork_chara_dan_00002 | 1 | 202603020 |

---

## 設定時のポイント

1. **現行では全報酬がグレードレベル5に設定**: 最大グレード到達時にのみ報酬が解放される設計。他のグレードレベルへの設定も構造上は可能。
2. **id にはユニットIDを使用**: `id` フィールドはUUIDではなくユニットIDと同値を使用する慣習になっている。
3. **resource_id の命名規則を遵守する**: アートワークIDは `artwork_{mst_unit_id}` の形式で命名する。
4. **新しいユニット追加時は本テーブルへの追加も忘れずに**: グレードアップ報酬（アートワーク）の設定はユニット追加と同時に行う。
5. **resource_amount は基本的に1**: アートワークは1枚単位で解放されるため数量は1で設定する。
6. **インデックス `idx_mst_unit_id` による検索最適化**: mst_unit_id で検索することを前提にした設計になっている。
7. **release_key はリリースキャンペーンに合わせて設定**: 新キャラ追加のリリースキーと揃えること。
8. **resource_type が Artwork のみ**: 現行のenum定義は Artwork のみだが、将来的な拡張を意識した設計になっている。
