# 姫様"拷問"の時間です インゲームパターン分析

## 概要

- series_id: `gom`
- URキャラ:
  - `chara_gom_00001` (Green / Defense) — 囚われの王女 姫様
- インゲームコンテンツ数: 18件（MstInGame）
  - normal: 6件、hard: 6件、veryhard: 6件

---

## コンテンツ種別一覧

### normal（通常ブロック）

| ingame_id | bgm_asset_key | loop_background_asset_key | player_outpost_asset_key | mst_enemy_outpost_id | outpost_hp |
|-----------|--------------|--------------------------|--------------------------|----------------------|-----------|
| normal_gom_00001 | SSE_SBG_003_006 | gom_00001 | gom_ally_0001 | normal_gom_00001 | 5,000 |
| normal_gom_00002 | SSE_SBG_003_006 | gom_00001 | gom_ally_0001 | normal_gom_00002 | 5,000 |
| normal_gom_00003 | SSE_SBG_003_006 | gom_00001 | gom_ally_0001 | normal_gom_00003 | 5,000 |
| normal_gom_00004 | SSE_SBG_003_006 | gom_00002 | gom_ally_0001 | normal_gom_00004 | 5,000 |
| normal_gom_00005 | SSE_SBG_003_006 | gom_00002 | gom_ally_0001 | normal_gom_00005 | 5,000 |
| normal_gom_00006 | SSE_SBG_003_006 | gom_00002 | （なし） | normal_gom_00006 | 5,000 |

- normalブロックの共通仕様: アウトポストHP=5,000、boss_bgm_asset_keyなし
- loop_background_asset_keyはブロックによって異なる（`gom_00001`：00001〜00003、`gom_00002`：00004〜00006）
- player_outpost_asset_key: `gom_ally_0001`（00006のみ空白）

### hard（ハードブロック）

| ingame_id | bgm_asset_key | loop_background_asset_key | outpost_hp |
|-----------|--------------|--------------------------|-----------|
| hard_gom_00001 | SSE_SBG_003_006 | （なし） | 50,000 |
| hard_gom_00002 | SSE_SBG_003_006 | （なし） | 50,000 |
| hard_gom_00003 | SSE_SBG_003_006 | （なし） | 50,000 |
| hard_gom_00004 | SSE_SBG_003_006 | （なし） | 50,000 |
| hard_gom_00005 | SSE_SBG_003_006 | （なし） | 50,000 |
| hard_gom_00006 | SSE_SBG_003_006 | （なし） | 50,000 |

- hardブロック: loop_background_asset_keyが全件空白（コマアセットで背景を構成）
- player_outpost_asset_key: `gom_ally_0001`（全件）

### veryhard（超ハードブロック）

| ingame_id | bgm_asset_key | outpost_hp |
|-----------|--------------|-----------|
| veryhard_gom_00001 | SSE_SBG_003_006 | 100,000 |
| veryhard_gom_00002 | SSE_SBG_003_006 | 150,000 |
| veryhard_gom_00003 | SSE_SBG_003_006 | 150,000 |
| veryhard_gom_00004 | SSE_SBG_003_006 | 150,000 |
| veryhard_gom_00005 | SSE_SBG_003_006 | 100,000 |
| veryhard_gom_00006 | SSE_SBG_003_006 | 150,000 |

- veryhardのアウトポストHPは 100,000 または 150,000（spyより小さい作品もある）

---

## アウトポストHP スケール比較

| コンテンツ種別 | アウトポストHP | 係数比（normal基準） |
|--------------|-------------|---------------------|
| normal | 5,000 | 1.0x |
| hard | 50,000 | 10x |
| veryhard | 100,000〜150,000 | 20〜30x |

---

## エネミーID → 日本語名対応表

| asset_key | 日本語名 | 種別 |
|-----------|---------|------|
| chara_gom_00001 | 囚われの王女 姫様 | URキャラ（Green/Defense） |
| chara_gom_00101 | トーチャー・トルチュール | キャラ（Technical） |
| chara_gom_00201 | クロル | キャラ（Attack） |
| chara_gom_00701 | カナッジ | キャラ（Technical） |
| enemy_gom_00301 | キュイ | 雑魚敵（Boss型） |
| enemy_gom_00401 | たこ焼きくん | 雑魚敵（Boss型/Normal型） |
| enemy_gom_00402 | たこ焼き | 雑魚敵（Normal型） |
| enemy_gom_00501 | バタートースト | 雑魚敵（Normal型） |
| enemy_gom_00502 | 割きトースト | 雑魚敵（Normal型） |
| enemy_gom_00701 | ラーメン | 雑魚敵（Boss型） |
| enemy_gom_00801 | ライス | 雑魚敵（Normal型） |
| enemy_gom_00901 | ライス (海苔) | 雑魚敵（Normal型） |
| enemy_gom_01001 | あんぱん | 雑魚敵（Normal/Boss型） |
| enemy_gom_01002 | トーストあんぱん | 雑魚敵（Normal/Boss型） |

---

## エネミー別パラメータ詳細（MstEnemyStageParameter）

### URキャラ（ボスとして登場）: chara_gom_00001（囚われの王女 姫様）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| c_gom_00001_general_n_Boss_Yellow | Boss | Defense | Yellow | 10,000 | 25 | 0.16 | 50 | 6 | 500 |
| c_gom_00001_general_h_Boss_Yellow | Boss | Defense | Yellow | 10,000 | 25 | 0.16 | 100 | 5 | 200 |

### キャラ（ボスとして登場）: chara_gom_00101（トーチャー・トルチュール）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| c_gom_00101_general_n_Boss_Red | Boss | Technical | Red | 10,000 | 25 | 0.39 | 50 | 6 | 500 |
| c_gom_00101_general_n_Boss_Yellow | Boss | Technical | Yellow | 10,000 | 25 | 0.39 | 50 | 6 | 500 |
| c_gom_00101_general_h_Boss_Red | Boss | Technical | Red | 10,000 | 25 | 0.39 | 100 | 4 | 200 |
| c_gom_00101_general_h_Boss_Yellow | Boss | Technical | Yellow | 10,000 | 25 | 0.39 | 100 | 4 | 200 |
| c_gom_00101_general_vh_Boss_Red | Boss | Technical | Red | 10,000 | 25 | 0.39 | 100 | 4 | 200 |
| c_gom_00101_general_vh_Boss_Yellow | Boss | Technical | Yellow | 10,000 | 25 | 0.39 | 100 | 4 | 200 |

### キャラ（ボスとして登場）: chara_gom_00201（クロル）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| c_gom_00201_general_n_Boss_Yellow | Boss | Attack | Yellow | 10,000 | 50 | 0.60 | 50 | 6 | 500 |
| c_gom_00201_general_h_Boss_Yellow | Boss | Attack | Yellow | 10,000 | 50 | 0.60 | 100 | 5 | 200 |
| c_gom_00201_general_vh_Boss_Yellow | Boss | Attack | Yellow | 10,000 | 50 | 0.60 | 100 | 5 | 200 |

### 雑魚敵: enemy_gom_00301（キュイ）— Boss型

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_gom_00301_general_n_Boss_Yellow | Boss | Attack | Yellow | 1,000 | 34 | 0.11 | 50 | 1 | 100 |

### 雑魚敵: enemy_gom_00401（たこ焼きくん）/ enemy_gom_00402（たこ焼き）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_gom_00401_general_n_Boss_Colorless | Boss | Attack | Colorless | 1,000 | — | — | 50 | — | — |
| e_gom_00401_general_n_Boss_Yellow | Boss | Attack | Yellow | 1,000 | — | — | 50 | — | — |
| e_gom_00402_general_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | — | — | 50 | — | — |
| e_gom_00402_general_n_Normal_Yellow | Normal | Attack | Yellow | 1,000 | — | — | 50 | — | — |

> ※ 雑魚敵（e_gom_004xx系）の詳細パラメータは参照クエリ結果に含まれているが、代表値としてHPとattack_powerを記載。

### 雑魚敵: enemy_gom_00501（バタートースト）/ enemy_gom_00502（割きトースト）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_gom_00501_general_n_Normal_Colorless | Normal | Defense | Colorless | 1,000 | 34 | 0.11 | 50 | 1 | 100 |
| e_gom_00501_general_n_Normal_Yellow | Normal | Defense | Yellow | 1,000 | 34 | 0.11 | 50 | 1 | 100 |
| e_gom_00502_general_n_Normal_Colorless | Normal | Defense | Colorless | 1,000 | 34 | 0.11 | 50 | 1 | 100 |
| e_gom_00502_general_n_Normal_Yellow | Normal | Defense | Yellow | 1,000 | 34 | 0.11 | 50 | 1 | 100 |

### 雑魚敵: enemy_gom_00801（ライス）/ enemy_gom_00901（ライス (海苔)）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_gom_00801_general_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 34 | 0.11 | 50 | 1 | 100 |
| e_gom_00801_general_n_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 34 | 0.11 | 50 | 1 | 100 |
| e_gom_00901_general_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 34 | 0.11 | 50 | 1 | 100 |
| e_gom_00901_general_n_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 34 | 0.11 | 50 | 1 | 100 |

### 雑魚敵: enemy_gom_01001（あんぱん）/ enemy_gom_01002（トーストあんぱん）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_gom_01001_general_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 25 | 0.20 | 50 | 1 | 50 |
| e_gom_01001_general_n_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 25 | 0.20 | 50 | 1 | 50 |
| e_gom_01001_general_vh_Normal_Blue | Normal | Attack | Blue | 1,000 | 25 | 0.20 | 100 | 1 | 200 |
| e_gom_01001_general_vh_Normal_Red | Normal | Attack | Red | 1,000 | 25 | 0.20 | 100 | 1 | 200 |
| e_gom_01001_general_vh_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 25 | 0.20 | 100 | 1 | 200 |
| e_gom_01001_general_vh_Boss_Yellow | Boss | Attack | Yellow | 1,000 | 25 | 0.20 | 100 | 1 | 200 |
| e_gom_01002_general_n_Normal_Colorless | Normal | Defense | Colorless | 1,000 | 25 | 0.15 | 50 | 1 | 50 |
| e_gom_01002_general_n_Normal_Yellow | Normal | Defense | Yellow | 1,000 | 25 | 0.15 | 50 | 1 | 50 |
| e_gom_01002_general_vh_Normal_Blue | Normal | Defense | Blue | 1,000 | 25 | 0.15 | 100 | 1 | 50 |
| e_gom_01002_general_vh_Normal_Red | Normal | Defense | Red | 1,000 | 25 | 0.15 | 100 | 1 | 50 |
| e_gom_01002_general_vh_Normal_Yellow | Normal | Defense | Yellow | 1,000 | 25 | 0.15 | 100 | 1 | 50 |
| e_gom_01002_general_vh_Boss_Yellow | Boss | Defense | Yellow | 1,000 | 25 | 0.15 | 100 | 1 | 200 |

---

## コマライン（MstKomaLine）パターン

### normalブロックのコマ行数・アセット

| ingame_id | 行数 | コマ構成 |
|-----------|-----|---------|
| normal_gom_00001 | 2行 | 行1: gom_00001×1（width=1.0）、行2: gom_00001×2（0.4+0.6） |
| normal_gom_00002 | 2行 | 行1: gom_00001×2（0.6+0.4）、行2: gom_00001×2（0.5+0.5） |
| normal_gom_00003 | 2行 | 行1: gom_00001×2（0.5+0.5、SlipDamage=100）、行2: gom_00001×2（0.75+0.25、SlipDamage=100） |
| normal_gom_00004 | 2行 | 行1: gom_00002×3（0.33+0.34+0.33）、行2: gom_00002×1（width=1.0） |
| normal_gom_00005 | 3行 | 行1: gom_00002×2（0.4+0.6）、行2: gom_00002×1（width=1.0）、行3: gom_00002×2（0.6+0.4） |
| normal_gom_00006 | 3行 | 行1: gom_00002×3（0.25+0.5+0.25）、行2: gom_00002×2（0.5+0.5、SlipDamage=100）、行3: gom_00002×2（0.6+0.4、SlipDamage=100） |

> コマアセットは `gom_00001`（00001〜00003）と `gom_00002`（00004〜00006）の2種類。

### hardブロックのコマ行数・アセット

| ingame_id | 行数 | 主要コマ構成・エフェクト |
|-----------|-----|---------|
| hard_gom_00001 | 2行 | gom_00001（行1=1コマ、行2=2コマ/AttackPowerUp=10） |
| hard_gom_00002 | 2行 | gom_00001（行1=2コマ、行2=2コマ） |
| hard_gom_00003 | 2行 | gom_00001（行1=2コマ/SlipDamage=200、行2=2コマ/SlipDamage=200） |
| hard_gom_00004 | 2行 | gom_00002（行1=3コマ、行2=1コマ） |
| hard_gom_00005 | 2行 | gom_00002（行1=2コマ/SlipDamage=200、行2=1コマ） |
| hard_gom_00006 | 3行 | gom_00002（行1=3コマ/AttackPowerUp+SlipDamage、行2=3コマ/SlipDamage、行3=2コマ/SlipDamage） |

### veryhardブロックのコマ行数・アセット

| ingame_id | 行数 | 主要コマ構成・エフェクト |
|-----------|-----|---------|
| veryhard_gom_00001 | 2行 | gom_00003（行1=3コマ/AttackPowerDown=50+SlipDamage=400、行2=2コマ/AttackPowerDown=50+SlipDamage=400） |
| veryhard_gom_00002 | 2行 | gom_00003（行1=3コマ/SlipDamage=400、行2=2コマ/AttackPowerUp=10） |
| veryhard_gom_00003 | 2行 | gom_00003（行1=2コマ/AttackPowerDown=50+SlipDamage=400、行2=2コマ/SlipDamage=400+AttackPowerUp=10） |
| veryhard_gom_00004 | 3行 | gom_00003（行1=3コマ/Poison=750+SlipDamage=400、行2=2コマ/SlipDamage=400+Poison=750、行3=2コマ/SlipDamage=400） |
| veryhard_gom_00005 | 4行 | gom_00003（行1=2コマ/AttackPowerUp、行2=2コマ/SlipDamage=400、行3=3コマ/SlipDamage=400+AttackPowerDown、行4=1コマ） |
| veryhard_gom_00006 | 3行 | gom_00003（行1=3コマ/SlipDamage=400、行2=3コマ/SlipDamage+Gust=250+AttackPowerDown=50、行3=2コマ/SlipDamage=400） |

> veryhardには `gom_00003` アセットを使用。SlipDamage=400、Poison=750、AttackPowerDown=50、Gust=250 などの強力なエフェクトが登場。

---

## シーケンスパターン（MstAutoPlayerSequence）

### normalブロックのシーケンス概要

| ingame_id | シーケンス数 | 主要使用エネミー | enemy_hp_coef範囲 | enemy_attack_coef範囲 |
|-----------|------------|----------------|-----------------|---------------------|
| normal_gom_00001 | 2 | e_gom_00501、e_gom_00502 | 2〜9 | 0.8〜2.4 |
| normal_gom_00002 | 9（グループ切替含む） | e_gom_00402、e_gom_00401（Boss） | 2 | 2.4〜4 |
| normal_gom_00003 | 7 | e_gom_00301（Boss）、e_glo_00001 | 4〜12 | 1.6〜20 |
| normal_gom_00004 | 5 | e_gom_01001、e_gom_01002 | 3〜7.5 | 0.4〜2.6 |
| normal_gom_00005 | 14（グループ切替含む） | e_gom_00701（Boss）、e_gom_00801、e_gom_00901 | 1〜9 | 0.8〜4 |
| normal_gom_00006 | 19（グループ切替含む） | c_gom_00101/00201（キャラボス）、c_gom_00001（URボス）、多数の雑魚 | 1.5〜10 | 0.8〜6 |

#### normal_gom_00001 の詳細シーケンス

```
要素1: ElapsedTime=250 → SummonEnemy(e_gom_00502_general_n_Normal_Colorless, count=5, interval=300)
        enemy_hp_coef=2, enemy_attack_coef=2.4
要素2: ElapsedTime=800 → SummonEnemy(e_gom_00501_general_n_Normal_Colorless, count=5, interval=25)
        enemy_hp_coef=9, enemy_attack_coef=0.8
```

2シーケンス構成。割きトースト（e_gom_00502）が先に出て、バタートースト（e_gom_00501）がHP高めで後から召喚される。

#### normal_gom_00002 の詳細シーケンス

```
要素1: ElapsedTime=250 → SummonEnemy(e_gom_00402_general_n_Normal_Colorless, count=1)
        enemy_hp_coef=2, enemy_attack_coef=2.4
要素9: FriendUnitDead=1 → SwitchSequenceGroup(group1)
[group1]
要素2: ElapsedTimeSinceGroupActivated=0 → SummonEnemy(e_gom_00401_general_n_Boss_Colorless, count=1)
        enemy_hp_coef=2, enemy_attack_coef=4
要素3〜8: ElapsedTimeSinceGroupActivated=0〜200 → e_gom_00402繰り返し召喚
        enemy_hp_coef=2, enemy_attack_coef=2.4
```

たこ焼き（e_gom_00402）の死亡でグループ切替し、たこ焼きくん（e_gom_00401 Boss）が登場。

#### normal_gom_00003 の詳細シーケンス

```
要素1: ElapsedTime=0 → SummonEnemy(e_gom_00301_general_n_Boss_Yellow, count=1)
        enemy_hp_coef=4, enemy_attack_coef=1.6
要素2〜7: ElapsedTime=3200〜6100 → e_glo_00001_general_n_Normal_Colorless × 1〜1（計6体）
        enemy_hp_coef=12, enemy_attack_coef=20
```

> **重要**: normal_gom_00003 では `e_glo_00001`（GLO汎用敵）がnormalブロックで登場している（spy系とは異なる特徴）。

#### normal_gom_00004 の詳細シーケンス

```
要素1: ElapsedTime=150 → e_gom_01001（あんぱん）× 15, enemy_hp_coef=3, enemy_attack_coef=2.6
要素2: ElapsedTime=300 → e_gom_01002（トーストあんぱん）× 99（繰り返し）, enemy_hp_coef=7.5, enemy_attack_coef=0.4
要素3: ElapsedTime=200 → e_gom_01001 × 2, enemy_hp_coef=3, enemy_attack_coef=2.6
要素4: ElapsedTime=600 → e_gom_01002 × 15, enemy_hp_coef=7.5, enemy_attack_coef=0.4
要素5: ElapsedTime=450 → e_gom_01001 × 99（繰り返し）, enemy_hp_coef=3, enemy_attack_coef=2.6
```

あんぱんとトーストあんぱんのみで構成されたシンプルな2種敵交互編成。

#### normal_gom_00005 の詳細シーケンス

```
[初期]
要素1: ElapsedTime=0   → e_gom_00701（ラーメン）Boss × 1, enemy_hp_coef=2, enemy_attack_coef=4
要素2: ElapsedTime=50  → e_gom_00801（ライス）× 99, enemy_hp_coef=9, enemy_attack_coef=0.8
要素3: ElapsedTime=100 → e_gom_00901（ライス海苔）× 99, enemy_hp_coef=2, enemy_attack_coef=2.4
要素13: FriendUnitDead=1 → SwitchSequenceGroup(group1)

[group1] ラーメン死亡後
要素4: ElapsedTimeSinceGroupActivated=0   → e_gom_00701 Boss × 1
要素5〜7: ライス・ライス海苔繰り返し
要素14: FriendUnitDead=4 → SwitchSequenceGroup(group2)

[group2] ラーメン再度死亡後
要素8〜12: ラーメン+ライス+ライス海苔繰り返し
```

ラーメン（ボス型）が倒されるたびにグループが切り替わる3段階構成。

#### normal_gom_00006 の詳細シーケンス（最終ステージ）

```
[初期]
要素1: ElapsedTime=0    → c_gom_00101（トルチュール）Boss_Yellow × 1, hp_coef=2.5, atk_coef=2
要素2: ElapsedTime=3000 → c_gom_00201（クロル）Boss_Yellow × 1, hp_coef=2, atk_coef=6
要素3: ElapsedTime=3050 → e_gom_00301（キュイ）Boss × 1, hp_coef=8, atk_coef=0.8
要素4: ElapsedTime=500  → e_gom_00701（ラーメン）Boss × 1, hp_coef=1.5, atk_coef=4
要素5〜8: バタートースト/割きトースト繰り返し
要素9〜11: あんぱん/トーストあんぱん繰り返し
要素19: FriendUnitDead=2 → SwitchSequenceGroup(group1)

[group1] キャラ系2体死亡後
要素12: c_gom_00001（姫様 URキャラ）Boss_Yellow × 1, hp_coef=10, atk_coef=1.6
要素13: e_gom_00401（たこ焼きくん）Boss × 1
要素14〜18: たこ焼き系繰り返し
```

> normalの最終ステージでURキャラ（chara_gom_00001 囚われの王女 姫様）がボスとして登場する。

---

## シーケンスパターン（hard/veryhard）

### hardブロックのシーケンス概要

| ingame_id | シーケンス数 | 主要enemy_hp_coef範囲 | e_glo_00001使用 |
|-----------|------------|---------------------|----------------|
| hard_gom_00001 | 6 | 14〜39 | あり（hp_coef=14） |
| hard_gom_00002 | 13（グループ切替） | 10〜18 | あり（hp_coef=13） |
| hard_gom_00003 | 13 | 10〜21 | あり（hp_coef=10〜21） |
| hard_gom_00004 | — | — | — |
| hard_gom_00005 | — | — | — |
| hard_gom_00006 | — | — | — |

hardの enemy_hp_coef は 10〜39 程度（normal比で約5〜10倍）。

### veryhardブロックのシーケンス概要

| ingame_id | シーケンス数 | 主要enemy_hp_coef範囲 | e_glo_00001使用 |
|-----------|------------|---------------------|----------------|
| veryhard_gom_00001 | 13 | 30〜600 | あり（hp_coef=35） |
| veryhard_gom_00002 | 25以上（グループ切替） | 10〜38 | なし |
| veryhard_gom_00003 | — | — | — |
| veryhard_gom_00004 | — | — | — |
| veryhard_gom_00005 | — | — | — |
| veryhard_gom_00006 | — | — | — |

veryhard の e_gom_01001/01002_general_vh_Boss_Yellow は hp_coef=420〜600 に達する。

---

## コンテンツ種別ごとの特徴比較

### アウトポストHP比較

| 種別 | アウトポストHP |
|-----|-------------|
| normal | 5,000 |
| hard | 50,000 |
| veryhard | 100,000〜150,000 |

### エネミー難易度係数（enemy_hp_coef）比較

| 種別 | enemy_hp_coef（典型値） |
|-----|----------------------|
| normal | 2〜9（初期）〜10（終盤） |
| hard | 13〜39 |
| veryhard | 30〜420（ノーマル敵30〜50、ボス型420〜600） |

### 使用エネミーキャラの違い

| 種別 | 主力雑魚 | ボス役 | 汎用補助敵 |
|-----|---------|-------|---------|
| normal | e_gom_00501/502、e_gom_00402、e_gom_00801/901等（ステージ毎に異なる） | e_gom_00301/401/701（中間ボス）、c_gom_00101/201（キャラボス）、c_gom_00001（URボス） | e_glo_00001（normal_gom_00003で登場） |
| hard | e_gom_00402/502等 | e_gom_00401（Boss） | e_glo_00001（多数） |
| veryhard | e_gom_00402/01001/01002等 | e_gom_00401（Boss）/ e_gom_01001/01002（Boss） | e_glo_00001（あり） |

> **重要**: gom作品では `e_glo_00001`（GLO汎用敵）が **normal_gom_00003 でも登場する**（spy系ではhard以降のみ）。これはgom固有の仕様。

### BGM（bgm_asset_key）パターン

| 用途 | BGM |
|-----|-----|
| 通常バトル（normal/hard/veryhard、全件） | SSE_SBG_003_006 |

> spyが `SSE_SBG_003_002` を使用するのに対し、gomは全件 `SSE_SBG_003_006` を使用。

### loop_background_asset_key（背景アセット）パターン

- normalブロック:
  - `gom_00001`: normal_gom_00001〜00003（序盤3ステージ）
  - `gom_00002`: normal_gom_00004〜00006（終盤3ステージ）
- hardブロック: 全件空白（背景アセットなし）
- veryhardブロック: 全件空白

### コマアセットパターン

| 種別 | 使用アセット |
|-----|------------|
| normal（00001〜00003） | `gom_00001` |
| normal（00004〜00006）/ hard | `gom_00002` |
| veryhard | `gom_00003` |

> gomはコンテンツ難易度に応じて3種類のコマアセットを使い分ける明確なルールがある。

### コマエフェクト（KomaLine）のパターン

normalブロック:
- `None`（エフェクトなし）: 行1が中心
- `SlipDamage=100`（スリップダメージ100）: normal_gom_00003、00006の後半行

hardブロック:
- `None`
- `AttackPowerUp=10`（攻撃力10%アップ/プレイヤー側）
- `SlipDamage=200`（スリップダメージ200）

veryhardブロック:
- `None`
- `AttackPowerDown=50`（攻撃力50%ダウン/プレイヤー側）
- `SlipDamage=400`（スリップダメージ400）
- `Poison=750`（毒ダメージ750、interval=3秒）
- `AttackPowerUp=10`
- `Gust=250`（突風250）

---

## dungeon（限界チャレンジ）参照情報

> ※ dungeon用のデータは現時点でMstInGameに存在しない（生成対象）。
> 以下は既存のnormalブロックパターンからdungeon向けパラメータを推定するための参考情報。

### dungeonブロック仕様（CLAUDE.mdより）

| 種別 | インゲームID例 | MstEnemyOutpost HP | コマ行数 | ボスの有無 |
|------|--------------|-------------------|---------|----------|
| normal | `dungeon_gom_normal_00001` | 100（固定） | 3行（固定） | なし |
| boss | `dungeon_gom_boss_00001` | 1,000（固定） | 1行（固定） | あり |

### dungeon normalブロックの推奨パラメータ（参考）

| 項目 | 推奨値 | 根拠 |
|-----|--------|------|
| BGM | `SSE_SBG_003_006` | gom全件で使用されているBGM |
| loop_background_asset_key | `gom_00001` | normalブロックの序盤で使用される背景 |
| player_outpost_asset_key | `gom_ally_0001` | normalブロックで使用 |
| コマアセット | `gom_00001` | normal序盤（00001〜00003）で使用 |
| コマ行数 | 3行（固定） | dungeonブロック仕様 |
| コマエフェクト | `None`（全行） | normalの初期ステージ（dungeon向けに簡素に） |

### dungeon normalブロックの推奨エネミー・シーケンス参考

gom専用雑魚の中で、dungeon normalブロック向けに推奨する雑魚敵:

**推奨メイン雑魚**: `enemy_gom_00501`（バタートースト）または `enemy_gom_00801`（ライス）
- 理由: normal_gom_00001/00005で使われる代表的な雑魚敵
- move_speed: 34（標準的な速度）
- HP: 1,000（Base）
- attack_power: 50（Base）

**推奨サブ雑魚**: `enemy_gom_00502`（割きトースト）または `enemy_gom_00901`（ライス(海苔)）
- 理由: メイン雑魚とセットで使われる補助雑魚

**enemy_hp_coef参考**: dungeon normalは小規模なので 1.5〜3 程度を想定

シーケンス参考（normal_gom_00001パターン — 最もシンプル）:
```
要素1: ElapsedTime=250 → SummonEnemy(e_gom_00502_general_n_Normal_Colorless, count=5, interval=300)
        enemy_hp_coef=1.5程度, enemy_attack_coef=1〜2
要素2: ElapsedTime=800 → SummonEnemy(e_gom_00501_general_n_Normal_Colorless, count=5, interval=25)
        enemy_hp_coef=1.5〜3程度, enemy_attack_coef=1〜2
```

### dungeon bossブロックの推奨パラメータ（参考）

| 項目 | 推奨値 | 根拠 |
|-----|--------|------|
| BGM | `SSE_SBG_003_006` | 共通 |
| コマアセット | `gom_00001` または `gom_00002` | normalで使用する背景 |
| コマ行数 | 1行（固定） | dungeonブロック仕様 |
| ボスエネミー | `c_gom_00001_general_n_Boss_Yellow`（囚われの王女 姫様） | URキャラ（dungeon指定） |

---

## まとめ・パターン特徴

### 雑魚敵の使用ルール

1. gom作品の雑魚敵はユニークなキャラクター名（食べ物系）が多く、ステージごとに異なる雑魚敵を使用する多様な構成
2. normal各ステージで使用する雑魚敵の組み合わせ:
   - 00001: バタートースト + 割きトースト
   - 00002: たこ焼き + たこ焼きくん（Boss）
   - 00003: キュイ（Boss）+ e_glo_00001（汎用敵）
   - 00004: あんぱん + トーストあんぱん
   - 00005: ラーメン（Boss）+ ライス + ライス(海苔)
   - 00006: キャラボス複数 + 多種雑魚（最終ステージ）
3. **enemy_gom_00xxx のgeneralパラメータ命名規則**: `e_{id}_general_{難易度}_{unit_kind}_{color}`
4. dungeon向けには `enemy_gom_00501`（バタートースト）と `enemy_gom_00502`（割きトースト）または `enemy_gom_00801/00901` が適切

### ボスエネミーのキャラ

normalの最終ステージ（normal_gom_00006）でURキャラを含むキャラが全員登場:
- `c_gom_00101_general_n_Boss_Yellow` — トーチャー・トルチュール（HP係数2.5）
- `c_gom_00201_general_n_Boss_Yellow` — クロル（HP係数2）
- `c_gom_00001_general_n_Boss_Yellow` — 囚われの王女 姫様（HP係数10、group1で登場）

### gom作品の固有特徴

1. **BGM**: `SSE_SBG_003_006`（spy=`003_002`とは異なる作品専用BGM）
2. **e_glo_00001がnormalで登場**: normal_gom_00003でGLO汎用敵がhp_coef=12、attack_coef=20で登場（スパイとの違い）
3. **コマアセット3段階**: gom_00001（normal序盤）→ gom_00002（normal後半/hard）→ gom_00003（veryhard）
4. **SlipDamageがnormalから登場**: normal_gom_00003/00006でSlipDamage=100のエフェクトが既に使用される
5. **veryhard HP**: 100,000〜150,000（spyより一部低い: 00001と00005が100,000）

### シーケンスのトリガー種別（確認分）

- `ElapsedTime` — 経過時間トリガー
- `ElapsedTimeSinceSequenceGroupActivated` — グループ活性化からの経過時間
- `FriendUnitDead` — 味方ユニット死亡トリガー → `SwitchSequenceGroup` でグループ切替
- `InitialSummon` — 初期召喚（veryhard_gom_00002で確認）
- `OutpostHpPercentage` — アウトポストHP割合トリガー（hard/veryhardで確認）
- `FriendUnitDead` → `SummonEnemy`（veryhard_gom_00001で確認: 死亡数でトリガー）

### dungeon生成時の留意点

1. **normalブロック（3行固定）** は `normal_gom_00001`（シンプル2種雑魚）が最も参考になる構成
2. **BGM**: `SSE_SBG_003_006`（gom作品専用BGM、spyの003_002とは異なる）
3. **背景**: `gom_00001`（normalブロック序盤の背景）
4. **使用雑魚**: `enemy_gom_00501`（バタートースト）または `enemy_gom_00801`（ライス）をメインに推奨
5. **enemy_hp_coef**: dungeonはHP=100固定で非常に小さいため、1.5〜3程度を目安に
6. **GLO汎用敵の扱い**: gom作品はnormalでもe_glo_00001を使うが、dungeonでは作品専用雑魚のみ使用することを推奨
7. **コマアセット**: `gom_00001` を使用（dungeon normalブロック向け）
