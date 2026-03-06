# MstTutorial 詳細説明

> CSVパス: `projects/glow-masterdata/MstTutorial.csv`

---

## 概要

チュートリアルの各項目と開始条件を管理するテーブル。チュートリアルには「イントロ」「メイン」「フリー」の3タイプがあり、それぞれに開放条件・実行する関数名・表示順序・期間が設定される。

- `function_name` はゲームクライアントがチュートリアル開始時に呼び出す関数名を識別するキー
- `type` で強制実行（Intro/Main）とプレイヤーが任意に実行できる（Free）に分類される
- `condition_type` と `condition_value` でフリーパートの開放条件を設定する
- `function_name` にはユニーク制約があり、同一の関数名を重複して設定できない

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | UUID（function_nameと同じ値が使われることが多い） |
| release_key | bigint | 不可 | `1` | リリースキー |
| type | enum | 不可 | `Intro` | チュートリアルタイプ（`TutorialType` enum参照） |
| sort_order | int | 不可 | `0` | チュートリアルの実行順序（昇順） |
| function_name | varchar(255) | 不可 | `` | チュートリアルを識別する関数名（ユニーク制約あり） |
| condition_type | varchar(255) | 不可 | `` | フリーパートの開放条件種別 |
| condition_value | varchar(255) | 不可 | `` | フリーパートの開放条件値 |
| start_at | timestamp | 不可 | - | 開始日時 |
| end_at | timestamp | 不可 | - | 終了日時 |

**ユニークインデックス**: `mst_tutorials_function_name_unique`（`function_name`）

---

## TutorialType（チュートリアルタイプ）

| 値 | 説明 |
|---|---|
| `Intro` | イントロチュートリアル（ゲーム開始時に強制実行） |
| `Main` | メインチュートリアル（ゲーム進行中に順次実行） |
| `Free` | フリーパート（開放条件を満たしたプレイヤーが任意で実行） |

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_tutorial_tips_i18n` | `mst_tutorials.id` → `mst_tutorial_tips_i18n.mst_tutorial_id` | チュートリアルに関連するTipsテキスト・画像 |

---

## 実データ例

### 例1: イントロチュートリアル（ゲーム開始時）

```
id                         | release_key | type  | sort_order | function_name             | condition_type | condition_value | start_at            | end_at
TutorialStartIntroduction  | 202509010   | Intro | 1          | TutorialStartIntroduction | UserLevel      | tutorial_1      | 2024-01-01 09:00:00 | 2035-01-01 09:00:00
```

最初のイントロチュートリアル。条件としてユーザーレベルの `tutorial_1` 達成が必要。

### 例2: メインチュートリアル（連続実行）

```
id                | release_key | type | sort_order | function_name    | condition_type | condition_value | start_at            | end_at
StartMainPart1    | 202509010   | Main | 2          | StartMainPart1   | UserLevel      | tutorial_1      | 2024-01-01 09:00:00 | 2035-01-01 09:00:00
GachaConfirmed    | 202509010   | Main | 3          | GachaConfirmed   | UserLevel      | NULL            | 2024-01-01 09:00:00 | 2035-01-01 09:00:00
SetPartyFormation | 202509010   | Main | 4          | SetPartyFormation| UserLevel      | NULL            | 2024-01-01 09:00:00 | 2035-01-01 09:00:00
```

メインチュートリアルは sort_order 順に実行される。

---

## 設定時のポイント

1. **function_name はユニーク**: チュートリアルの関数名は1つしか登録できない。同じ関数を複数回チュートリアルとして定義することはできない
2. **id と function_name は同一の値を使用**: 実データの慣習として `id` と `function_name` に同じ文字列を設定している
3. **sort_order でチュートリアルの実行順序を制御**: Intro/Main タイプは sort_order 昇順で順に実行される
4. **フリーパートは condition_type と condition_value で開放条件を設定**: Free タイプのみ開放条件が意味を持つ。Intro/Main はチュートリアルの進行状態管理で制御される
5. **期間設定を長めに**: 通常は `start_at = 2024-01-01` ・ `end_at = 2035-01-01` のように長い期間を設定して常に有効な状態を保つ
6. **クライアントクラス**: `MstTutorialData`（`GLOW.Core.Data.Data`名前空間）。`id`・`type`（`TutorialType` enum）・`sortOrder`・`functionName`・`conditionType`（`TutorialConditionType` enum）・`conditionValue`・`startAt`・`endAt` が配信される
7. **Tipsとの連携**: チュートリアル中に表示するTipsは `mst_tutorial_tips_i18n` テーブルで別管理されているため、セットで設定する
