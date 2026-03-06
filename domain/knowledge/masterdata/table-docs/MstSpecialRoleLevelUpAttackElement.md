# MstSpecialRoleLevelUpAttackElement 詳細説明

> CSVパス: `projects/glow-masterdata/MstSpecialRoleLevelUpAttackElement.csv`

---

## 概要

`MstSpecialRoleLevelUpAttackElement` は**スペシャルロールキャラクターのレベルアップに応じた攻撃パラメータ（アタックエレメント）のスケーリング設定テーブル**。

スペシャルロールはユニットの特殊役割（ロール）のひとつで、レベルを上げるほど必殺技の攻撃力・エフェクト回数・持続時間・効果量などが強化される。本テーブルでは各アタックエレメント（攻撃の個々の当たり判定）ごとに、レベル最小・最大時のパラメータ値を設定する。

### ゲームへの影響

- スペシャルロールキャラのレベルが上がるにつれて、設定された `min` から `max` の範囲で各パラメータが線形補間される。
- **攻撃パラメータ** (`power_parameter`): ダメージ倍率。0の場合は固定値で `effect_parameter` が代わりに使われるケースあり。
- **エフェクト回数** (`effective_count`): 攻撃が何回ヒットするか。
- **エフェクト持続時間** (`effective_duration`): 攻撃効果が何フレーム持続するか（60fps）。
- **エフェクトパラメータ** (`effect_parameter`): バフ倍率・回復量など、攻撃種別に応じた追加パラメータ。

### テーブル連携図

```
MstAttackElement（攻撃エレメント）
  └─ id → MstSpecialRoleLevelUpAttackElement.mst_attack_element_id（1:1）
              （スペシャルロールのレベルアップ時のパラメータスケーリング）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_attack_element_id` | varchar(255) | 不可 | `""` | 対応するアタックエレメントID（`mst_attack_elements.id`） |
| `min_power_parameter` | decimal(10,2) | 不可 | - | レベル最小時の攻撃パラメータ |
| `max_power_parameter` | decimal(10,2) | 不可 | - | レベル最大時の攻撃パラメータ |
| `min_effective_count` | int | 不可 | - | 強化するeffective_countの最低値 |
| `max_effective_count` | int | 不可 | - | 強化するeffective_countの最高値 |
| `min_effective_duration` | int | 不可 | - | 強化するeffective_durationの最低値 |
| `max_effective_duration` | int | 不可 | - | 強化するeffective_durationの最高値 |
| `min_effect_parameter` | decimal(10,2) | 不可 | - | 強化するeffect_parameterの最低値 |
| `max_effect_parameter` | decimal(10,2) | 不可 | - | 強化するeffect_parameterの最高値 |

---

## 命名規則 / IDの生成ルール

`id` は `mst_attack_element_id` と同じ値を設定するのが慣例:

```
{mst_unit_id}_{attack_kind}_{連番5桁}
```

例:
- `chara_sur_00401_Special_00001` → surキャラ00401のスペシャル攻撃エレメント1番目

---

## 他テーブルとの連携

| 連携先テーブル | 結合キー | 用途 |
|-------------|--------|------|
| `MstAttackElement` | `MstSpecialRoleLevelUpAttackElement.mst_attack_element_id = MstAttackElement.id` | 攻撃エレメントの基本設定を取得 |

---

## 実データ例

### パターン1: surキャラ（サクラダリセット系）のスペシャル攻撃スケーリング

```csv
ENABLE,release_key,id,mst_attack_element_id,min_power_parameter,max_power_parameter,min_effective_count,max_effective_count,min_effective_duration,max_effective_duration,min_effect_parameter,max_effect_parameter
e,202509010,chara_sur_00401_Special_00001,chara_sur_00401_Special_00001,0,0,0,0,0,0,0,10.5
e,202509010,chara_sur_00401_Special_00002,chara_sur_00401_Special_00002,0,0,0,0,0,0,0,10.5
e,202509010,chara_sur_00401_Special_00003,chara_sur_00401_Special_00003,0,0,0,0,0,0,0,10.5
```

- `power_parameter` は0（固定）で `effect_parameter` のみ強化される
- `max_effect_parameter = 10.5` がレベル最大時の倍率

### パターン2: spyキャラ（SPY×FAMILY系）のスペシャル攻撃スケーリング

```csv
ENABLE,release_key,id,mst_attack_element_id,min_power_parameter,max_power_parameter,min_effective_count,max_effective_count,min_effective_duration,max_effective_duration,min_effect_parameter,max_effect_parameter
e,202510010,chara_spy_00301_Special_00001,chara_spy_00301_Special_00001,0,0,0,0,0,0,0,7.5
e,202510010,chara_spy_00301_Special_00002,chara_spy_00301_Special_00002,0,0,0,0,0,0,0,7.5
```

- `max_effect_parameter = 7.5` でsurキャラより低い最大倍率

---

## 設定時のポイント

1. **`mst_attack_element_id` は `MstAttackElement` に存在するIDを参照する**。スペシャルロール対象のアタックエレメントIDを正確に指定する。
2. **`min_*` と `max_*` の組み合わせでレベルアップ時のスケーリング範囲を定義する**。`min = max` の場合はレベルに関わらず固定値になる。
3. **0を設定したパラメータはスケーリングの対象外**。現在のデータパターンでは `power_parameter`, `effective_count`, `effective_duration` がすべて0で `effect_parameter` のみスケーリングしているケースが多い。
4. **1つのアタックエレメントにつき1レコード**（1:1対応）。同じ `mst_attack_element_id` のレコードを複数作らないように注意する。
5. **新スペシャルロールキャラ追加時は `MstAttackElement` → `MstSpecialRoleLevelUpAttackElement` の順に設定する**。先にアタックエレメントを作成してからスケーリング設定を追加する。
6. **`effect_parameter` の単位は攻撃種別によって異なる**（倍率・固定値等）。`MstAttackElement` の `effect_type` を確認して適切な単位で設定する。
7. **`release_key` は対応するスペシャルロールキャラの追加リリースキーに合わせる**。
