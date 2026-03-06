# MstPvpBonusPoint 詳細説明

> CSVパス: `projects/glow-masterdata/MstPvpBonusPoint.csv`

---

## 概要

`MstPvpBonusPoint` は**PvPバトル結果に応じて付与されるボーナスポイントを定義するテーブル**。勝利時の対戦相手のランクや、バトルのクリアタイムに基づくボーナスを設定する。

ボーナスタイプは大きく2カテゴリに分かれる:
1. **勝利ボーナス系** (`WinUpperBonus`/`WinSameBonus`/`WinLowerBonus`): 対戦相手のランク区分（格上・同格・格下）に応じた勝利ボーナス
2. **クリアタイムボーナス** (`ClearTime`): バトルを指定ミリ秒以内にクリアした場合のボーナス

### ゲームプレイへの影響

- `WinUpperBonus`: 格上のプレイヤーに勝利した際に付与される追加ポイント（多め）
- `WinSameBonus`: 同格のプレイヤーに勝利した際のボーナスポイント（中程度）
- `WinLowerBonus`: 格下のプレイヤーに勝利した際のボーナスポイント（少なめ）
- `ClearTime`: 指定ミリ秒（`condition_value`）以内にクリアすると `bonus_point` が加算される。タイムが早いほどボーナスが大きい

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `condition_value` | varchar(255) | 可 | NULL | しきい値。ClearTimeはミリ秒、Win系はランク区分名 |
| `bonus_point` | int unsigned | 不可 | 0 | 付与されるボーナスポイント数 |
| `bonus_type` | enum | 不可 | - | PvPボーナスタイプ（後述のenum参照） |

---

## PvpBonusType（PVPボーナスタイプ）

| 値 | 説明 | condition_value |
|----|------|----------------|
| `ClearTime` | クリアタイムボーナス。指定ミリ秒以内のクリアでボーナス付与 | クリアタイム上限（ミリ秒） |
| `WinUpperBonus` | 格上への勝利ボーナス | ランク区分名（Bronze/Silver/Gold/Platinum） |
| `WinSameBonus` | 同格への勝利ボーナス | ランク区分名 |
| `WinLowerBonus` | 格下への勝利ボーナス | ランク区分名 |

---

## 命名規則 / IDの生成ルール

- `id`: ボーナスタイプと条件を組み合わせた命名
  - ClearTime系: `default_timebonus_{連番}` 形式（例: `default_timebonus_1`）
  - Win系: `default_{ランク区分}_{upper/same/lower}` 形式（例: `default_bronze_upper`）

---

## 他テーブルとの連携

このテーブルはPvPバトル結果処理サーバーロジックから参照されるが、他のマスタテーブルとの直接的な外部キー参照はない。

```
PvPバトル結果処理（サーバー）
  └─ 対戦相手ランク区分 → MstPvpBonusPoint.condition_value（Win系）でボーナス取得
  └─ クリアタイム → MstPvpBonusPoint.condition_value（ClearTime）でボーナス取得
```

---

## 実データ例

**パターン1: クリアタイムボーナス**

| ENABLE | id | release_key | condition_value | bonus_point | bonus_type |
|--------|-----|-------------|----------------|-------------|------------|
| e | default_timebonus_1 | 202509010 | 30000 | 10 | ClearTime |
| e | default_timebonus_2 | 202509010 | 40000 | 9 | ClearTime |
| e | default_timebonus_8 | 202509010 | 100000 | 3 | ClearTime |
| e | default_timebonus_10 | 202509010 | 140000 | 1 | ClearTime |

**パターン2: 勝利ランクボーナス**

| ENABLE | id | release_key | condition_value | bonus_point | bonus_type |
|--------|-----|-------------|----------------|-------------|------------|
| e | default_bronze_upper | 202509010 | Bronze | 20 | WinUpperBonus |
| e | default_bronze_same | 202509010 | Bronze | 10 | WinSameBonus |
| e | default_bronze_lower | 202509010 | Bronze | 5 | WinLowerBonus |
| e | default_gold_upper | 202509010 | Gold | 20 | WinUpperBonus |

---

## 設定時のポイント

1. **ClearTimeのcondition_valueはミリ秒**: 30秒 = 30000ms のように設定する。値が小さいほど短時間クリア=高ボーナスとなるよう、bonus_pointを降順で設定する
2. **Win系のcondition_valueはランク区分名**: `Bronze`・`Silver`・`Gold`・`Platinum` のいずれかを設定。全ランク区分に対してUpper/Same/Lowerの3種類を設定する
3. **格上ボーナスを最大に設定**: `WinUpperBonus > WinSameBonus > WinLowerBonus` の順でボーナスが高くなる設計にする
4. **condition_valueとbonus_typeの組み合わせに一意制約あり**: 同じ `condition_value` + `bonus_type` の組み合わせで複数レコードは作成できない
5. **全ランク区分に設定が必要**: Bronze・Silver・Gold・Platinumそれぞれに対してWin系ボーナスの3種類（合計12レコード）を設定する
6. **クリアタイムボーナスの段階設定**: クリアタイムのしきい値を複数段階設定することで、高速クリアほど多くのポイントが獲得できる仕組みを実現する
