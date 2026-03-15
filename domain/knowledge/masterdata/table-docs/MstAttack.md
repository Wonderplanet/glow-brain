# MstAttack 詳細説明

> CSVパス: `projects/glow-masterdata/MstAttack.csv`

---

## 概要

MstAttack はユニット（キャラクター・敵）の**攻撃定義**を管理するテーブル。攻撃の種別（通常攻撃・スペシャル攻撃・登場時攻撃）・フレーム情報・キラー設定を保持する。

1つのユニットに対して複数の MstAttack レコードが存在し、`attack_kind` と `unit_grade`（グレード）の組み合わせで識別される。実際の攻撃ダメージ・効果範囲・対象などの詳細は、紐づく MstAttackElement に定義される。

```
MstUnit.id
  └─ MstAttack.mst_unit_id
       └─ MstAttackElement.mst_attack_id
```

総レコード数: **2,633件**（2026年3月時点）

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| `ENABLE` | varchar | ○ | `e` = 有効 |
| `release_key` | bigint | | リリースキー（例: `202509010`） |
| `id` | varchar | ○ | 攻撃ID（命名規則あり。後述） |
| `mst_unit_id` | varchar | ○ | 攻撃を所有するユニットID（MstUnit.id を参照） |
| `unit_grade` | bigint | ○ | ユニットのグレード（0〜5程度）。attack_kind=Normal は通常 0 |
| `attack_kind` | varchar | ○ | 攻撃種別（後述）|
| `killer_colors` | varchar | | キラー対象カラー（複数カラーをカンマ区切りで指定。空欄 = キラーなし） |
| `killer_percentage` | varchar | | キラー倍率（%単位。空欄 = キラーなし） |
| `action_frames` | bigint | ○ | 攻撃アクションの総フレーム数（1フレーム ≒ 1/60秒） |
| `attack_delay` | bigint | ○ | 攻撃開始までの遅延フレーム数 |
| `next_attack_interval` | bigint | ○ | 次の攻撃までのインターバルフレーム数（通常攻撃のみ有効。Special/Appearanceは 0） |
| `asset_key` | varchar | | 攻撃アニメーションのアセットキー（空欄 = デフォルトアニメ使用） |

---

## attack_kind（攻撃種別）

| 値 | 説明 |
|----|------|
| `Normal` | 通常攻撃。敵が自動的に繰り返す基本攻撃。`next_attack_interval` で連射間隔を制御 |
| `Special` | スペシャル攻撃。プレイヤーが発動するアクティブスキル。`unit_grade` でグレードを区別 |
| `Appearance` | 登場時攻撃。ユニットがフィールドに召喚された瞬間に発動する演出・効果 |

- `Normal` の `unit_grade` は通常 `0`（1種類のみ）
- `Special` の `unit_grade` は `1〜5`（グレードアップごとに別レコード）
- `Appearance` の `unit_grade` は通常 `0`（登場時1種類のみ）

---

## 命名規則 / IDの生成ルール

### 基本パターン

```
{mst_unit_id}_{attack_kind}_{unit_grade_5桁}
```

### 現行データの例

| id | 説明 |
|----|------|
| `chara_dan_00001_Normal_00000` | ダンキャラ00001の通常攻撃（グレード0） |
| `chara_dan_00001_Special_00001` | ダンキャラ00001のスペシャル攻撃グレード1 |
| `chara_dan_00001_Special_00005` | ダンキャラ00001のスペシャル攻撃グレード5 |
| `chara_dan_00001_Appearance_00000` | ダンキャラ00001の登場時攻撃 |

---

## 他テーブルとの連携

### MstUnit との関係

| カラム | 参照先 | 説明 |
|--------|--------|------|
| `mst_unit_id` | `mst_units.id` | この攻撃を所有するユニット |

### MstAttackElement との関係

| カラム | 参照元 | 説明 |
|--------|--------|------|
| `id` | `mst_attack_elements.mst_attack_id` | 1つの攻撃に対して1〜複数の攻撃エレメントが紐づく |

---

## 実データ例

### パターン1: 通常攻撃（Normal）

```
id:                   chara_dan_00001_Normal_00000
mst_unit_id:          chara_dan_00001
unit_grade:           0
attack_kind:          Normal
killer_colors:        （空欄）
killer_percentage:    （空欄）
action_frames:        72
attack_delay:         27
next_attack_interval: 100
asset_key:            （空欄）
```

- `action_frames=72`（約1.2秒）の攻撃モーション
- `attack_delay=27` フレーム後にダメージ判定
- `next_attack_interval=100` フレーム（約1.67秒）の連射間隔

### パターン2: スペシャル攻撃（Special, グレード別）

```
id:                   chara_dan_00001_Special_00001
mst_unit_id:          chara_dan_00001
unit_grade:           1
attack_kind:          Special
action_frames:        200
attack_delay:         83
next_attack_interval: 0   ← Specialは0固定
```

- `unit_grade` が 1〜5 まで別レコードで存在
- `next_attack_interval=0`（スペシャル攻撃は繰り返さない）
- グレードが上がるほど威力・効果がアップするが、このテーブルではフレームのみ管理

---

## 設定時のポイントと注意事項

### ポイント1: attack_kind と unit_grade の組み合わせ

- `Normal` は `unit_grade=0` の1レコードのみが基本
- `Special` はグレード数分（通常 1〜5 = 5レコード）を用意する
- `Appearance` は `unit_grade=0` の1レコードが基本

### ポイント2: next_attack_interval の扱い

- `Normal` のみ有効な値を設定する（攻撃間隔を制御）
- `Special` と `Appearance` は `0` を設定する（繰り返しなし）

### ポイント3: killer_colors / killer_percentage の形式

- `killer_colors` は対象カラー名をカンマ区切りで記述（例: `Red,Blue`）
- `killer_percentage` は倍率を % で記述（例: `150` = 1.5倍）
- キラーなしの場合は両方空欄にする

### ポイント4: 攻撃の実効果は MstAttackElement で定義

- このテーブルはフレームタイミングとキラー設定のみを管理
- ダメージ量・範囲・対象・状態効果などは全て MstAttackElement で設定する
- 1つの MstAttack に対して MstAttackElement が複数存在する場合（`sort_order` 順に処理）
