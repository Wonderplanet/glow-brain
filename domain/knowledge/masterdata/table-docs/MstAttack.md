# MstAttack 詳細説明

> CSVパス: `projects/glow-masterdata/MstAttack.csv`
> 関連テーブル:
> - `projects/glow-masterdata/MstAttackElement.csv`（攻撃エレメント）
> - `projects/glow-masterdata/MstAttackHitEffect.csv`（ヒット演出）
> - `projects/glow-masterdata/MstAttackI18n.csv`（多言語テキスト）

---

## 1. 概要

`MstAttack` は**ユニット（キャラ・エネミー）の攻撃アクション定義テーブル**。1ユニットはグレード（レベル帯）ごとに1レコードを持ち、攻撃種別・タイミング・弱点（キラー）設定を格納する。

### ゲームプレイへの影響

- **攻撃種別** (`attack_kind`) によってモーションと効果が大きく異なる。キャラクターがフィールドを歩いて敵に近づいて攻撃するのが `Normal`、特殊な演出付きの大技が `Special`、召喚登場時のアニメーションが `Appearance`。
- **キラー設定** (`killer_colors` / `killer_percentage`) はいわゆる「弱点倍率」。指定した属性の敵に対してダメージが増幅され、差別化要素として機能する。
- **フレーム設定** (`action_frames` / `attack_delay` / `next_attack_interval`) は攻撃の実際の演出長さとタイミングを制御する。ゲームは60fps動作。

### 関連テーブルとの構造図

```
MstUnit（ユニット本体）
  └─ mst_unit_id → MstAttack.mst_unit_id（グレード別に複数レコード）
        ├─ attack_kind = Normal      （通常攻撃）
        ├─ attack_kind = Special     （スペシャルアタック、グレード1〜5）
        └─ attack_kind = Appearance  （登場モーション）

MstAttack
  └─ id → MstAttackElement.mst_attack_id（1:N、攻撃の各当たり判定ロジック）
  └─ id → MstAttackI18n.mst_attack_id（多言語スキル説明文）

MstAttackElement
  └─ hit_effect_id → MstAttackHitEffect.id（ヒット演出設定）
```

---

## 2. 全カラム一覧

### mst_attacks（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `release_key` | int | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `id` | varchar(255) | 不可 | - | 主キー。命名規則は後述 |
| `mst_unit_id` | varchar(255) | 不可 | - | 参照先ユニットID（`mst.units.id`） |
| `unit_grade` | int | 不可 | - | ユニットグレード。0〜5が一般的（0=グレードなし/Normal、1〜5=スペシャルグレード） |
| `attack_kind` | enum | 不可 | - | 攻撃種別。`Normal` / `Special` / `Appearance` の3種 |
| `killer_colors` | varchar(255) | 不可 | "" | 弱点対象属性。`Red` / `Blue` / `Green` / `Yellow` をカンマ区切りで複数指定可。未設定は空文字 |
| `killer_percentage` | int | 不可 | 0 | 弱点攻撃時のダメージ倍率（%）。100が等倍、150なら1.5倍ダメージ。弱点なしの場合は0 |
| `action_frames` | int | 不可 | - | 攻撃アクション全体のフレーム数（60fps）。モーションの長さに相当 |
| `attack_delay` | int | 不可 | - | 攻撃アクション開始から実際にダメージが発生するまでのフレーム数 |
| `next_attack_interval` | int | 不可 | - | 次の攻撃アクション開始までのインターバルフレーム数。`Normal`攻撃は正値、`Special`は0（次の攻撃移行なし） |
| `asset_key` | varchar(255) | - | "" | アセットキー（現行データでは未使用、空文字） |

---

## 3. 主要なenum/フラグの解説

### AttackKind（攻撃種別）

| 値 | 意味 | 用途 |
|---|---|---|
| `Normal` | 通常攻撃 | フィールド上でのオートリピート攻撃。`unit_grade = 0` が対応。次の攻撃まで `next_attack_interval` フレーム待機して繰り返す |
| `Special` | スペシャルアタック | ゲージが溜まった際に発動する大技。グレード1〜5それぞれに1レコード。発動後は次の攻撃へ移行しない（`next_attack_interval = 0`） |
| `Appearance` | 登場アニメーション | ユニットがフィールドに召喚された瞬間の演出。攻撃ダメージは発生しない場合が多い |

### Killer（弱点）設定

| カラム | 説明 |
|---|---|
| `killer_colors` | 弱点となる敵の属性。カンマ区切りで複数指定可。例: `"Green"` や `"Red,Blue"` |
| `killer_percentage` | 弱点時のダメージ倍率（%単位）。0は弱点なし。実際のデータでは110〜225程度の範囲で設定されている |

### フレーム設定の関係性

```
|←──── action_frames ────→|

       |← attack_delay →|
       ↑ ダメージ発生
                          |← next_attack_interval →| 次のアクション開始
```

- `action_frames` のフレーム数がモーションの全長
- `attack_delay` フレーム後にダメージ判定（MstAttackElement参照）が実行される
- `Normal` 攻撃は `next_attack_interval` フレーム後に再び同じ攻撃を繰り返す

---

## 4. 命名規則 / IDの生成ルール

### IDフォーマット

```
{mst_unit_id}_{attack_kind}_{unit_grade(5桁ゼロ埋め)}
```

#### 具体例

| ID例 | ユニットID | 攻撃種別 | グレード |
|------|-----------|---------|---------|
| `chara_dan_00001_Normal_00000` | `chara_dan_00001` | Normal | 0 |
| `chara_dan_00001_Special_00001` | `chara_dan_00001` | Special | 1 |
| `chara_dan_00001_Special_00005` | `chara_dan_00001` | Special | 5 |
| `e_glo_00001_tutorial_Boss_Yellow_Appearance_00001` | `e_glo_00001_tutorial_Boss_Yellow` | Appearance | 1 |

#### グレード番号の意味

- `00000` = Normal攻撃（グレードなし）
- `00001`〜`00005` = Special攻撃のグレード1〜5（スター数に対応）

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst.units` | `mst_attack.mst_unit_id → mst_units.id` | 攻撃が紐づくユニット本体 |

### 参照されるテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_attack_elements` | `mst_attack_elements.mst_attack_id → mst_attacks.id` | 攻撃ごとの当たり判定・ダメージ・エフェクト設定 |
| `mst_attacks_i18n` | `mst_attacks_i18n.mst_attack_id → mst_attacks.id` | スペシャルアタックの説明文（多言語） |

### 関連するCSVファイル群

| CSVファイル | 対応DBテーブル | 役割 |
|-----------|-------------|------|
| `MstAttack.csv` | `mst_attacks` | 攻撃アクションの基本定義（本テーブル） |
| `MstAttackElement.csv` | `mst_attack_elements` | 攻撃の各ヒット要素（ダメージ・範囲・効果など詳細設定） |
| `MstAttackHitEffect.csv` | `mst_attack_hit_effects` | ヒット時の演出（擬音語・SE）定義 |
| `MstAttackI18n.csv` | `mst_attacks_i18n` | スペシャルアタックの説明テキスト |
| `MstSpecialAttackI18n.csv` | `mst_special_attacks_i18n` | スペシャルアタックの名称テキスト |
| `MstSpecialRoleLevelUpAttackElement.csv` | `mst_special_role_level_up_attack_elements` | ロールレベルアップ時の追加攻撃エレメント |

---

## 6. 実データ例

### Normal攻撃（通常攻撃）

| id | mst_unit_id | unit_grade | attack_kind | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|---|---|---|---|---|---|---|---|---|
| `chara_dan_00001_Normal_00000` | `chara_dan_00001` | 0 | Normal | （なし） | 0 | 72 | 27 | 100 |
| `chara_dan_00002_Normal_00000` | `chara_dan_00002` | 0 | Normal | （なし） | 0 | 92 | 0 | 75 |
| `chara_gom_00001_Normal_00000` | `chara_gom_00001` | 0 | Normal | （なし） | 0 | 65 | 25 | 100 |

- アクション72フレーム、27フレーム後にダメージ発生、その後100フレームの間隔で繰り返し攻撃

### Special攻撃（グレード別）

| id | mst_unit_id | unit_grade | attack_kind | killer_colors | killer_percentage | action_frames | attack_delay | next_attack_interval |
|---|---|---|---|---|---|---|---|---|
| `chara_dan_00001_Special_00001` | `chara_dan_00001` | 1 | Special | （なし） | 0 | 200 | 83 | 0 |
| `chara_dan_00001_Special_00005` | `chara_dan_00001` | 5 | Special | （なし） | 0 | 200 | 83 | 0 |
| `chara_jig_00101_Special_00001` | `chara_jig_00101` | 1 | Special | Green | 110 | 125 | 60 | 0 |
| `chara_jig_00101_Special_00005` | `chara_jig_00101` | 5 | Special | Green | 150 | 125 | 60 | 0 |

- `chara_jig_00101` はGreen属性キラーを持ち、グレードが上がると弱点倍率も上昇（110%→125%→136%→144%→150%）
- Special攻撃は `next_attack_interval = 0`（一度発動後は次の攻撃へ移行しない）

### Appearance攻撃（登場演出）

| id | mst_unit_id | unit_grade | attack_kind | action_frames | attack_delay |
|---|---|---|---|---|---|
| `e_glo_00001_tutorial_Boss_Yellow_Appearance_00001` | `e_glo_00001_tutorial_Boss_Yellow` | 0 | Appearance | 50 | 0 |
| `c_chi_00201_general_Boss_Yellow_Appearance_00001` | `c_chi_00201_general_Boss_Yellow` | 0 | Appearance | 50 | 0 |

- 登場演出は全て `action_frames = 50`、`attack_delay = 0`、`next_attack_interval = 0` の固定値
- キラー設定なし（登場演出はダメージ発生なし）

---

## 7. 設定時のポイント

### IDの採番ルール

- `Normal` 攻撃は必ず `unit_grade = 0`、`_Normal_00000` サフィックスを使用する
- `Special` 攻撃は `unit_grade = 1` から始まり、最大 `unit_grade = 5` まで5レコード作成する
- `Appearance` 攻撃は `unit_grade = 0`（または1）で1レコードのみ作成する
- IDはユニットIDをそのままプレフィックスに使う（手動変更禁止）

### フレーム数の設定

- ゲームは60fpsで動作するため、1秒 = 60フレーム
- `action_frames` はアニメーション担当と連携して決定する（アニメーション長と一致させる必要がある）
- `attack_delay` はモーションの「ヒットフレーム」に合わせて設定する
- `Normal` 攻撃の `next_attack_interval` は攻撃間隔を調整するゲームバランスパラメータ

### キラー（弱点）設定の注意点

- `killer_colors` を設定する場合は必ず `killer_percentage` も設定する（0以外の値）
- `killer_colors` が空文字（弱点なし）の場合、`killer_percentage` は `0` にする
- 複数の弱点属性を設定する場合はカンマ区切り（例: `"Red,Blue"`）で記述する
- `killer_percentage` の値は100を基準とし、110〜225の範囲が実データでの使用範囲
- グレードが上がるにつれて `killer_percentage` を段階的に増加させるのが推奨パターン

### Special攻撃の設定

- `next_attack_interval` は必ず `0` にする（Special発動後はオートリピートしない）
- グレード1〜5の5レコードを必ずセットで作成する
- キラー設定（`killer_colors` / `killer_percentage`）はグレード間で属性を統一し、倍率のみ変化させる

### MstAttackElement との関係

- `MstAttack` 単体ではダメージ計算・範囲指定・状態異常付与の詳細は定義されない
- 実際の攻撃効果はすべて `MstAttackElement` に記述されており、1つの `MstAttack` に対して1件以上の `MstAttackElement` が必要
- `MstAttack` のレコードを追加した場合は、必ず対応する `MstAttackElement` も追加すること

### レコード数の目安

| 攻撃種別 | 1ユニットあたりのレコード数 |
|---------|--------------------------|
| Normal | 1件（unit_grade = 0） |
| Special | 5件（unit_grade = 1〜5） |
| Appearance | 1件（unit_grade = 0 または 1） |

全体レコード数: 2601件（2026-03-06時点）
- Normal: 1174件
- Special: 992件
- Appearance: 435件
