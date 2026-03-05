# サマータイムレンダ インゲームパターン分析

## 概要

- series_id: sum
- URキャラ: chara_sum_00101 (影のウシオ 小舟 潮 / Blue / Support)
- インゲームコンテンツ数: 18件（normal x6, hard x6, veryhard x6）
- dungeonコンテンツ: 現時点では存在しない（未生成）
- BGM: SSE_SBG_003_003（全コンテンツ共通）

---

## コンテンツ種別一覧

### normal（通常）

| ingame_id | loop_background_asset_key | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef | boss_enemy_hp_coef | boss_enemy_attack_coef |
|-----------|---------------------------|------------|----------------------|--------------------------|--------------------|-----------------------|
| normal_sum_00001 | sum_00003 | 60,000 | 2.0 | 1.5 | 1.5 | 1.5 |
| normal_sum_00002 | glo_00004 | 62,000 | 2.0 | 2.0 | 1.5 | 2.0 |
| normal_sum_00003 | sum_00003 | 64,000 | 2.0 | 2.0 | 2.0 | 2.0 |
| normal_sum_00004 | sum_00002 | 66,000 | 2.0 | 2.0 | 2.0 | 2.0 |
| normal_sum_00005 | sum_00005 | 68,000 | 2.2 | 2.2 | 2.5 | 2.5 |
| normal_sum_00006 | sum_00003 | 70,000 | 2.0 | 2.0 | 2.0 | 2.0 |

> normalコンテンツは全てSSE_SBG_003_003、boss_bgm_asset_keyは空（ボスなし）

### hard（難）

hard_sum_00001〜00005はnormalのシーケンスを流用し、coef値で難易度を上げる設計。
hard_sum_00006のみ独自シーケンスあり。

| ingame_id | mst_auto_player_sequence_id（参照先） | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef | boss_enemy_hp_coef | boss_enemy_attack_coef |
|-----------|--------------------------------------|------------|----------------------|--------------------------|--------------------|-----------------------|
| hard_sum_00001 | normal_sum_00001 | 120,000 | 3.0 | 3.5 | 3.0 | 3.0 |
| hard_sum_00002 | normal_sum_00002 | 125,000 | 2.8 | 3.5 | 3.0 | 3.0 |
| hard_sum_00003 | normal_sum_00003 | 130,000 | 3.0 | 3.5 | 3.0 | 3.0 |
| hard_sum_00004 | normal_sum_00004 | 135,000 | 4.0 | 3.5 | 3.0 | 2.5 |
| hard_sum_00005 | normal_sum_00005 | 140,000 | 4.0 | 4.0 | 4.0 | 4.0 |
| hard_sum_00006 | hard_sum_00006（独自） | 145,000 | 1.0 | 1.0 | 1.0 | 1.0 |

> hardは背景アセット（loop_background_asset_key）が空（normalと同じ背景を使用と思われる）

### veryhard（超難）

veryhard_sum_00001〜00006は全て独自シーケンス。coef値は全て1.0（sum_vh系専用パラメータを参照するため）。

| ingame_id | outpost_hp | normal_enemy_hp_coef | boss_enemy_hp_coef |
|-----------|------------|----------------------|--------------------|
| veryhard_sum_00001 | 250,000 | 1.0 | 1.0 |
| veryhard_sum_00002 | 255,000 | 1.0 | 1.0 |
| veryhard_sum_00003 | 260,000 | 1.0 | 1.0 |
| veryhard_sum_00004 | 265,000 | 1.0 | 1.0 |
| veryhard_sum_00005 | 270,000 | 1.0 | 1.0 |
| veryhard_sum_00006 | 275,000 | 1.0 | 1.0 |

> veryhardはcoef値が1.0なのは、シーケンスで参照するパラメータID自体が高HPに設定されているため（後述）

---

## エネミーID → 日本語名対応表

| asset_key | 日本語名 | 備考 |
|-----------|---------|------|
| chara_sum_00001 | 網代 慎平 | メインキャラ |
| chara_sum_00101 | 影のウシオ 小舟 潮 | URキャラ |
| chara_sum_00201 | 小舟 澪 | サブキャラ |
| enemy_sum_00001 | 影 | メイン雑魚 |
| enemy_sum_00101 | 小舟 澪の影 (拳銃) | サブ雑魚 |
| enemy_sum_00201 | 小舟 澪の影 (包丁) | サブ雑魚 |

---

## エネミー別パラメータ詳細

### generalエネミー（normal/hard 共通で使用）

**メイン雑魚: enemy_sum_00001（影）**

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| e_sum_00001_general_Normal_Colorless | Normal | Defense | Colorless | 15,000 | 200 | 40 | 1 |
| e_sum_00001_general_Normal_Blue | Normal | Defense | Blue | 18,000 | 400 | 40 | 1 |
| e_sum_00001_general_Normal_Yellow | Normal | Defense | Yellow | 26,000 | 300 | 40 | 1 |
| e_sum_00001_general_Normal_Red | Normal | Defense | Red | 80,000 | 500 | 40 | 1 |
| e_sum_00001_general_Boss_Red | Boss | Defense | Red | 350,000 | 600 | 40 | 1 |

**サブ雑魚: enemy_sum_00101（小舟 澪の影 / 拳銃）**

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| e_sum_00101_general_Boss_Yellow | Boss | Attack | Yellow | 200,000 | 500 | 45 | 1 |

**サブ雑魚: enemy_sum_00201（小舟 澪の影 / 包丁）**

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| e_sum_00201_general_Boss_Blue | Boss | Attack | Blue | 300,000 | 800 | 45 | 1 |

### キャラエネミー（normal/hard で使用）

**chara_sum_00201（小舟 澪）**

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| c_sum_00201_general_Normal_Colorless | Normal | Attack | Colorless | 70,000 | 490 | 50 | 4 |
| c_sum_00201_general_Normal_Yellow | Normal | Attack | Yellow | 270,000 | 1,050 | 50 | 4 |
| c_sum_00201_general_Boss_Blue | Boss | Attack | Blue | 400,000 | 1,400 | 60 | 4 |
| c_sum_00201_general2_Normal_Colorless | Normal | Attack | Colorless | 70,000 | 500 | 50 | 7 |
| c_sum_00201_general2_Normal_Red | Normal | Attack | Red | 140,000 | 700 | 50 | 5 |

**chara_sum_00001（網代 慎平）**

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| c_sum_00001_general_Normal_Red | Normal | Technical | Red | 245,000 | 500 | 45 | 5 |

**chara_sum_00101（影のウシオ 小舟 潮 / URキャラ）**

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| c_sum_00101_general_Boss_Red | Boss | Support | Red | 190,000 | 600 | 40 | 7 |

### veryhardエネミー（sum_vh系）

**enemy_sum_00001（sum_vh系）**

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed |
|----|---------------------|-----------|-------|-----|--------------|------------|
| e_sum_00001_general_sum_vh_Normal_Red | Normal | Attack | Red | 30,000 | 600 | 45 |
| e_sum_00001_general_sum_vh_Normal_Blue | Normal | Attack | Blue | 38,000 | 500 | 45 |
| e_sum_00001_general_sum_vh_Normal_Green | Normal | Attack | Green | 21,000 | 800 | 45 |
| e_sum_00001_general_sum_vh_Normal_Yellow | Normal | Attack | Yellow | 45,000 | 400 | 45 |
| e_sum_00001_general_sum_vh_big_Normal_Blue | Normal | Attack | Blue | 150,000 | 700 | 50 |
| e_sum_00001_general_sum_vh_big_Normal_Yellow | Normal | Attack | Yellow | 200,000 | 700 | 50 |

**enemy_sum_00101（sum_vh系）**

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed |
|----|---------------------|-----------|-------|-----|--------------|------------|
| e_sum_00101_general_sum_vh_Normal_Green | Normal | Attack | Green | 200,000 | 700 | 55 |
| e_sum_00101_general_sum_vh_Boss_Blue | Boss | Attack | Blue | 800,000 | 1,700 | 55 |

**enemy_sum_00201（sum_vh系）**

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed |
|----|---------------------|-----------|-------|-----|--------------|------------|
| e_sum_00201_general_sum_vh_Normal_Yellow | Normal | Attack | Yellow | 500,000 | 1,200 | 55 |
| e_sum_00201_general_sum_vh_Boss_Yellow | Boss | Attack | Yellow | 500,000 | 1,200 | 55 |

### veryhardキャラエネミー（sum_vh系）

| id | mst_enemy_character_id | character_unit_kind | role_type | color | hp | attack_power |
|----|------------------------|---------------------|-----------|-------|-----|--------------|
| c_sum_00001_general_sum_vh2_Normal_Blue | chara_sum_00001 | Normal | Attack | Blue | 400,000 | 900 |
| c_sum_00001_general_sum_vh_Normal_Green | chara_sum_00001 | Normal | Attack | Green | 400,000 | 1,300 |
| c_sum_00101_general_sum_vh_Boss_Blue | chara_sum_00101（URキャラ） | Boss | Support | Blue | 500,000 | 400 |
| c_sum_00101_general_sum_vh_Boss_Yellow | chara_sum_00101（URキャラ） | Boss | Support | Yellow | 500,000 | 400 |
| c_sum_00201_general_sum_vh_Normal_Blue | chara_sum_00201 | Normal | Attack | Blue | — | — |
| c_sum_00201_general_sum_vh_Boss_Blue | chara_sum_00201 | Boss | Attack | Blue | 1,000,000 | 2,500 |
| c_sum_00201_general_sum_vh2_Boss_Green | chara_sum_00201 | Boss | Defense | Green | 750,000 | 1,100 |
| c_sum_00201_general_sum_vh3_Normal_Green | chara_sum_00201 | Normal | Defense | Green | 200,000 | 1,100 |

### GLO汎用エネミー（veryhardで使用）

| id | mst_enemy_character_id | color | hp | attack_power | move_speed |
|----|------------------------|-------|-----|--------------|------------|
| e_glo_00001_general_sum_vh_Normal_Yellow | enemy_glo_00001 | Yellow | — | — | — |
| e_glo_00001_general_sum_vh_Normal_Green | enemy_glo_00001 | Green | — | — | — |
| e_glo_00001_general_sum_vh_Normal_Blue | enemy_glo_00001 | Blue | — | — | — |
| e_glo_00001_general_sum_vh_Normal_Red | enemy_glo_00001 | Red | — | — | — |
| e_glo_00001_general_sum_vh_big_Normal_Blue | enemy_glo_00001 | Blue | — | — | — |
| e_glo_00001_general_sum_vh_big_Normal_Red | enemy_glo_00001 | Red | — | — | — |
| e_glo_00001_general_sum_vh_big_Normal_Green | enemy_glo_00001 | Green | — | — | — |

> GLO汎用エネミーのパラメータはMstEnemyStageParameterのsum_vhフィルタでは取得されず、glo系のパラメータIDを参照していると推定される

---

## シーケンスパターン（MstAutoPlayerSequence）

### normal_sum_00001（3イベント / c_sum_00201（小舟 澪）中心）

- 使用エネミー: `c_sum_00201_general_Normal_Colorless`（初期2体登場）→ `c_sum_00201_general_Normal_Yellow`（後半）
- 進行パターン:
  1. InitialSummon(2): Colorless x1（Boss aura, position 1.8）
  2. FriendUnitDead(1): Colorless x1（Boss aura）
  3. FriendUnitDead(2): Yellow x1（Boss aura）

### normal_sum_00002（10イベント / e_sum_00001（影）中心）

- 使用エネミー: `e_sum_00001_general_Normal_Colorless`（序盤）→ Yellow（中盤）→ `e_sum_00101_general_Boss_Yellow`（後半ボス）
- 進行パターン:
  1. ElapsedTime(200): Colorless x1
  2. ElapsedTime(500): Colorless x1
  3. ElapsedTime(1000): Colorless x1
  4. FriendUnitDead(3): Yellow x3（間隔400）
  5. ElapsedTime(2200): Yellow x2（間隔600）
  6. ElapsedTime(2600): Colorless x99（間隔700、量産型）
  7. ElapsedTime(3700): Red x2（間隔50）
  8. ElapsedTime(4000): `e_sum_00101_general_Boss_Yellow` x1
  9. ElapsedTime(5000): Yellow x99（間隔1400）
  10. OutpostDamage(1): Yellow x99（間隔1000、OutpostへのリーチでLoop）

### normal_sum_00003（4イベント / c_sum_00201（小舟 澪）フル展開）

- 使用エネミー: `c_sum_00201_general_Normal_Colorless`（初期）→ Yellow → Boss_Blue（最終）
- 進行パターン:
  1. InitialSummon(2): Colorless x1（Boss aura, position 1.7）
  2. FriendUnitDead(1): Colorless x1（Boss aura）
  3. FriendUnitDead(2): Yellow x1（Boss aura）
  4. FriendUnitDead(3): `c_sum_00201_general_Boss_Blue` x1

### normal_sum_00004（10イベント / e_sum_00001 + e_sum_00201_Boss_Blue）

- 使用エネミー: `e_sum_00001_general_Normal_Colorless`（序盤）→ Blue（中盤）→ `e_sum_00201_general_Boss_Blue`（後半ボス）
- 進行パターン:
  1. ElapsedTime(200): Colorless x1
  2. ElapsedTime(500): Colorless x1
  3. ElapsedTime(1000): Colorless x1
  4. FriendUnitDead(3): Blue x3（間隔400）
  5. ElapsedTime(2200): Blue x2（間隔400）
  6. ElapsedTime(2600): Colorless x99（間隔700）
  7. ElapsedTime(3700): `e_sum_00201_general_Boss_Blue` x1
  8. ElapsedTime(3900): Red x2（間隔50）
  9. ElapsedTime(5000): Blue x99（間隔1400）
  10. OutpostDamage(1): Blue x99（間隔1000）

### normal_sum_00005（14イベント / e_sum_00001 大量投入 + Boss_Red登場）

- 使用エネミー: `e_sum_00001_general_Normal_Colorless`（序盤大量）→ Blue（中盤）→ `e_sum_00001_general_Boss_Red`（最終ボス）
- 総イベント数: 14（最も複雑なnormal）
- 進行パターン:
  1. InitialSummon(2): Colorless x1（position 2.8）
  2. InitialSummon(2): Colorless x1（position 2.7）
  3. FriendUnitDead(1): Colorless x3（間隔700）
  4. FriendUnitDead(2): Colorless x3（間隔700）
  5. ElapsedTime(2600): Colorless x1
  6. ElapsedTime(2700): Colorless x1
  7. ElapsedTime(2800): Blue x99（間隔1500）
  8. FriendUnitDead(5): Blue x1（Fall, position 2.8）
  9. FriendUnitDead(5): Blue x1（Fall, position 2.7）
  10. FriendUnitDead(5): Colorless x99（Fall, position 2.9、間隔600）
  11. FriendUnitDead(8): Red x1（Fall, position 2.8）
  12. FriendUnitDead(8): Red x1（Fall, position 2.7）
  13. FriendUnitDead(9): `e_sum_00001_general_Boss_Red` x1（Fall, position 2.9）
  14. OutpostDamage(1): Blue x99（間隔700）

### normal_sum_00006（4イベント / キャラ複数連続登場 + URキャラ）

- 使用エネミー: `c_sum_00201_general2_Normal_Colorless` → `c_sum_00201_general2_Normal_Red` → `c_sum_00001_general_Normal_Red` + `c_sum_00101_general_Boss_Red`（URキャラ）
- 進行パターン:
  1. InitialSummon(2): c_sum_00201_general2_Normal_Colorless x1（Boss aura, position 2.8）
  2. FriendUnitDead(1): c_sum_00201_general2_Normal_Red x1（Boss aura）
  3. FriendUnitDead(2): c_sum_00001_general_Normal_Red x1（Fall, Boss aura, position 2.9）
  4. FriendUnitDead(2): `c_sum_00101_general_Boss_Red`（URキャラ）x1（Fall, position 2.8）

---

### hard_sum_00006（独自シーケンス / 4イベント）

hard_sum_00001〜00005はnormalのシーケンスを参照（coef値で難易度を上げる）。
hard_sum_00006のみ独自シーケンスを持つ（normal_sum_00006とほぼ同じ構成）。

- 進行パターン:
  1. InitialSummon(2): c_sum_00201_general2_Normal_Colorless x1（Boss aura, position 2.8）
  2. FriendUnitDead(1): c_sum_00201_general2_Normal_Red x1（Boss aura）
  3. FriendUnitDead(2): c_sum_00001_general_Normal_Red x1（Fall, Boss aura, position 2.9）
  4. FriendUnitDead(2): c_sum_00101_general_Boss_Red（URキャラ）x1（Fall, position 2.8）

---

### veryhard_sum_00001（20イベント）

- メインエネミー: `e_glo_00001_general_sum_vh_Normal_Yellow`（GLO汎用）
- サブエネミー: `e_glo_00001_general_sum_vh_big_Normal_Blue`（大型Boss aura）
- 中間キャラ: `c_sum_00201_general_sum_vh_Normal_Blue`（FriendUnitDead1/3/6/13）
- 最終ボス: `c_sum_00201_general_sum_vh_Boss_Blue`（FriendUnitDead10）
- 特記:
  - OutpostDamage(1)でbig_Blue x2連続（ループ延長）
  - FriendUnitDead(11): Yellow x99（間隔500、量産ループ）

### veryhard_sum_00002（19イベント）

- メインエネミー: `e_glo_00001_general_sum_vh_Normal_Yellow`（GLO汎用）＋ `e_sum_00001_general_sum_vh_Normal_Red`
- 特殊大型: `e_sum_00001_general_sum_vh_big_Normal_Yellow`（FriendUnitDead3/3/10/10/15/16）
- 最終ボス: `e_sum_00201_general_sum_vh_Boss_Yellow`（FriendUnitDead10）
- 特記:
  - OutpostHpPercentage(50/60)でbig_Yellow登場
  - FriendUnitDead(13): Yellow/Red/Blue x99（各間隔1000/1200/700、量産ループ）

### veryhard_sum_00003（23イベント、最大規模）

- メインエネミー: `e_glo_00001_general_sum_vh_Normal_Blue`（GLO汎用）＋ `e_sum_00001_general_sum_vh_Normal_Green`
- 大型: `e_sum_00001_general_sum_vh_big_Normal_Blue`（FriendUnitDead8/8/10/11/14/15/16/17/19）
- GLO大型: `e_glo_00001_general_sum_vh_big_Normal_Red`（ElapsedTime3500/FriendUnitDead8/9）
- 最終ボス: `e_sum_00101_general_sum_vh_Boss_Blue`（FriendUnitDead14/15/16/17）
- 特記:
  - OutpostHpPercentage(50)でbig_Blue x2
  - FriendUnitDead(14): Blue/Green x99（各間隔900/700、量産ループ）

### veryhard_sum_00004（18イベント）

- メインエネミー: `e_glo_00001_general_sum_vh_Normal_Red` + `e_glo_00001_general_sum_vh_Normal_Blue`（GLO汎用）
- サブキャラ: `c_sum_00201_general_sum_vh3_Normal_Green`（FriendUnitDead1）
- 大型: `e_glo_00001_general_sum_vh_big_Normal_Green`（FriendUnitDead6/6/11/13/14/15、AdventBoss1 aura）
- 最終ボス: `c_sum_00201_general_sum_vh2_Boss_Green` + `c_sum_00001_general_sum_vh_Normal_Green`（FriendUnitDead8同時登場）
- 特記:
  - OutpostHpPercentage(50)でbig_Green x2（AdventBoss1 aura）
  - FriendUnitDead(12): Red/Blue/Red x99（各間隔1000/1200/800）

### veryhard_sum_00005（22イベント）

- メインエネミー: `e_glo_00001_general_sum_vh_Normal_Yellow`（GLO汎用）＋ `e_sum_00001_general_sum_vh_Normal_Green`
- 中間キャラ: `e_sum_00101_general_sum_vh_Normal_Green`（FriendUnitDead4）
- 大型: `e_sum_00001_general_sum_vh_big_Normal_Yellow`（FriendUnitDead11/11/13/14/16/17）
- GLO大型: `e_glo_00001_general_sum_vh_big_Normal_Green`（FriendUnitDead8/16）
- 中間ボス: `e_sum_00201_general_sum_vh_Normal_Yellow`（FriendUnitDead7）
- 最終ボス: `c_sum_00101_general_sum_vh_Boss_Yellow`（URキャラ / FriendUnitDead11）
- 特記:
  - OutpostDamage(1/2000)でbig_Yellow x2
  - FriendUnitDead(13): Green x99（間隔900）
  - ElapsedTime(500): GLO_Green x10（間隔1000）

### veryhard_sum_00006（19イベント）

- メインエネミー: `e_glo_00001_general_sum_vh_Normal_Yellow` + Green（GLO汎用）＋ `e_sum_00001_general_sum_vh_Normal_Blue` + Yellow
- 大型: `e_sum_00001_general_sum_vh_big_Normal_Yellow`（FriendUnitDead8/9/10/11/13/18/19）
- GLO大型: `e_glo_00001_general_sum_vh_big_Normal_Blue`（ElapsedTime1900）
- 最終ボス: `c_sum_00001_general_sum_vh2_Normal_Blue` + `c_sum_00101_general_sum_vh_Boss_Blue`（URキャラ / OutpostDamage(1)で同時登場）
- 特記:
  - OutpostHpPercentage(50)でbig_Yellow
  - FriendUnitDead(14): Yellow x99（間隔900）+ Blue x99（間隔1200）

---

## コンテンツ種別ごとの特徴比較

### OutpostHP（アウトポストHP）スケール

| 種別 | 範囲 | 増加幅 |
|------|------|--------|
| normal | 60,000 ～ 70,000 | +2,000/ステージ |
| hard | 120,000 ～ 145,000 | +5,000/ステージ |
| veryhard | 250,000 ～ 275,000 | +5,000/ステージ |

### 雑魚エネミーHP（normalコンテンツのbase値 / coef適用前）

| エネミーID | Colorless | Blue | Yellow | Red | move_speed |
|-----------|-----------|------|--------|-----|------------|
| enemy_sum_00001（影） | 15,000 | 18,000 | 26,000 | 80,000 | 40 |

> coef=2.0適用後の実効HP: Colorless 30,000 / Blue 36,000 / Yellow 52,000 / Red 160,000

### ボスエネミーHP比較

| エネミー | normal使用コンテンツ | HP |
|---------|--------------------|----|
| e_sum_00101_general_Boss_Yellow | normal_sum_00002 | 200,000 |
| e_sum_00201_general_Boss_Blue | normal_sum_00004 | 300,000 |
| e_sum_00001_general_Boss_Red | normal_sum_00005 | 350,000 |
| c_sum_00201_general_Boss_Blue | normal_sum_00003 | 400,000 |
| c_sum_00101_general_Boss_Red（URキャラ） | normal_sum_00006 | 190,000 |

### シーケンスパターンの違い

| 種別 | シーケンス複雑度 | 特記事項 |
|------|----------------|---------|
| normal | 3〜14イベント | sum_00001/00003/00006はキャラ中心の小規模、00002/00004は雑魚10イベント |
| hard | 独自シーケンスは00006のみ | normalと同じシーケンスを参照し、coef値で強化 |
| veryhard | 18〜23イベント（全独自） | sum_vh専用パラメータ使用、GLO汎用敵混在、OutpostHP%トリガー多用 |

### 使用エネミー比較

| エネミーカテゴリ | normal | hard | veryhard |
|----------------|--------|------|----------|
| enemy_sum_00001（影） | 使用（Colorless/Blue/Yellow/Red/Boss_Red） | 流用 | 使用（sum_vh系/big版） |
| enemy_sum_00101 | 使用（Boss_Yellow / 00002） | 流用 | 使用（sum_vh_Boss_Blue / 00003/00005） |
| enemy_sum_00201 | 使用（Boss_Blue / 00004） | 流用 | 使用（sum_vh_Boss_Yellow） |
| chara_sum_00201 | 使用（Normal/Boss / 00001/00003/00006） | 流用+独自 | 使用（sum_vh_Boss_Blue, sum_vh2_Boss_Green） |
| chara_sum_00001 | 使用（Normal_Red / 00006） | 流用 | 使用（sum_vh2_Normal_Blue） |
| chara_sum_00101（URキャラ） | 使用（Boss_Red / 00006） | 流用 | 使用（sum_vh_Boss_Blue/Yellow） |
| enemy_glo_00001 | 未使用 | 未使用 | 使用（sum_vh系、全veryhard） |

---

## dungeon（限界チャレンジ）向けの推奨パラメータ

### 現状

dungeon_sum_* のMstInGameエントリは現時点で存在しない。これから生成する。

### 雑魚敵の選択

作品別雑魚敵使用状況調査（`domain/knowledge/masterdata/in-game/作品別雑魚敵使用状況調査.md`）に従い、sumの専用雑魚敵を使用する。

- **normalブロック用の雑魚敵**: `enemy_sum_00001`（影）がメイン雑魚として最適
  - `e_sum_00001_general_Normal_Colorless`（HP 15,000, Def, speed 40）← 主力候補
  - `e_sum_00001_general_Normal_Yellow`（HP 26,000, Def, speed 40）← サブ候補
  - dungeonはOutpostHP=100固定なので、コンテンツIDには既存パラメータを参照する設計が必要
- **bossブロック用**: `chara_sum_00101`（影のウシオ 小舟 潮 / URキャラ）がボスとして最適
  - `c_sum_00101_general_Boss_Red`（HP 190,000, Support, speed 40, combo x7）

### dungeon normalブロック（推奨）

| 項目 | 推奨値 | 根拠 |
|------|--------|------|
| OutpostHP | 100（固定仕様） | dungeon仕様 |
| 雑魚エネミー（メイン） | enemy_sum_00001 / Colorless | normal_sum_00001〜00006 で最頻使用 |
| 雑魚エネミー（サブ） | enemy_sum_00001 / Yellow | normal_sum_00002/00004/00005 で使用 |
| BGM | SSE_SBG_003_003 | 全sum共通BGM |
| 背景アセット | sum_00003 | normal_sum_00001/00003/00006 で最多使用 |
| コマ行数 | 3行（固定仕様） | dungeon仕様 |

### dungeon bossブロック（推奨）

| 項目 | 推奨値 | 根拠 |
|------|--------|------|
| OutpostHP | 1,000（固定仕様） | dungeon仕様 |
| ボスエネミー | chara_sum_00101 / Boss_Red | URキャラ、normal_sum_00006でボスとして実績あり |
| BGM | SSE_SBG_003_003 | 全sum共通BGM |
| 背景アセット | sum_00003 | normal_sum_00006と同じ作品終盤の背景 |
| コマ行数 | 1行（固定仕様） | dungeon仕様 |

### パラメータ参考値（既存コンテンツより）

| 参考コンテンツ | 雑魚HP (coef=1) | 攻撃力 |
|---------------|----------------|--------|
| normal（outpost HP: 60,000〜70,000 / coef=2.0） | 15,000〜26,000 | 200〜400 |
| hard（outpost HP: 120,000〜145,000 / coef=2.8〜4.0） | 15,000〜80,000（x倍補正） | 200〜600（x倍補正） |

---

## まとめ・パターン特徴

1. **BGM統一**: 全18コンテンツで `SSE_SBG_003_003` を使用（chiは003_001、danは003_002）
2. **enemy_sum_00001（影）が中心雑魚**: Colorless（弱）→ Yellow/Blue（中）→ Red/Boss（強）の色バリアントで幅広く使用
3. **2パターンのnormal構成**:
   - キャラ中心型（00001/00003/00006）: 3〜4イベント、FriendUnitDeadトリガーで次のキャラへ連鎖
   - 雑魚中心型（00002/00004/00005）: 10〜14イベント、ElapsedTimeとFriendUnitDeadの複合、OutpostDamageでループ
4. **normalのボス群**: 雑魚型コンテンツの後半ボスは `e_sum_00101_general_Boss_Yellow`、`e_sum_00201_general_Boss_Blue`、`e_sum_00001_general_Boss_Red` と段階的に強化
5. **normal_sum_00006がクライマックス**: URキャラ（chara_sum_00101）が `c_sum_00101_general_Boss_Red` として登場する最終ステージ
6. **hardはnormalシーケンス流用**: 00001〜00005はnormalを参照し、coef値（2.8〜4.0）で難易度を上げる。00006のみ独自シーケンス（構成はnormal_sum_00006と同一）
7. **veryhardはGLO汎用敵を全面活用**: 全6ステージでenemy_glo_00001を使用。sum_vh専用パラメータ（high HP）とGLO汎用の組み合わせ
8. **URキャラはveryhardでも主役**: veryhard_sum_00005で `c_sum_00101_general_sum_vh_Boss_Yellow`、veryhard_sum_00006で `c_sum_00101_general_sum_vh_Boss_Blue` として登場
9. **dungeon用データは未生成**: これから `/masterdata-ingame-creator` スキルで normal_00001 と boss_00001 を生成する予定
