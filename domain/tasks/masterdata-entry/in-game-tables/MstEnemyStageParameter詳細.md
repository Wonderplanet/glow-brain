# MstEnemyStageParameter 詳細説明

> 参照リリースキー: 202602015
> CSVパス: `domain/raw-data/masterdata/released/{リリースキー}/tables/MstEnemyStageParameter.csv`

---

## 概要

MstEnemyStageParameter は**インゲーム（バトル）で召喚される敵ユニットのステータス・挙動パラメータ**を定義するテーブル。
「どの敵キャラクターが、どのステージで、どんな強さで出てくるか」を1行1レコードで表す。

MstAutoPlayerSequenceが「いつ・どこに・何体出すか」を定義するのに対して、
MstEnemyStageParameterは「その敵が実際にどんな能力を持つか」を定義する。

```
MstAutoPlayerSequence
  └─ action_type = SummonEnemy
       └─ action_value = MstEnemyStageParameter.id  ← ここで参照される
                           ├─ HP / 攻撃力 / 移動速度
                           ├─ ロールタイプ / 影色
                           ├─ アビリティ
                           └─ 変身条件（オプション）
```

**重要な特徴**:
- 同じキャラ（`mst_enemy_character_id`）でも、ステージ難易度・役割（ボスか雑魚か）・色違いなど、用途ごとに別レコードを作成する
- プレイヤーキャラクターが敵として出てくる場合（`c_`プレフィックス）もこのテーブルで管理する

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| `ENABLE` | string | ○ | `e` = 有効 |
| `id` | string | ○ | レコードID。命名規則は後述 |
| `release_key` | bigint | ○ | リリースキー |
| `mst_enemy_character_id` | string | ○ | キャラクターID。`MstEnemyCharacter.id` を参照。見た目・アセットが決まる |
| `character_unit_kind` | string | ○ | 敵の種別 → 詳細は後述 |
| `role_type` | enum | ○ | ロールタイプ → 詳細は後述 |
| `color` | enum | ○ | 影色（色相性に影響）→ 詳細は後述 |
| `sort_order` | int | ○ | 管理画面等での表示並び順 |
| `hp` | int | ○ | 基本HP。MstInGame・MstAutoPlayerSequenceの倍率と乗算される |
| `damage_knock_back_count` | int | ○ | ノックバック回数。HPが等分減るたびに後退する回数 |
| `move_speed` | int | ○ | 移動速度（実測値範囲: 5〜100）。大きいほど速い |
| `well_distance` | float | ○ | 索敵距離（実測値範囲: 0.11〜0.6）。この距離内に敵を検知したら攻撃 |
| `attack_power` | int | ○ | 攻撃力（実測値範囲: 50〜3800）|
| `attack_combo_cycle` | int | ○ | 攻撃コンボサイクル数。`0` = 攻撃しない（移動専用ユニット等） |
| `mst_unit_ability_id1` | string | - | アビリティID。`MstUnitAbility.id` を参照。空 = アビリティなし |
| `drop_battle_point` | int | ○ | 撃破時のバトルポイント（リーダーP）獲得量 |
| `mstTransformationEnemyStageParameterId` | string | - | 変身後の敵ID。空 = 変身なし |
| `transformationConditionType` | string | ○ | 変身条件タイプ。`None` = 変身なし / `HpPercentage` = HP%で変身 |
| `transformationConditionValue` | string | - | 変身条件の値（HpPercentageなら0〜100の数値） |

> **CSVカラム名の注意**: `mstTransformationEnemyStageParameterId` / `transformationConditionType` / `transformationConditionValue` はCSV上でcamelCase表記。DBスキーマでは snake_case（`mst_transformation_enemy_stage_parameter_id` 等）。

---

## character_unit_kind（敵の種別）

| 値 | 用途 |
|----|------|
| `Normal` | 通常の雑魚敵。複数体まとめて出現することが多い |
| `Boss` | ボス敵。HPが高く、aura演出（MstAutoPlayerSequence.aura_type）が付くことが多い |
| `AdventBattleBoss` | 降臨バトル専用ボス。`Boss` より格上の演出 |

> `IsBoss` 判定（クライアント内部）: `Boss` または `AdventBattleBoss` のとき `true`

---

## role_type（ロールタイプ）

ユニットの役割分類。ゲームプレイ上の色相性ボーナスや行動パターンに影響する。

| 値 | 説明 |
|----|------|
| `None` | ロールなし |
| `Attack` | 攻撃型 |
| `Balance` | バランス型 |
| `Defense` | 防御型 |
| `Support` | サポート型 |
| `Unique` | ユニーク型 |
| `Technical` | テクニカル型 |
| `Special` | スペシャル型 |

---

## color（影色）

ユニットの色属性。色相性ボーナス計算に使用。

| 値 | 説明 |
|----|------|
| `None` | 無属性（色相性なし） |
| `Colorless` | 無色 |
| `Red` | 赤 |
| `Blue` | 青 |
| `Yellow` | 黄 |
| `Green` | 緑 |

---

## 変身（Transformation）機能

HPが一定以下になったとき、別のパラメータセットに切り替わる機能。
「見た目は同じキャラだがステータスが変わる」「形態変化する」演出に使う。

```
変身前レコード:
  id: c_chi_00001_general_chi_vh_Boss_Blue
  hp: 500000
  mstTransformationEnemyStageParameterId: c_chi_00002_general_chi_vh_Boss_Blue  ← 変身先
  transformationConditionType: HpPercentage
  transformationConditionValue: 30  ← HP30%以下で変身

変身後レコード:
  id: c_chi_00002_general_chi_vh_Boss_Blue
  hp: 〔変身後のHP〕
  mstTransformationEnemyStageParameterId: （空）  ← 変身しない
  transformationConditionType: None
```

### transformationConditionType の値

| 値 | transformationConditionValue | 説明 |
|----|------------------------------|------|
| `None` | （空） | 変身しない。大多数のレコード |
| `HpPercentage` | 0〜100の整数 | HPが指定%以下になったとき変身 |

> **実績値**: `HpPercentage` 使用時は `30`（HP30%で変身）または `50`（HP50%で変身）または `1`（HP1%で変身）がよく使われる。

---

## IDの命名規則

### 基本形式

```
{種別プレフィックス}_{mst_enemy_character_id短縮}_{インゲームID}_{character_unit_kind}_{color}
```

### 種別プレフィックス

| プレフィックス | 意味 | mst_enemy_character_idの形式 |
|--------------|------|---------------------------|
| `e_` | 敵キャラ（Enemy）専用のパラメータ | `enemy_xxx_xxxxx` |
| `c_` | プレイヤーキャラクターが敵として登場するパラメータ | `chara_xxx_xxxxx` |

> `c_` は通常のプレイヤーキャラと同じアセットを持つ敵（イベントボスなど）。

### IDの例

| id | mst_enemy_character_id | character_unit_kind | color | 説明 |
|----|------------------------|---------------------|-------|------|
| `e_chi_00101_general_Normal_Colorless` | `enemy_chi_00101` | Normal | Colorless | 通常雑魚（汎用ステージ用） |
| `e_chi_00001_general_Boss_Yellow` | `enemy_chi_00001` | Boss | Yellow | 汎用ボス |
| `c_chi_00201_general_Boss_Yellow` | `chara_chi_00201` | Boss | Yellow | プレイヤーキャラが敵として登場 |
| `e_kai_00301_kai1_advent_Boss_Red` | `enemy_kai_00301` | AdventBattleBoss | Red | 降臨バトルボス |
| `c_chi_00001_general_chi_vh_Boss_Blue` | `chara_chi_00001` | Boss | Blue | Very Hard用ボス（変身あり） |
| `e_dan_00001_general_n_trans_Normal_Colorless` | `enemy_dan_00001` | Normal | Colorless | 変身する雑魚 |

### インゲームIDの短縮形パターン

| インゲームIDの一部 | 意味 |
|-----------------|------|
| `general` | 汎用（複数ステージで使い回す） |
| `{イベントID}_n` | 特定イベントノーマル難易度 |
| `{イベントID}_h` | 特定イベントハード難易度 |
| `{イベントID}_vh` | 特定イベントベリーハード難易度 |
| `{イベントID}_vh2` | 特定イベントVH2難易度 |
| `{イベントID}_advent` | 降臨バトル用 |
| `{インゲームID（省略形）}` | 特定インゲームにのみ使用 |

---

## 実データ例

### パターン1: シンプルな雑魚（汎用）

`e_chi_00101_general_Normal_Colorless`

```
id:                      e_chi_00101_general_Normal_Colorless
mst_enemy_character_id:  enemy_chi_00101
character_unit_kind:     Normal
role_type:               Defense
color:                   Colorless
hp:                      5000
damage_knock_back_count: 1         ← ノックバック1回
move_speed:              35
well_distance:           0.11      ← 索敵距離は標準的
attack_power:            320
attack_combo_cycle:      1
mst_unit_ability_id1:    （空）
drop_battle_point:       50
mstTransformationEnemyStageParameterId: （空）
transformationConditionType: None
```

→ 汎用ステージで使い回せるシンプルな雑魚。アビリティなし、変身なし。

---

### パターン2: ボス敵

`e_chi_00201_general_Boss_Yellow`

```
id:                      e_chi_00201_general_Boss_Yellow
mst_enemy_character_id:  enemy_chi_00201
character_unit_kind:     Boss
role_type:               Attack
color:                   Yellow
hp:                      500000    ← 雑魚の100倍
damage_knock_back_count: 3         ← ノックバック3回（HPを3等分で後退）
move_speed:              50
well_distance:           0.2
attack_power:            320
attack_combo_cycle:      7         ← コンボ数多め
mst_unit_ability_id1:    （空）
drop_battle_point:       50
mstTransformationEnemyStageParameterId: （空）
transformationConditionType: None
```

→ HPが高く、ノックバック回数も多い典型的なボスパラメータ。

---

### パターン3: プレイヤーキャラが敵として登場

`c_chi_00201_general_Boss_Yellow`

```
id:                      c_chi_00201_general_Boss_Yellow
mst_enemy_character_id:  chara_chi_00201   ← プレイヤーキャラのID
character_unit_kind:     Boss
role_type:               Attack
color:                   Yellow
hp:                      500000
damage_knock_back_count: 1
move_speed:              45
well_distance:           0.11
attack_power:            400
attack_combo_cycle:      7
mst_unit_ability_id1:    （空）
drop_battle_point:       50
mstTransformationEnemyStageParameterId: （空）
transformationConditionType: None
```

→ `mst_enemy_character_id` が `chara_` で始まるのがポイント。プレイヤーが使うキャラと同じ見た目で敵として登場する演出に使う。

---

### パターン4: アビリティ付きの強敵

`e_chi_00001_general_chi_vh_Normal_Red`

```
id:                      e_chi_00001_general_chi_vh_Normal_Red
mst_enemy_character_id:  enemy_chi_00001
character_unit_kind:     Normal
role_type:               Technical
color:                   Red
hp:                      250000    ← 雑魚だが高HP
damage_knock_back_count: 1
move_speed:              35
well_distance:           0.4       ← 索敵距離が広め
attack_power:            700
attack_combo_cycle:      1
mst_unit_ability_id1:    enemy_ability_gust_1_zombie  ← アビリティあり
drop_battle_point:       0         ← スコアなし（VH用）
transformationConditionType: None
```

→ 強い難易度（VH）向け。アビリティを持ち、索敵距離が広いため積極的に攻撃してくる。

---

### パターン5: 変身するボス

`c_chi_00001_general_chi_vh_Boss_Blue`

```
id:                      c_chi_00001_general_chi_vh_Boss_Blue
mst_enemy_character_id:  chara_chi_00001
character_unit_kind:     Boss
role_type:               （省略）
hp:                      500000
attack_power:            500
mstTransformationEnemyStageParameterId: c_chi_00002_general_chi_vh_Boss_Blue  ← 変身先
transformationConditionType: HpPercentage
transformationConditionValue: 30   ← HP30%で変身

---変身後---
id:                      c_chi_00002_general_chi_vh_Boss_Blue
hp:                      （変身後の新しいHP）
transformationConditionType: None  ← もう変身しない
```

→ HP30%以下になると別キャラ（`c_chi_00002`）のパラメータに切り替わる。2段階形態変化の演出に使う。

---

### パターン6: 降臨バトルボス

`e_kai_00001_kai1_advent_Boss_Red`

```
id:                      e_kai_00001_kai1_advent_Boss_Red
mst_enemy_character_id:  enemy_kai_00001
character_unit_kind:     AdventBattleBoss  ← 降臨専用
role_type:               Attack
color:                   Red
hp:                      10000   ← MstAutoPlayerSequenceで大きな倍率をかけて最終HP調整
damage_knock_back_count: 3
move_speed:              21      ← 遅め（強力な攻撃を持つボスのため）
well_distance:           0.35    ← 広い索敵
attack_power:            500
attack_combo_cycle:      1
mst_unit_ability_id1:    （空）
drop_battle_point:       300
transformationConditionType: None
```

→ 降臨バトル専用の最上位ボス。`AdventBattleBoss` は `aura_type=AdventBoss3` と組み合わせて特別な演出になる。

---

## MstAutoPlayerSequenceとの連携

MstAutoPlayerSequenceの `action_type = SummonEnemy` の行で `action_value` としてこのIDを指定する。

```
MstAutoPlayerSequence の行:
  action_type:   SummonEnemy
  action_value:  e_chi_00101_general_Normal_Colorless  ← MstEnemyStageParameter.id
  summon_count:  5
  enemy_hp_coef: 3.0    ← このシーケンス行でのHP倍率

最終HP計算:
  最終HP = MstEnemyStageParameter.hp（5000）
         × MstInGame.normal_enemy_hp_coef（全体倍率）
         × MstAutoPlayerSequence.enemy_hp_coef（3.0）
```

---

## 設定時のポイントと注意事項

### ポイント1: HPはMstAutoPlayerSequenceの倍率と乗算される

```
最終HP = MstEnemyStageParameter.hp
       × MstInGame.normal_enemy_hp_coef  （ステージ全体倍率）
       × MstAutoPlayerSequence.enemy_hp_coef（シーケンス行ごとの倍率）
```

同じ `MstEnemyStageParameter` でも、参照するシーケンスの `enemy_hp_coef` を変えるだけでHP調整できる。
`hp` は「基準値」として設定し、細かい強さ調整はシーケンス側で行うのが基本。

### ポイント2: character_unit_kind は演出に直結する

- `Boss` / `AdventBattleBoss` は MstAutoPlayerSequence の `aura_type` 設定と組み合わせて特別な出現演出になる
- クライアント内部で `IsBoss` 判定があり、Boss以上は特別扱いされる

### ポイント3: `e_` と `c_` プレフィックスの使い分け

| プレフィックス | mst_enemy_character_id | 用途 |
|--------------|----------------------|------|
| `e_` | `enemy_xxx_xxxxx` | 敵専用キャラクターのパラメータ |
| `c_` | `chara_xxx_xxxxx` | プレイヤーキャラを敵として使うパラメータ |

プレイヤーキャラがイベントで「ラスボス」として登場する場合などは `c_` を使う。

### ポイント4: attack_combo_cycle = 0 は攻撃しないユニット

移動してプレイヤー砦に到達することだけが目的のユニット（障害物的な存在）などに使う。

### ポイント5: drop_battle_point = 0 のケース

- 強難易度（VH等）のステージでスコア表示が不要な場合に `0` を設定
- 通常ステージは `0` より大きい値を設定して撃破時にバトルポイントが獲得できる

### ポイント6: damage_knock_back_count の効果

HPを等分した「区切り」で敵が後退する演出回数。
例: `hp=300000, damage_knock_back_count=3` の場合、HP 200000 / 100000 / 0 の3回後退する。
ボスほど大きな値になりやすい。

### ポイント7: 変身先IDは必ず事前に用意する

`mstTransformationEnemyStageParameterId` で指定するIDが存在しないと、変身時にエラーになる。
変身前・変身後のレコードを**同じCSVで一括作成**すること。
