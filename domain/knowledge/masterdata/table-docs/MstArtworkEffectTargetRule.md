# MstArtworkEffectTargetRule 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtworkEffectTargetRule.csv`

---

## 概要

原画効果の対象（ターゲットルール）を管理するテーブル。`mst_artwork_effects` で定義された効果が、バトル中のどのユニットに適用されるかを定義する。

発動条件（`mst_artwork_effect_activation_rules`）が「いつ効果が発動するか」を定義するのに対し、このテーブルは「誰に効果が適用されるか」を定義する。複数ルールを組み合わせることで、「全ユニットの中からランダムに3体」「特定シリーズのユニットのみ」といった複雑な対象指定が可能になる。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。対象ルールレコードを一意に識別するID |
| mst_artwork_effect_id | varchar(255) | YES | 対象の効果の `mst_artwork_effects.id` |
| condition_type | varchar(255) | YES | 対象条件タイプ（後述のリスト参照） |
| condition_value | varchar(255) | YES | 条件値（条件タイプに応じた値） |
| release_key | bigint | YES | リリースキー |

---

## condition_type（対象条件タイプ）

| 値 | 説明 | condition_value の意味 |
|----|------|----------------------|
| `All` | 全ての味方ユニットが対象 | 空文字 |
| `TargetCount` | ランダムに選択する対象体数 | 選択するユニット数（数値文字列、例: `1`、`3`、`9`） |
| `Series` | 特定シリーズのユニットのみが対象 | シリーズIDの文字列（例: `spy`） |
| `Unit` | 特定のユニットのみが対象 | ユニットのID |

---

## 命名規則 / IDの生成ルール

- `id`: `{mst_artwork_effect_id}_{連番（2桁）}`（例: `artwork_spy_0001_01`、`artwork_spy_0001_02`）

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_artwork_effects` | 多対1 | 対象の原画効果。`mst_artwork_effect_id` で参照 |
| `mst_series` | 多対1 | `condition_type='Series'` の場合に `condition_value` が参照 |
| `mst_units` | 多対1 | `condition_type='Unit'` の場合に `condition_value` が参照 |

---

## 実データ例

### パターン1: 全ユニットからランダム1体を対象

```
ENABLE: e
id: artwork_spy_0001_01
mst_artwork_effect_id: artwork_spy_0001
condition_type: All
condition_value: （空文字）
release_key: 202603020

ENABLE: e
id: artwork_spy_0001_02
mst_artwork_effect_id: artwork_spy_0001
condition_type: TargetCount
condition_value: 1
release_key: 202603020
```

### パターン2: 全ユニットからランダム3体を対象

```
ENABLE: e
id: artwork_spy_0002_01
mst_artwork_effect_id: artwork_spy_0002
condition_type: All
condition_value: （空文字）
release_key: 202603020

ENABLE: e
id: artwork_spy_0002_02
mst_artwork_effect_id: artwork_spy_0002
condition_type: TargetCount
condition_value: 3
release_key: 202603020
```

### パターン3: 特定シリーズのユニットからランダム選択

```
# surシリーズのキャラを対象に選択
ENABLE: e
id: artwork_sur_xxxx_01
mst_artwork_effect_id: artwork_sur_xxxx
condition_type: Series
condition_value: sur
release_key: 202603020

ENABLE: e
id: artwork_sur_xxxx_02
mst_artwork_effect_id: artwork_sur_xxxx
condition_type: TargetCount
condition_value: 1
release_key: 202603020
```

---

## 設定時のポイント

1. **All + TargetCount のセット**: ランダム選択を表現するには `All`（全体から選択）と `TargetCount`（選択体数）の2レコードをセットで作成する。`TargetCount` のみでは機能しない。
2. **Series + TargetCount のセット**: 特定シリーズのキャラだけを対象にしたランダム選択は `Series` と `TargetCount` の2レコードで表現する。
3. **効果テキストとの一致**: `mst_artwork_effects_i18n` の説明テキストで記載している対象（「ランダムに味方1体」「ランダムに味方3体」等）と、このテーブルの設定内容が一致していることを確認する。
4. **発動条件との区別**: このテーブルは「誰が対象か」を定義し、`mst_artwork_effect_activation_rules` は「どの条件下で発動するか」を定義する。2つのテーブルを混同しないよう注意する。
5. **TargetCount の妥当性**: 編成可能なユニット数（通常は10体程度）を超えた `TargetCount` を設定すると、全ユニットが対象となる可能性がある。
6. **連番のid管理**: 同一 `mst_artwork_effect_id` 内で複数のルールを設定する場合、`_01`、`_02` と連番で振る。
