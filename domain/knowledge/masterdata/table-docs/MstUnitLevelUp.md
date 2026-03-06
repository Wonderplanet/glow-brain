# MstUnitLevelUp 詳細説明

> CSVパス: `projects/glow-masterdata/MstUnitLevelUp.csv`

---

## 概要

キャラクター（ユニット）のレベルアップに必要なコイン消費量を定義するテーブル。
ユニットラベル（レアリティ）とレベルの組み合わせごとに、そのレベルへアップするために消費するコイン数を設定する。
レベル1から始まり、レベル1の消費コインは0（無料）で設定される。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID（CSVでは連番整数） |
| unit_label | enum | NOT NULL | - | ユニットラベル（レアリティ種別） |
| level | int | NOT NULL | - | レベル（1〜上限値） |
| required_coin | int | NOT NULL | - | レベルアップに必要なコイン数 |
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

CSVの `id` は連番整数で管理される。unit_label と level の組み合わせが `uk_unit_label_level` ユニーク制約により保護されている。

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| mst_unit_rank_ups | unit_label + require_level | ランクアップに必要なレベル条件 |
| mst_units | unit_label | ユニット本体のレアリティ情報 |
| mst_user_levels | - | プレイヤーレベルとの関連（スタミナ上限） |

---

## 実データ例（CSVから取得）

### パターン1: DropR（ドロップRレアリティ）のレベルアップコスト（レベル1〜10）

| id | unit_label | level | required_coin | release_key |
|---|---|---|---|---|
| 1 | DropR | 1 | 0 | 202509010 |
| 2 | DropR | 2 | 20 | 202509010 |
| 3 | DropR | 3 | 20 | 202509010 |
| 4 | DropR | 4 | 21 | 202509010 |
| 5 | DropR | 5 | 22 | 202509010 |
| 6 | DropR | 6 | 22 | 202509010 |
| 7 | DropR | 7 | 23 | 202509010 |
| 8 | DropR | 8 | 24 | 202509010 |
| 9 | DropR | 9 | 25 | 202509010 |
| 10 | DropR | 10 | 27 | 202509010 |

### パターン2: 別レアリティとの比較（レベル1）

| unit_label | level | required_coin |
|---|---|---|
| DropR | 1 | 0 |
| DropSR | 1 | 0 |
| DropSSR | 1 | 0 |
| PremiumUR | 1 | 0 |

---

## 設定時のポイント

1. **レベル1のコストは必ず0**: 初期状態からのレベルアップ（Lv1→Lv2相当ではなく、Lv1そのもの）はコストなし。全ラベルで統一する。
2. **unit_label と level の組み合わせはユニーク**: DBスキーマに `uk_unit_label_level` ユニーク制約があるため重複登録不可。
3. **レベル上限はmst_unit_rank_upsのrequire_levelと連動**: ランクアップ（Lv上限開放）前後でレベル上限が変わるため、対応するレベルまでの全行が必要。
4. **コイン消費量はレベルが上がるにつれて増加する設計**: 単調増加で設計すること。急激な変化は避ける。
5. **全ラベル × 全レベル数分のレコードが必要**: ラベル追加時は最大レベルまでの全レコードを追加する。
6. **ランクアップによるLv上限開放後のレベルも登録が必要**: ランク2以降のレベル（例: Lv21〜30）についても本テーブルに登録する。
7. **release_key はリリース管理に使用**: Lv上限開放追加時は新しいリリースキーを設定する。
