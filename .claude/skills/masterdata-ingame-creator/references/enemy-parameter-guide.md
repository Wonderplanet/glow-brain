# MstEnemyStageParameter 設計ガイド

`MstEnemyStageParameter` の設計に必要な情報をまとめたガイド。
詳細仕様は `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` を参照。

---

## c_ / e_ プレフィックスの使い分け

| プレフィックス | 用途 | mst_enemy_character_id の形式 |
|--------------|------|------------------------------|
| `c_` | **プレイヤーキャラが敵として登場**（イベントボス等） | `chara_{シリーズ}_{番号}` |
| `e_` | **敵専用キャラのパラメータ** | `enemy_{シリーズ}_{番号}` |

```
# プレイヤーキャラ（YOU）がボスとして登場する例:
id:                    c_you_00201_you1_charaget01_Boss_Yellow
mst_enemy_character_id: chara_you_00201   ← chara_ で始まる

# 敵専用キャラ（YOU）の雑魚:
id:                    e_you_00001_you1_charaget01_Normal_Colorless
mst_enemy_character_id: enemy_you_00001   ← enemy_ で始まる
```

---

## character_unit_kind の選択基準

| 値 | 使用場面 | クライアントの扱い |
|----|---------|-----------------|
| `Normal` | 通常の雑魚敵 | IsBoss = false |
| `Boss` | イベントボスクラス | IsBoss = true |
| `AdventBattleBoss` | 降臨バトル専用の上位ボス | IsBoss = true |

**選択の目安:**
- 雑魚 → `Normal`
- 通常イベントのボス → `Boss`
- 降臨バトルや特別なコンテンツの最強ボス → `AdventBattleBoss`

---

## ステータス値の範囲目安

### HP（基準値）

| 種別 | HP目安 | 備考 |
|------|--------|------|
| 雑魚（Normal、通常難易度） | `1,000 〜 10,000` | MstAutoPlayerSequenceのenemy_hp_coefで調整 |
| 雑魚（Normal、高難易度） | `50,000 〜 300,000` | |
| ボス（Boss） | `100,000 〜 1,000,000` | シーケンス倍率との兼ね合いで基準値を決める |
| 降臨ボス（AdventBattleBoss） | `10,000 〜 100,000` | シーケンス倍率が大きいため基準値は低めでよい |

**重要**: `MstEnemyStageParameter.hp` はあくまで基準値。
最終HPは `MstEnemyStageParameter.hp × MstInGame.*_coef × MstAutoPlayerSequence.enemy_hp_coef` で決まる。

### 攻撃力（attack_power）

| 強さ | attack_power |
|------|-------------|
| 弱い（デイリー等） | `50 〜 200` |
| 通常 | `200 〜 500` |
| 強い（VH等） | `500 〜 2,000` |
| ボス | `300 〜 3,800` |

### 移動速度（move_speed）

| 速度感 | move_speed |
|--------|-----------|
| 非常に遅い（砦前で待機） | `5 〜 20` |
| 遅い | `20 〜 35` |
| 普通 | `35 〜 50` |
| 速い | `50 〜 80` |
| 非常に速い | `80 〜 100` |

### 索敵距離（well_distance）

| 感覚 | well_distance |
|------|--------------|
| 狭い（近づかないと攻撃しない） | `0.11 〜 0.2` |
| 普通 | `0.2 〜 0.35` |
| 広い（遠くから攻撃） | `0.35 〜 0.6` |

### ノックバック回数（damage_knock_back_count）

| 種別 | 回数 |
|------|------|
| 雑魚 | `0 〜 2` |
| ボス | `2 〜 5` |
| ノックバックなし | `0` |

### 攻撃コンボサイクル（attack_combo_cycle）

| 値 | 意味 |
|-----|------|
| `0` | 攻撃しない（移動専用ユニット等） |
| `1` | シンプルな攻撃 |
| `6 〜 8` | コンボ多め（ボスらしい演出） |

---

## role_type の選択基準

| role_type | 特徴 | よく使う場面 |
|-----------|------|------------|
| `Attack` | 攻撃型 | ボス、攻撃的な雑魚 |
| `Defense` | 防御型 | 硬い雑魚 |
| `Balance` | バランス型 | 汎用雑魚 |
| `Technical` | テクニカル | アビリティ付き雑魚 |
| `Support` | サポート型 | 特殊な挙動の雑魚 |
| `Special` | スペシャル | 特殊キャラ |
| `Unique` | ユニーク | レア敵 |
| `None` | ロールなし | 分類不要な雑魚 |

---

## drop_battle_point の目安

| 種別 | 値 |
|------|-----|
| 通常雑魚 | `50 〜 200` |
| 強めの雑魚 | `200 〜 500` |
| ボス | `300 〜 1000` |
| スコアなし（VH等） | `0` |

---

## 変身設定の使い方

HP一定%以下で別パラメータに変わる演出。

**変身前レコード（HP50%で変身する例）:**
```
id:                              c_kai_00001_kai1_boss_Boss_Red
hp:                              500000
mstTransformationEnemyStageParameterId: c_kai_00002_kai1_boss_Boss_Red  ← 変身先
transformationConditionType:     HpPercentage
transformationConditionValue:    50    ← HP50%以下で変身
```

**変身後レコード:**
```
id:                              c_kai_00002_kai1_boss_Boss_Red
hp:                              500000   ← 変身後の新HP（別途設定）
mstTransformationEnemyStageParameterId: （空）   ← これ以上変身しない
transformationConditionType:     None
transformationConditionValue:    （空）
```

**実績のある変身条件値:**
- `30`（HP30%以下で変身）
- `50`（HP50%以下で変身）
- `1`（HP1%以下で変身）

---

## MstAutoPlayerSequenceとの倍率連携

```
最終HP = MstEnemyStageParameter.hp
       × MstInGame.normal_enemy_hp_coef（全体倍率）
       × MstAutoPlayerSequence.enemy_hp_coef（個別倍率）
```

**実用パターン:**
- 基準HPを1000に設定し、シーケンス倍率で10倍にして最終HP10000にする
- ボスのHPを大きく設定し、シーケンス倍率を1.5等の細かい調整に使う
- 汎用パラメータ（`general`）を使い回す場合は、シーケンス倍率で難易度調整する

---

## CSVカラム名の注意点

以下のカラムはCSV上でcamelCase表記（DBスキーマはsnake_case）:

| CSVカラム名（実際） | DB名（スキーマ） |
|-------------------|----------------|
| `mstTransformationEnemyStageParameterId` | `mst_transformation_enemy_stage_parameter_id` |
| `transformationConditionType` | `transformation_condition_type` |
| `transformationConditionValue` | `transformation_condition_value` |

**→ CSVを生成する際は、必ず `projects/glow-masterdata/MstEnemyStageParameter.csv` の1行目を確認し、実際のカラム名に従うこと。**
