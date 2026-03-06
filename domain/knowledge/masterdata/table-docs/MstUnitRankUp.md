# MstUnitRankUp 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitRankUp.csv`

---

## 概要

キャラクター（ユニット）のランクアップ（レベル上限開放）に必要な素材と条件を定義するテーブル。
ユニットラベル（レアリティ）とランクの組み合わせごとに、必要なリミテッドメモリー数・要求レベル・メモリーフラグメント（SR/SSR/UR）の必要数を設定する。
ユニット個別の設定が必要な場合は mst_unit_specific_rank_ups を使用する。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID（CSVでは連番整数） |
| unit_label | enum | NOT NULL | - | ユニットラベル（レアリティ種別） |
| rank | int | NOT NULL | - | ランクアップ後のランク（1〜6） |
| amount | int | NOT NULL | - | リミテッドメモリーの必要数 |
| require_level | int | NOT NULL | - | ランクアップに必要なユニットの現在レベル |
| sr_memory_fragment_amount | int | NOT NULL | 0 | 初級メモリーフラグメントの必要数 |
| ssr_memory_fragment_amount | int | NOT NULL | 0 | 中級メモリーフラグメントの必要数 |
| ur_memory_fragment_amount | int | NOT NULL | 0 | 上級メモリーフラグメントの必要数 |
| release_key | int | NOT NULL | 1 | リリースキー |

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

CSVの `id` は連番整数で管理される。`unit_label` と `rank` の組み合わせが `uk_unit_label_rank` ユニーク制約により保護されている。

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| mst_unit_level_ups | unit_label + require_level | ランクアップに必要なレベル上限の参照 |
| mst_unit_rank_coefficients | rank | ランク別ステータス係数の参照 |
| mst_unit_specific_rank_ups | mst_unit_id + rank | ユニット個別のランクアップ設定（本テーブルを上書き） |
| mst_units | unit_label | ユニット本体のレアリティ情報 |

---

## 実データ例（CSVから取得）

### パターン1: DropR（ドロップRレアリティ）のランクアップ設定

| id | unit_label | rank | amount | require_level | sr_memory_fragment_amount | ssr_memory_fragment_amount | ur_memory_fragment_amount | release_key |
|---|---|---|---|---|---|---|---|---|
| 1 | DropR | 1 | 200 | 20 | 0 | 0 | 0 | 202509010 |
| 2 | DropR | 2 | 250 | 30 | 5 | 0 | 0 | 202509010 |
| 3 | DropR | 3 | 300 | 40 | 10 | 5 | 0 | 202509010 |
| 4 | DropR | 4 | 350 | 50 | 15 | 10 | 0 | 202509010 |
| 5 | DropR | 5 | 400 | 60 | 20 | 15 | 3 | 202509010 |
| 6 | DropR | 6 | 450 | 70 | 25 | 20 | 5 | 202509010 |

### パターン2: DropSR（ドロップSRレアリティ）のランクアップ設定（一部）

| id | unit_label | rank | amount | require_level | sr_memory_fragment_amount | release_key |
|---|---|---|---|---|---|---|
| 10 | DropSR | 1 | 200 | 20 | 0 | 202509010 |
| 11 | DropSR | 2 | 250 | 30 | 5 | 202509010 |
| 12 | DropSR | 3 | 300 | 40 | 10 | 202509010 |

---

## 設定時のポイント

1. **unit_label と rank の組み合わせはユニーク**: `uk_unit_label_rank` ユニーク制約があるため重複登録不可。
2. **ランクが高いほどコストが増加する設計**: リミテッドメモリー数・要求レベル・フラグメント数がランクとともに増加する。
3. **低ランクのフラグメントは0から始まる**: ランク1はフラグメント不要、ランクが上がるにつれてSR→SSR→URの順に追加される。
4. **mst_unit_specific_rank_upsが優先される**: ユニット個別の設定がある場合は本テーブルより specific_rank_ups が優先される可能性があるため、個別設定ユニットの有無を確認する。
5. **全ラベル × 全ランク分のレコードが必要**: 新しい unit_label 追加時はランク1〜最大ランク分のレコードを全て追加する。
6. **require_level はmst_unit_level_upsに対応するレベルであること**: 設定したrequire_levelが mst_unit_level_ups に存在しないとゲームロジックが崩れる。
7. **amount はリミテッドメモリーの消費数**: リミテッドメモリーの供給バランスを考慮した上で数値を設定する。
