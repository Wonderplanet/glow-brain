# 魔都精兵のスレイブ（sur）インゲームパターン分析

## 概要

- series_id: `sur`
- URキャラ:
  - `chara_sur_00101` 誇り高き魔都の剣姫 羽前 京香（Green / Attack）
  - `chara_sur_00501` 空間を操る六番組組長 出雲 天花（Yellow / Technical）
  - `chara_sur_00901` 万物を統べる総組長 山城 恋（Blue / Attack）※FestivalUR
- インゲームコンテンツ数: 45件（MstInGame）
- 雑魚敵: `enemy_sur_00101`（醜鬼）がメイン、`enemy_sur_00001`（和倉 青羽）がサブ

---

## コンテンツ種別一覧

### normal（通常ブロック）

| ingame_id | bgm_asset_key | loop_background_asset_key | mst_enemy_outpost_id | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef | boss_enemy_attack_coef |
|-----------|--------------|--------------------------|----------------------|-----------|---------------------|--------------------------|------------------------|
| normal_sur_00001 | SSE_SBG_003_001 | sur_00001 | normal_sur_00001 | 50,000 | 1.0 | 1.0 | 1.0 |
| normal_sur_00002 | SSE_SBG_003_001 | sur_00001 | normal_sur_00002 | 52,000 | 1.0 | 1.0 | 1.0 |
| normal_sur_00003 | SSE_SBG_003_001 | sur_00001 | normal_sur_00003 | 54,000 | 1.0 | 1.0 | 1.0 |
| normal_sur_00004 | SSE_SBG_003_001 | sur_00001 | normal_sur_00004 | 56,000 | 1.0 | 1.0 | 1.0 |
| normal_sur_00005 | SSE_SBG_003_001 | sur_00002 | normal_sur_00005 | 58,000 | 1.0 | 1.0 | 1.3 |
| normal_sur_00006 | SSE_SBG_003_001 | sur_00002 | normal_sur_00006 | 60,000 | 1.0 | 1.0 | 1.0 |

- normalブロックの特徴: アウトポストHP は50,000〜60,000と**段階的に増加する**（spyの5,000固定と異なる）
- BGMは全ブロック統一: `SSE_SBG_003_001`
- boss_bgm_asset_key は全ブロック未設定（ボスBGMなし）
- loop_background_asset_key: `sur_00001`（00001〜00004）、`sur_00002`（00005〜00006）

### hard（ハードブロック）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef | boss_enemy_hp_coef | boss_enemy_attack_coef |
|-----------|--------------|----------------------|-----------|---------------------|--------------------------|-------------------|------------------------|
| hard_sur_00001 | SSE_SBG_003_001 | hard_sur_00001 | 100,000 | 2.5 | 2.5 | 1.0 | 1.0 |
| hard_sur_00002 | SSE_SBG_003_001 | hard_sur_00002 | 105,000 | 2.5 | 2.5 | 1.0 | 1.0 |
| hard_sur_00003 | SSE_SBG_003_001 | hard_sur_00003 | 110,000 | 2.5 | 2.5 | 1.0 | 1.0 |
| hard_sur_00004 | SSE_SBG_003_001 | hard_sur_00004 | 115,000 | 2.5 | 2.5 | 2.5 | 2.5 |
| hard_sur_00005 | SSE_SBG_003_001 | hard_sur_00005 | 120,000 | 2.5 | 2.0 | 1.5 | 1.5 |
| hard_sur_00006 | SSE_SBG_003_001 | hard_sur_00006 | 125,000 | 2.0 | 2.0 | 3.0 | 1.8 |

- hardブロックの特徴: アウトポストHP 100,000〜125,000（normal比で約2倍）
- normal_enemy_hp_coef は 2.0〜2.5 でシーケンス内のエネミーHP拡大に使用
- loop_background_asset_key は未設定（空白）

### veryhard（超ハードブロック）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef | boss_enemy_hp_coef | boss_enemy_attack_coef |
|-----------|--------------|----------------------|-----------|---------------------|--------------------------|-------------------|------------------------|
| veryhard_sur_00001 | SSE_SBG_003_001 | veryhard_sur_00001 | 200,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| veryhard_sur_00002 | SSE_SBG_003_001 | veryhard_sur_00002 | 205,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| veryhard_sur_00003 | SSE_SBG_003_001 | veryhard_sur_00003 | 210,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| veryhard_sur_00004 | SSE_SBG_003_001 | veryhard_sur_00004 | 215,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| veryhard_sur_00005 | SSE_SBG_003_001 | veryhard_sur_00005 | 220,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| veryhard_sur_00006 | SSE_SBG_003_001 | veryhard_sur_00006 | 225,000 | 1.0 | 1.0 | 1.0 | 1.0 |

- veryhardブロックの特徴: アウトポストHP 200,000〜225,000（normal比で約4倍）
- normal_enemy_hp_coef および boss_enemy_hp_coef はすべて 1.0
  - 難易度はシーケンス内で専用パラメータ（`e_sur_00101_general_sur_vh_*`）を使用して実現
- loop_background_asset_key は未設定

### event（イベントブロック：event_sur1）

| イベント形式 | アウトポストHP範囲 | BGM | boss_bgm |
|------------|----------------|-----|---------|
| event_sur1_1day_00001 | 500 | SSE_SBG_003_001 | なし |
| event_sur1_challenge01_00001〜00004 | 30,000〜80,000 | SSE_SBG_003_001 | SSE_SBG_003_004 |
| event_sur1_charaget01_00001〜00008 | 20,000〜40,000 | SSE_SBG_003_001 | SSE_SBG_003_004 |
| event_sur1_charaget02_00001〜00008 | 20,000〜50,000 | SSE_SBG_003_001 | SSE_SBG_003_004 |
| event_sur1_savage_00001〜00003 | 120,000〜180,000 | SSE_SBG_003_001 | SSE_SBG_003_004 |

### pvp（PVPブロック）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id |
|-----------|--------------|----------------------|
| pvp_sur_01 | — | pvp |
| pvp_sur_02 | — | pvp |

### raid（レイドボス）

| ingame_id | mst_enemy_outpost_id | outpost_hp |
|-----------|----------------------|-----------|
| raid_sur1_00001 | raid_sur1_00001 | 1,000,000 |

---

## アウトポストHP スケール比較

| コンテンツ種別 | アウトポストHP | 係数比（normal最小基準） |
|--------------|-------------|---------------------|
| normal | 50,000〜60,000 | 1.0x |
| hard | 100,000〜125,000 | 2.0〜2.5x |
| veryhard | 200,000〜225,000 | 4.0〜4.5x |
| event (1day) | 500 | 0.01x |
| event (challenge, 最低) | 30,000 | 0.6x |
| event (charaget, 最低) | 20,000 | 0.4x |
| event (savage, 最低) | 120,000 | 2.4x |
| raid | 1,000,000 | 20x |

> **重要**: surシリーズのアウトポストHPは各ブロック内でも段階的に増加する設計（spyとは異なる）。

---

## エネミーID → 日本語名対応表

| asset_key | 日本語名 |
|-----------|---------|
| chara_sur_00001 | 和倉 優希 |
| chara_sur_00101 | 誇り高き魔都の剣姫 羽前 京香 |
| chara_sur_00201 | 東 日万凛 |
| chara_sur_00301 | 駿河 朱々 |
| chara_sur_00501 | 空間を操る六番組組長 出雲 天花 |
| chara_sur_00601 | 東 八千穂 |
| chara_sur_00701 | 和倉 青羽 |
| chara_sur_00801 | 無窮の鎖 和倉 優希 |
| chara_sur_00901 | 万物を統べる総組長 山城 恋 |
| enemy_sur_00001 | 和倉 青羽（敵キャラ） |
| enemy_sur_00101 | 醜鬼 |

---

## エネミー別パラメータ詳細

### 雑魚敵: enemy_sur_00101（醜鬼）— メイン雑魚

generalグループ（通常・veryhard別）:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_sur_00101_general_Normal_Colorless | Normal | Defense | Colorless | 3,000 | 35 | 0.2 | 100 | 1 | 10 |
| e_sur_00101_general_Normal_Blue | Normal | Attack | Blue | 15,000 | 40 | 0.2 | 200 | 1 | 10 |
| e_sur_00101_general_Normal_Green | Normal | Attack | Green | 22,000 | 50 | 0.2 | 300 | 1 | 10 |
| e_sur_00101_general_Boss_Green | Boss | Attack | Green | 400,000 | 45 | 0.2 | 850 | 1 | 10 |
| e_sur_00101_general_sur_vh_Normal_Red | Normal | Attack | Red | 10,000 | 65 | 0.2 | 2,000 | 1 | 1 |
| e_sur_00101_general_sur_vh_Normal_Green | Normal | Attack | Green | 10,000 | 45 | 0.2 | 2,000 | 1 | 1 |
| e_sur_00101_general_sur_vh_Normal_Blue | Normal | Defense | Blue | 110,000 | 65 | 0.2 | 700 | 1 | 1 |

- `_general_Normal_*` はnormal/hardブロックで主に使用（Colorless→Blue→Greenの順に強化）
- `_general_sur_vh_*` はveryhardブロック専用の超強化パラメータ

### 雑魚敵: enemy_sur_00001（和倉 青羽）— サブ雑魚

generalグループ:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_sur_00001_general_Normal_Green | Normal | Attack | Green | 150,000 | 45 | 0.3 | 500 | 1 | 10 |
| e_sur_00001_general_Boss_Green | Boss | Attack | Green | 300,000 | 45 | 0.3 | 500 | 7 | 10 |
| e_sur_00001_general_sur_vh_Normal_Red | Normal | Attack | Red | 600,000 | 45 | 0.3 | 600 | 1 | 1 |
| e_sur_00001_general_sur_vh2_Normal_Red | Normal | Attack | Red | 500,000 | 55 | 0.3 | 700 | 5 | 1 |
| e_sur_00001_general_sur_vh_Boss_Red | Boss | Attack | Red | 1,200,000 | 45 | 0.3 | 1,000 | 5 | 1 |

- `e_sur_00001` は normal_sur_00005 から登場（序盤には出ない）
- well_distance が 0.3 と醜鬼（0.2）より遠め

### URキャラ（ボスとして登場）: chara_sur_00101（羽前 京香）

| parameter_id | 用途 | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle |
|-------------|-----|--------------------|-----------|----|---|---|---|---|---|
| c_sur_00101_general_Boss_Blue | general（veryhard）ボス | Boss | Attack | Blue | 400,000 | 45 | 0.2 | 700 | 7 |
| c_sur_00101_general_as_Boss_Blue | general（上位）ボス | Boss | Attack | Blue | 600,000 | 45 | 0.2 | 800 | 5 |
| c_sur_00101_general_sur_vh_Boss_Green | veryhard専用ボス | Boss | Attack | Green | 350,000 | 55 | 0.2 | 600 | 7 |
| c_sur_00101_general_ori3_vh_Normal_Yellow | veryhard Normal枠 | Normal | Attack | Yellow | 700,000 | 45 | 0.2 | 300 | 5 |
| c_sur_00101_surget_Boss_Blue | キャラゲットボス | Boss | Attack | Blue | 50,000 | 45 | 0.25 | 500 | 4 |
| c_sur_00101_surget_Normal_Blue | キャラゲットNormal | Normal | Attack | Blue | 50,000 | 40 | 0.2 | 500 | 6 |
| c_sur_00101_aobaget_Boss_Red | イベントボス | Boss | Attack | Red | 10,000 | 45 | 0.2 | 100 | 4 |
| c_sur_00101_aobaget_Normal_Red | イベントNormal | Normal | Attack | Red | 1,000 | 45 | 0.2 | 100 | 4 |
| c_sur_00101_savege_Boss_Yellow | サベージボス | Boss | Attack | Yellow | 100,000 | 45 | 0.25 | 500 | 5 |
| c_sur_00101_challenge_Boss_Green | チャレンジボス | Boss | Attack | Green | 50,000 | 45 | 0.25 | 300 | 4 |
| c_sur_00101_glo2_advent_Boss_Colorless | GLO2アドベントボス | Boss | Attack | Colorless | 10,000 | 45 | 0.25 | 300 | 4 |

- normal_sur_00006 の4番シーケンスでボスとして登場（`c_sur_00101_general_Boss_Blue`）
- move_speed: 45〜55 と高速

### URキャラ（ボスとして登場）: chara_sur_00501（出雲 天花）

| parameter_id | 用途 | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle |
|-------------|-----|--------------------|-----------|----|---|---|---|---|---|
| c_sur_00501_surget_Boss_Blue | キャラゲットボス | Boss | Technical | Blue | 50,000 | 35 | 0.45 | 300 | 5 |
| c_sur_00501_surget_Normal_Blue | キャラゲットNormal | Normal | Technical | Blue | 50,000 | 32 | 0.45 | 300 | 6 |
| c_sur_00501_savege_Boss_Blue | サベージボス | Boss | Attack | Blue | 100,000 | 35 | 0.45 | 500 | 4 |
| c_sur_00501_savege_Atrans_Boss_Blue | サベージ変身後 | Boss | Technical | Blue | 100,000 | 35 | 0.45 | 500 | 6 |
| c_sur_00501_savege_Btrans_Boss_Blue | サベージ変身前→変身（transformationConditionType: HpPercentage） | Boss | Attack | Blue | 100,000 | 35 | 0.45 | 500 | 4 |
| c_sur_00501_aobaget_Boss_Blue | イベントボス | Boss | Attack | Blue | 10,000 | 25 | 0.45 | 100 | 10 |
| c_sur_00501_challenge_Boss_Yellow | チャレンジボス | Boss | Technical | Yellow | 50,000 | 25 | 0.45 | 300 | 4 |
| c_sur_00501_glo2_advent_Boss_Red | GLO2アドベントボス | Boss | Technical | Red | 10,000 | 25 | 0.45 | 300 | 4 |

- well_distance が 0.45 と他キャラより大きい（遠距離攻撃タイプ）
- move_speed: 25〜35 とやや遅め
- 変身パラメータあり（savege_Btrans → savege_Atrans）

### URキャラ（ボスとして登場）: chara_sur_00901（山城 恋）

| parameter_id | 用途 | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle |
|-------------|-----|--------------------|-----------|----|---|---|---|---|---|
| c_sur_00901_glo2_advent_Boss_Colorless | GLO2アドベントボス | Boss | Attack | Colorless | 10,000 | 30 | 0.39 | 300 | 4 |
| c_sur_00901_glo2_advent_Boss_Red | GLO2アドベントボス | Boss | Attack | Red | 10,000 | 30 | 0.39 | 300 | 4 |
| c_sur_00901_glo2_savage01_Boss_Colorless | GLO2サベージボス | Boss | Attack | Colorless | 10,000 | 32 | 0.39 | 300 | 4 |
| c_sur_00901_glo2_savage01_Boss_Red | GLO2サベージボス | Boss | Attack | Red | 10,000 | 32 | 0.39 | 300 | 4 |
| c_sur_00901_glo2_1d1c_Normal_Colorless | GLO2 1日1枠Normal | Normal | Attack | Colorless | 10,000 | 30 | 0.39 | 300 | 0 |

- chara_sur_00901 は FestivalURのため、surシリーズのgeneral/surgetコンテンツには**登場しない**
- GLO2（GLOW2）関連コンテンツのみに使用されるパラメータのみが存在
- dungeonボスとして使用する場合、新規パラメータの作成が必要

---

## コマライン（MstKomaLine）パターン

### normalブロックのコマ行数

| ingame_id | 行数 | コマ構成 | エフェクト |
|-----------|-----|---------|-----------|
| normal_sur_00001 | 2行 | 行1: sur_00001×1、行2: sur_00001×3 | 行1: None、行2: AttackPowerDown |
| normal_sur_00002 | 3行 | 行1: sur_00001×2、行2: sur_00001×1、行3: sur_00001×3 | 全行 None |
| normal_sur_00003 | 3行 | 行1: sur_00001×2、行2: sur_00001×3、行3: sur_00001×2 | 全行 None |
| normal_sur_00004 | 3行 | 行1: sur_00001×1、行2: sur_00001×3、行3: sur_00001×1 | 全行 None |
| normal_sur_00005 | 4行 | 行1: sur_00002×2、行2: sur_00002×3、行3: sur_00002×1、行4: sur_00002×2 | 全行 None |
| normal_sur_00006 | 1行（※重複あり） | 行1: sur_00002×2 | None |

> コマアセットキー: `sur_00001`（00001〜00004）、`sur_00002`（00005〜00006）

---

## シーケンスパターン（MstAutoPlayerSequence）

### normalブロックのシーケンス

| ingame_id | シーケンス数 | 使用エネミー（主要） | 特徴 |
|-----------|------------|------------------|----|
| normal_sur_00001 | 15 | e_sur_00101系 | ElapsedTime + FriendUnitDead + OutpostDamage |
| normal_sur_00002 | 17 | e_sur_00101系 | ElapsedTime + FriendUnitDead |
| normal_sur_00003 | 19 | e_sur_00101系 | ElapsedTime + FriendUnitDead |
| normal_sur_00004 | 14 | e_sur_00101系 + e_sur_00001系 | e_sur_00001登場 |
| normal_sur_00005 | 15 | e_sur_00101系 + e_sur_00001系 | InitialSummon + FriendUnitDead |
| normal_sur_00006 | 4 | c_sur_00001 + c_sur_00201 + c_sur_00301 + c_sur_00101ボス | キャラ登場シーケンス |

#### normal_sur_00001 の詳細シーケンス（抜粋）

```
要素1:  ElapsedTime=200    → SummonEnemy(e_sur_00101_general_Normal_Colorless, count=3, interval=200)
要素2:  ElapsedTime=1000   → SummonEnemy(e_sur_00101_general_Normal_Colorless, count=4, interval=50)
要素3:  ElapsedTime=1300   → SummonEnemy(e_sur_00101_general_Normal_Blue, count=2, interval=0)
要素4:  FriendUnitDead=3   → SummonEnemy(e_sur_00101_general_Normal_Colorless, count=3, interval=50)
要素5:  ElapsedTime=1500   → SummonEnemy(e_sur_00101_general_Normal_Blue, count=1, interval=0)
要素6:  FriendUnitDead=5   → SummonEnemy(e_sur_00101_general_Normal_Blue, count=2, interval=50)
要素7:  ElapsedTime=2500   → SummonEnemy(e_sur_00101_general_Normal_Blue, count=5, interval=250)
要素8:  ElapsedTime=3200   → SummonEnemy(e_sur_00101_general_Normal_Colorless, count=99, interval=500)
要素9:  ElapsedTime=4000   → SummonEnemy(e_sur_00101_general_Normal_Blue, count=2, interval=500)
要素10: ElapsedTime=4500   → SummonEnemy(e_sur_00101_general_Normal_Green, count=10, interval=1200)
要素11: ElapsedTime=4900   → SummonEnemy(e_sur_00101_general_Normal_Green, count=2, interval=700)
要素12: ElapsedTime=5800   → SummonEnemy(e_sur_00101_general_Normal_Blue, count=99, interval=1200)
要素13: ElapsedTime=6600   → SummonEnemy(e_sur_00101_general_Normal_Green, count=10, interval=750)
要素14: ElapsedTime=7000   → SummonEnemy(e_sur_00101_general_Normal_Green, count=10, interval=1200)
要素15: OutpostDamage=1    → SummonEnemy(e_sur_00101_general_Normal_Green, count=99, interval=750)
```

- normal_sur_00001のenemy_hp_coef は全て 1.0（MstInGameのcoef=1.0と合わせてエネミーHP そのまま）

#### normal_sur_00006 の詳細シーケンス（UR登場パターン）

```
要素1: InitialSummon=2  → SummonEnemy(c_sur_00001_general_Normal_Blue, count=1, pos=1.7)
要素2: FriendUnitDead=1 → SummonEnemy(c_sur_00201_general_Normal_Blue, count=1)
要素3: FriendUnitDead=1 → SummonEnemy(c_sur_00301_general_Normal_Blue, count=1)
要素4: FriendUnitDead=3 → SummonEnemy(c_sur_00101_general_Boss_Blue, count=1)  ← UR京香がボスとして登場
```

### hardブロックのシーケンス

| sequence_set_id | シーケンス数 | 備考 |
|----------------|------------|------|
| normal_sur_00001（hard_sur_00001参照） | 15 | hard_00001はnormalシーケンス流用 |
| hard_sur_00002 | 16 | hard専用シーケンス（e_sur_00101_general系のみ使用） |
| normal_sur_00003（hard_sur_00003参照） | 19 | hard_00003はnormalシーケンス流用 |

- hard_sur_00001 と hard_sur_00003 は `normal_sur_*` のシーケンスを流用
- hard のenemy_hp_coef と enemy_attack_coef は MstInGame の `normal_enemy_hp_coef=2.5` で拡大

#### hard_sur_00002 の詳細シーケンス（抜粋）

```
要素1:  ElapsedTime=100   → SummonEnemy(e_sur_00101_general_Normal_Colorless, count=3, interval=100)
要素2:  ElapsedTime=500   → SummonEnemy(e_sur_00101_general_Normal_Colorless, count=2)
要素3:  ElapsedTime=1000  → SummonEnemy(e_sur_00101_general_Normal_Colorless, count=1)
要素4:  FriendUnitDead=3  → SummonEnemy(e_sur_00101_general_Normal_Colorless, count=2)
要素5:  ElapsedTime=1700  → SummonEnemy(e_sur_00101_general_Normal_Blue, count=3)
...（Greenへと段階的に強化）
要素15: ElapsedTime=6900  → SummonEnemy(e_sur_00101_general_Normal_Green, count=99)
要素16: OutpostDamage=1   → SummonEnemy(e_sur_00101_general_Normal_Green, count=99)
```

### veryhardブロックのシーケンス

| sequence_set_id | シーケンス数 | 主要エネミー |
|----------------|------------|------------|
| veryhard_sur_00001 | 13 | e_sur_00101_general_sur_vh_* + e_glo_00001_general_sur_vh_* |
| veryhard_sur_00002 | 11 | e_sur_00101系 vhパラメータ |
| veryhard_sur_00003 | 18 | e_sur_00101系 + glo_00001系 |
| veryhard_sur_00004 | 17 | e_sur_00101系 vhパラメータ |
| veryhard_sur_00005 | 19 | e_sur_00101系 + e_sur_00001系 |
| veryhard_sur_00006 | 12 | c_sur_00101_general_sur_vh_* ボス |

#### veryhard_sur_00001 の詳細シーケンス

```
要素1:  ElapsedTime=1    → SummonEnemy(e_sur_00101_general_sur_vh_Normal_Red, count=99, interval=...)
要素2:  ElapsedTime=400  → SummonEnemy(e_sur_00101_general_sur_vh_Normal_Green, count=99)
要素3:  ElapsedTime=850  → SummonEnemy(e_glo_00001_general_sur_vh_Normal_Blue, count=99)
要素4:  ElapsedTime=900  → SummonEnemy(e_glo_00001_general_sur_vh_Normal_Blue, count=99)
要素5:  ElapsedTime=500  → SummonEnemy(e_glo_00001_general_sur_vh_Normal_Red, count=99)
要素6:  ElapsedTime=2000 → SummonEnemy(e_glo_00001_general_sur_vh_big_Normal_Blue, count=1)
要素7:  ElapsedTime=4000 → SummonEnemy(e_sur_00101_general_sur_vh_Normal_Red, count=99)
要素8:  ElapsedTime=5000 → SummonEnemy(e_sur_00101_general_sur_vh_Normal_Green, count=99)
要素9:  ElapsedTime=6000 → SummonEnemy(e_glo_00001_general_sur_vh_Normal_Blue, count=99)
要素10: ElapsedTime=6050 → SummonEnemy(e_glo_00001_general_sur_vh_Normal_Blue, count=99)
要素11: OutpostDamage=1  → SummonEnemy(e_glo_00001_general_sur_vh_big_Normal_Blue, count=1)
要素12: OutpostDamage=1  → SummonEnemy(e_glo_00001_general_sur_vh_big_Normal_Blue, count=1)
要素13: ElapsedTime=1500 → SummonEnemy(e_glo_00001_general_sur_vh_Normal_Colorless, count=7)
```

> **重要**: `e_glo_00001_general_sur_vh_*`（GLO汎用敵のveryhard用パラメータ）がveryhardから積極使用。

---

## コンテンツ種別ごとの特徴比較

### アウトポストHP比較

| 種別 | アウトポストHP |
|-----|-------------|
| normal | 50,000〜60,000（段階増加） |
| hard | 100,000〜125,000（段階増加） |
| veryhard | 200,000〜225,000（段階増加） |

### surシリーズ固有の特徴

1. **アウトポストHPが段階増加**: 同種別内でもブロックが進むごとにHPが増加（spyの固定値と異なる）
2. **BGMは全ブロック統一**: `SSE_SBG_003_001`（spyの `SSE_SBG_003_002` より1つ前のトラック）
3. **veryhardはMstInGame係数=1.0**: シーケンス内の専用パラメータで難易度を実現
4. **hardはMstInGame係数=2.5**: normalシーケンスを流用しつつ係数で難易度調整

### 使用エネミーキャラの違い

| 種別 | 主力雑魚 | ボス役 | 汎用補助敵 |
|-----|---------|-------|---------|
| normal | enemy_sur_00101（醜鬼）| chara_sur_00101（UR/羽前京香） | なし |
| hard | enemy_sur_00101 | （normalシーケンス流用） | なし |
| veryhard | enemy_sur_00101（vh専用版）| chara_sur_00101（vh専用版） | e_glo_00001（GLO汎用敵） |

> **重要**: `e_glo_00001_general_sur_vh_*`（GLO汎用敵surシリーズvh用パラメータ）はveryhard専用。normalおよびhardでは使用されない。

### BGM（bgm_asset_key）パターン

| 用途 | BGM |
|-----|-----|
| 通常バトル（normal/hard/veryhard/event） | SSE_SBG_003_001 |
| ボスBGM（チャレンジ・キャラゲット・サベージ） | SSE_SBG_003_004 |

### loop_background_asset_key（背景アセット）パターン

- `sur_00001`: normal_sur_00001〜00004（暗い・戦闘的な背景）
- `sur_00002`: normal_sur_00005〜00006（別バリエーション）
- hard/veryhard: 未設定（空白）

---

## dungeon（限界チャレンジ）設計向けの推奨パラメータ

> ※ dungeonのMstInGameエントリは現時点では存在しない（これから生成する対象）。

### dungeonブロック仕様（CLAUDE.mdより）

| 種別 | インゲームID例 | MstEnemyOutpost HP | コマ行数 | ボスの有無 |
|------|--------------|-------------------|---------|----------|
| normal | `dungeon_sur_normal_00001` | 100（固定） | 3行（固定） | なし |
| boss | `dungeon_sur_boss_00001` | 1,000（固定） | 1行（固定） | あり |

### dungeon normalブロックの推奨パラメータ

- **BGM**: `SSE_SBG_003_001`（surシリーズ全体の通常バトルBGM）
- **loop_background_asset_key**: `sur_00001`（normal帯序盤で使用される背景）
- **使用エネミー**: `enemy_sur_00101`（醜鬼）をメインに使用
  - 推奨パラメータ: `e_sur_00101_general_Normal_Colorless`（弱）、`e_sur_00101_general_Normal_Blue`（中）、`e_sur_00101_general_Normal_Green`（強）
  - dungeonは3行固定なので、normal_sur_00003（3行構成）が最も参考になる
- **コマアセット**: `sur_00001`
- **MstInGame coefficients**: `normal_enemy_hp_coef=1.0`、`normal_enemy_attack_coef=1.0`（dungeonはoutpostHP=100で制御）

### dungeon normalブロックの推奨シーケンス

```
3行固定のコマ構成（normal_sur_00003パターンを参考）:
  行1: sur_00001×2
  行2: sur_00001×3
  行3: sur_00001×2

シーケンス例:
  要素1: ElapsedTime=200   → SummonEnemy(e_sur_00101_general_Normal_Colorless, count=3)
  要素2: FriendUnitDead=3  → SummonEnemy(e_sur_00101_general_Normal_Blue, count=2)
  要素3: ElapsedTime=2000  → SummonEnemy(e_sur_00101_general_Normal_Green, count=1)
```

### dungeon bossブロックの推奨パラメータ

- **使用URボス**: `chara_sur_00101`（羽前 京香）を最有力候補とする
  - ボスパラメータ候補: `c_sur_00101_general_Boss_Blue`（HP=400,000、move_speed=45）
  - dungeonのoutpostHP=1,000 なので、enemy_hp_coef で調整
- **BGM**: `SSE_SBG_003_001`（または `SSE_SBG_003_004` をboss_bgmに設定）
- **コマ行数**: 1行固定

---

## まとめ・パターン特徴

### 雑魚敵の使用ルール

1. **enemy_sur_00101**（醜鬼）が全難易度でメイン雑魚
2. **enemy_sur_00001**（和倉青羽）は normal_sur_00004 以降のサブ雑魚
3. generalパラメータIDの命名規則: `e_{enemy_id}_general_{unit_kind}_{color}`
   - Colorless（弱）→ Blue（中）→ Green（強）の3段階
   - veryhard用: `e_{enemy_id}_general_sur_vh_{unit_kind}_{color}`
4. **e_glo_00001**（GLO汎用敵）は veryhard 帯のみ `_general_sur_vh_*` パラメータで登場

### URキャラの登場パターン

| キャラ | ingame登場 | dungeonでの活用可能性 |
|--------|-----------|---------------------|
| chara_sur_00101 | normal_sur_00006でボス、veryhardでも使用 | dungeon bossの筆頭候補 |
| chara_sur_00501 | surget/savage/challengeのみ | dungeon bossとして使用可能（新規パラメータ必要） |
| chara_sur_00901 | GLO2専用のみ（surシリーズgeneralコンテンツ未登場） | dungeon boss使用時は新規パラメータ作成必須 |

### シーケンスのトリガー種別

確認されたcondition_type:
- `ElapsedTime` — 経過時間トリガー（最多使用）
- `FriendUnitDead` — 味方ユニット死亡トリガー
- `OutpostDamage` — アウトポストダメージトリガー
- `InitialSummon` — 初期召喚（特定位置に配置）

### surシリーズのdungeon生成における注意点

1. **normalブロック（3行固定）** は `normal_sur_00003`（3行、15〜19シーケンス）が参考
2. **アセットキー**:
   - BGM: `SSE_SBG_003_001`（spyと異なる点に注意）
   - 背景: `sur_00001`
3. **コマアセット**: `sur_00001`（スロット幅 0.55固定）
4. **GLO汎用敵は使用しない**（dungeon normalは作品専用雑魚のみ）
5. **chara_sur_00901は専用パラメータなし**: dungeonで使用する場合は `_glo2_advent_*` を参考に新規作成
6. **MstInGame coefficient**: surシリーズのnormalは全て `1.0`（シーケンス内のHP値そのまま適用）
