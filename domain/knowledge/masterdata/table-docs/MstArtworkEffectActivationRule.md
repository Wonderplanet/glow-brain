# MstArtworkEffectActivationRule 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtworkEffectActivationRule.csv`

---

## 概要

原画効果の発動条件（アクティベーションルール）を管理するテーブル。`mst_artwork_effects` で定義された効果が、どの条件下で発動するかをレコード単位で定義する。

複数条件をAND条件として組み合わせる場合は、同じ `mst_artwork_effect_id` に対して複数のレコードを作成する。たとえば「SPY×FAMILYシリーズのキャラを1体以上編成」のような条件は、`Series=spy` と `Count=1` の2レコードで表現される。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。発動ルールレコードを一意に識別するID |
| mst_artwork_effect_id | varchar(255) | YES | 対象の効果の `mst_artwork_effects.id` |
| condition_type | varchar(255) | YES | 条件タイプ（後述のリスト参照） |
| condition_value | varchar(255) | YES | 条件値（条件タイプに応じた値） |
| release_key | bigint | YES | リリースキー |

---

## condition_type（条件タイプ）

| 値 | 説明 | condition_value の意味 |
|----|------|----------------------|
| `None` | 無条件（常時発動） | 空文字 |
| `Series` | 特定シリーズのキャラを編成している | シリーズIDの文字列（例: `spy`） |
| `Count` | 指定シリーズキャラの必要編成数 | 必要な編成体数（数値文字列、例: `1`） |
| `Unit` | 特定のユニットを編成している | ユニットのID |

---

## 命名規則 / IDの生成ルール

- `id`: `{mst_artwork_effect_id}_{連番（2桁）}`（例: `artwork_spy_0001_01`、`artwork_spy_0003_02`）

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_artwork_effects` | 多対1 | 対象の原画効果。`mst_artwork_effect_id` で参照 |
| `mst_series` | 多対1 | `condition_type='Series'` の場合に `condition_value` が参照 |
| `mst_units` | 多対1 | `condition_type='Unit'` の場合に `condition_value` が参照 |

---

## 実データ例

### パターン1: 無条件発動

```
ENABLE: e
id: artwork_spy_0001_01
mst_artwork_effect_id: artwork_spy_0001
condition_type: None
condition_value: （空文字）
release_key: 202603020
```

### パターン2: シリーズキャラの編成条件あり（Series + Count のAND条件）

```
# SPY×FAMILYキャラを1体以上編成している場合に発動
ENABLE: e
id: artwork_spy_0003_01
mst_artwork_effect_id: artwork_spy_0003
condition_type: Series
condition_value: spy
release_key: 202603020

ENABLE: e
id: artwork_spy_0003_02
mst_artwork_effect_id: artwork_spy_0003
condition_type: Count
condition_value: 1
release_key: 202603020
```

---

## 設定時のポイント

1. **None で常時発動**: シリーズ縛りや編成条件がない原画効果は `condition_type='None'` のレコードを1件作成する。
2. **Series + Count のセット**: シリーズ縛りを設定する場合は `Series`（対象シリーズ指定）と `Count`（必要体数）の2レコードをセットで作成する。`Count` のみを設定しても正しく機能しない。
3. **AND 条件の表現**: 複数レコードはすべてAND条件として評価される。OR条件は現在のスキーマでは直接表現できない。
4. **condition_value の値確認**: `Series` タイプの場合は `mst_series.id`、`Unit` タイプの場合は `mst_units.id` に存在するIDを指定する。存在しないIDを指定するとバグの原因になる。
5. **効果テキストとの一致**: `mst_artwork_effects_i18n` の説明テキストで言及している条件と、このテーブルの設定内容が一致していることを確認する。
6. **連番のid管理**: 同一 `mst_artwork_effect_id` 内で複数のルールを設定する場合、`_01`、`_02` と連番で振る。
