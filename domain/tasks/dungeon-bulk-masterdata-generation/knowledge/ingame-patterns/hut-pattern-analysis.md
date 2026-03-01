# ふつうの軽音部（hut）インゲームパターン分析

## 概要

- series_id: `hut`
- URキャラ:
  - `chara_hut_00001` (Green / Defense) — ひたむきギタリスト 鳩野 ちひろ
- インゲームコンテンツ数: 21件（MstInGame）
- **注意: normal / hard / veryhard ブロックは存在しない（イベント・PVP・レイドのみ）**

---

## コンテンツ種別一覧

### event（イベントブロック：event_hut1）

| ingame_id | bgm_asset_key | boss_bgm_asset_key | mst_enemy_outpost_id | outpost_hp |
|-----------|--------------|-------------------|----------------------|-----------|
| event_hut1_1day_00001 | SSE_SBG_003_002 | （なし） | event_hut1_1day_00001 | 500 |
| event_hut1_challenge_00001 | SSE_SBG_003_009 | SSE_SBG_003_007 | event_hut1_challenge_00001 | 30,000 |
| event_hut1_challenge_00002 | SSE_SBG_003_009 | SSE_SBG_003_007 | event_hut1_challenge_00002 | 40,000 |
| event_hut1_challenge_00003 | SSE_SBG_003_009 | SSE_SBG_003_007 | event_hut1_challenge_00003 | 60,000 |
| event_hut1_challenge_00004 | SSE_SBG_003_009 | SSE_SBG_003_007 | event_hut1_challenge_00004 | 80,000 |
| event_hut1_charaget01_00001 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget01_00001 | 15,000 |
| event_hut1_charaget01_00002 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget01_00002 | 25,000 |
| event_hut1_charaget01_00003 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget01_00003 | 30,000 |
| event_hut1_charaget01_00004 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget01_00004 | 45,000 |
| event_hut1_charaget02_00001 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget02_00001 | 25,000 |
| event_hut1_charaget02_00002 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget02_00002 | 25,000 |
| event_hut1_charaget02_00003 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget02_00003 | 30,000 |
| event_hut1_charaget02_00004 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget02_00004 | 30,000 |
| event_hut1_charaget02_00005 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget02_00005 | 40,000 |
| event_hut1_charaget02_00006 | SSE_SBG_003_002 | SSE_SBG_003_004 | event_hut1_charaget02_00006 | 45,000 |
| event_hut1_savage_00001 | SSE_SBG_003_007 | （なし） | event_hut1_savage_00001 | 120,000 |
| event_hut1_savage_00002 | SSE_SBG_003_007 | （なし） | event_hut1_savage_00002 | 150,000 |
| event_hut1_savage_00003 | SSE_SBG_003_007 | （なし） | event_hut1_savage_00003 | 200,000 |

- **loop_background_asset_key はすべて空**（hut専用背景アセットは未設定）
- **コマアセット**は汎用アセット（`glo_00014`, `glo_00024`, `glo_00038`, `glo_00039`）のみを使用

### pvp（PVPブロック）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id |
|-----------|--------------|----------------------|
| pvp_hut_01 | SSE_SBG_003_007 | pvp |
| pvp_hut_02 | SSE_SBG_003_007 | pvp |

### raid（レイドボス）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp |
|-----------|--------------|----------------------|-----------|
| raid_hut1_00001 | SSE_SBG_003_008 | raid_hut1_00001 | 1,000,000 |

---

## アウトポストHP スケール比較

| コンテンツ種別 | アウトポストHP |
|--------------|-------------|
| event (1day) | 500 |
| event (challenge, 最低) | 30,000 |
| event (challenge, 最高) | 80,000 |
| event (charaget, 最低) | 15,000 |
| event (charaget, 最高) | 45,000 |
| event (savage, 最低) | 120,000 |
| event (savage, 最高) | 200,000 |
| raid | 1,000,000 |

---

## エネミーID → 日本語名対応表

| asset_key | 日本語名 |
|-----------|---------|
| chara_hut_00001 | ひたむきギタリスト 鳩野 ちひろ |
| chara_hut_00101 | 幸山 厘 |
| chara_hut_00201 | 内田 桃 |
| chara_hut_00301 | 藤井 彩目 |
| enemy_hut_00001 | 鷹見 項希 |

> **重要**: hut専用の雑魚敵は `enemy_hut_00001`（鷹見 項希）のみ。GLO汎用敵 `enemy_glo_00001` が多用される。

---

## エネミー別パラメータ詳細（MstEnemyStageParameter）

### 雑魚敵: enemy_hut_00001（鷹見 項希）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_hut_00001_hut1_1d1c_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 34 | 0.3 | 100 | 0 | 200 |
| e_hut_00001_hut1_challenge_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 34 | 0.3 | 100 | 0 | 30 |
| e_hut_00001_hut1_advent_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 35 | 0.3 | 100 | 1 | 50 |
| e_hut_00001_hut1_advent_Boss_Yellow | Boss | Attack | Yellow | 10,000 | 35 | 0.3 | 100 | 1 | 100 |

**備考**: move_speedは34〜35、well_distanceは0.3固定、attack_powerは100固定。コンボサイクル0または1。

### URキャラ（ボスとして登場）: chara_hut_00001（ひたむきギタリスト 鳩野 ちひろ）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| c_hut_00001_hut1_1d1c_Normal_Colorless | Normal | Defense | Colorless | 10,000 | 35 | 0.21 | 100 | 5 | 200 |
| c_hut_00001_hut1_charaget01_Normal_Colorless | Normal | Defense | Colorless | 10,000 | 35 | 0.21 | 100 | 5 | 200 |
| c_hut_00001_hut1_charaget02_Boss_Yellow | Boss | Defense | Yellow | 10,000 | 35 | 0.21 | 100 | 5 | 200 |
| c_hut_00001_hut1_challenge1_Boss_Green | Boss | Defense | Green | 10,000 | 35 | 0.21 | 100 | 5 | 200 |
| c_hut_00001_hut1_challenge2_Boss_Blue | Boss | Defense | Blue | 10,000 | 35 | 0.21 | 100 | 5 | 200 |
| c_hut_00001_hut1_challeng3_Boss_Red | Boss | Defense | Red | 10,000 | 35 | 0.21 | 100 | 5 | 200 |
| c_hut_00001_hut1_challenge4_Boss_Yellow | Boss | Defense | Yellow | 10,000 | 35 | 0.21 | 100 | 5 | 200 |
| c_hut_00001_hut1_advent_Normal_Colorless | Normal | Defense | Colorless | 1,000 | 30 | 0.15 | 100 | 5 | 50 |
| c_hut_00001_hut1_advent_Boss_Colorless | Boss | Defense | Colorless | 10,000 | 30 | 0.15 | 100 | 5 | 100 |
| c_hut_00001_hut1_savage01_Boss_Colorless | Boss | Defense | Colorless | 100,000 | 35 | 0.15 | 500 | 7 | 50 |
| c_hut_00001_hut1_savage02_Boss_Red | Boss | Defense | Red | 100,000 | 35 | 0.15 | 500 | 7 | 50 |
| c_hut_00001_hut1_savage03_Boss_Yellow | Boss | Defense | Yellow | 100,000 | 35 | 0.15 | 500 | 7 | 50 |

**特徴**: role_type は Defense 固定（URキャラの特性）。サベージ用は HP 100,000・attack_power 500。

### キャラクター: chara_hut_00101（幸山 厘）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle |
|-------------|--------------------|-----------|----|---|---|---|---|---|
| c_hut_00101_hut1_charaget01_Boss_Colorless | Boss | Support | Colorless | 10,000 | 35 | 0.42 | 100 | 6 |
| c_hut_00101_hut1_charaget02_Normal_Yellow | Normal | Support | Yellow | 1,000 | 35 | 0.42 | 100 | 6 |
| c_hut_00101_hut1_challeng3_Normal_Red | Normal | Support | Red | 10,000 | 35 | 0.42 | 100 | 6 |
| c_hut_00101_hut1_challenge2_Normal_Blue | Normal | Support | Blue | 10,000 | 35 | 0.42 | 100 | 6 |
| c_hut_00101_hut1_challenge4_Normal_Yellow | Normal | Support | Yellow | 10,000 | 35 | 0.42 | 100 | 6 |
| c_hut_00101_hut1_advent_Normal_Colorless | Normal | Support | Colorless | 1,000 | 35 | 0.35 | 100 | 3 |
| c_hut_00101_hut1_advent_Boss_Yellow | Boss | Attack | Yellow | 10,000 | 35 | 0.35 | 100 | 5 |
| c_hut_00101_hut1_savage01_Boss_Colorless | Boss | Attack | Colorless | 100,000 | 28 | 0.3 | 500 | 5 |
| c_hut_00101_hut1_savage02_Boss_Red | Boss | Attack | Red | 100,000 | 35 | 0.3 | 500 | 5 |
| c_hut_00101_hut1_savage03_Boss_Yellow | Boss | Attack | Yellow | 50,000 | 35 | 0.3 | 500 | 5 |

### キャラクター: chara_hut_00201（内田 桃）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power |
|-------------|--------------------|-----------|----|---|---|---|---|
| c_hut_00201_hut1_charaget01_Boss_Colorless | Boss | Attack | Colorless | 10,000 | 35 | 0.42 | 100 |
| c_hut_00201_hut1_charaget02_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 35 | 0.42 | 100 |
| c_hut_00201_hut1_challeng3_Normal_Red | Normal | Attack | Red | 10,000 | 35 | 0.42 | 100 |
| c_hut_00201_hut1_advent_Boss_Colorless | Boss | Attack | Colorless | 10,000 | 35 | 0.42 | 100 |
| c_hut_00201_hut1_advent_Normal_Colorless | Normal | Support | Colorless | 1,000 | 30 | 0.3 | 100 |
| c_hut_00201_hut1_savage02_Boss_Red | Boss | Attack | Red | 100,000 | 35 | 0.3 | 500 |
| c_hut_00201_hut1_savage03_Boss_Yellow | Boss | Attack | Yellow | 100,000 | 35 | 0.3 | 500 |

### キャラクター: chara_hut_00301（藤井 彩目）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power |
|-------------|--------------------|-----------|----|---|---|---|---|
| c_hut_00301_hut1_charaget01_Boss_Colorless | Boss | Attack | Colorless | 10,000 | 35 | 0.42 | 100 |
| c_hut_00301_hut1_charaget02_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 35 | 0.42 | 100 |
| c_hut_00301_hut1_challenge4_Normal_Yellow | Normal | Technical | Yellow | 10,000 | 35 | 0.5 | 100 |
| c_hut_00301_hut1_advent_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 35 | 0.38 | 100 |
| c_hut_00301_hut1_savage03_Boss_Yellow | Boss | Attack | Yellow | 50,000 | 30 | 0.38 | 500 |

### GLO汎用敵: enemy_glo_00001（hut向けパラメータ）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power |
|-------------|--------------------|-----------|----|---|---|---|---|
| e_glo_00001_hut1_challenge_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 40 | 0.2 | 100 |
| e_glo_00001_hut1_charaget01_Normal_Blue | Normal | Attack | Blue | 1,000 | 40 | 0.2 | 100 |
| e_glo_00001_hut1_charaget02_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 40 | 0.2 | 100 |
| e_glo_00001_hut1_savage01_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 40 | 0.18 | 100 |
| e_glo_00001_hut1_savage02_poison_Normal_Red | Normal | Technical | Red | 10,000 | 40 | 0.2 | 100 |
| e_glo_00001_hut1_savage03_Normal_Yellow | Normal | Attack | Yellow | 10,000 | 40 | 0.18 | 100 |
| e_glo_00001_hut1_savage03_tank_Normal_Yellow | Normal | Defense | Yellow | 100,000 | 40 | 0.13 | 100 |
| e_glo_00001_hut1_advent_Normal_Colorless | Normal | Defense | Colorless | 1,000 | 40 | 0.21 | 100 |
| e_glo_00001_hut1_advent_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 37 | 0.22 | 100 |
| e_glo_00001_hut1_advent_Boss_Colorless | Boss | Defense | Colorless | 10,000 | 40 | 0.21 | 100 |
| e_glo_00001_hut1_advent_Boss_Yellow | Boss | Attack | Yellow | 10,000 | 37 | 0.22 | 100 |

---

## コマライン（MstKomaLine）パターン

### 使用コマアセット

hut では**作品専用背景アセット（`hut_XXXXX`）は存在しない**。すべて汎用アセットを使用:

| コマアセットキー | 主な使用コンテンツ |
|----------------|-----------------|
| `glo_00014` | challenge、charaget02、PVP（1day含む） |
| `glo_00024` | charaget01 |
| `glo_00038` | savage（スリップ・毒コンテンツ） |
| `glo_00039` | raid |

### コンテンツ別コマ行数

| コンテンツ | コマ行数 |
|----------|---------|
| event_hut1_1day_00001 | 2行 |
| event_hut1_challenge_00001 | 3行 |
| event_hut1_challenge_00002 | 3行 |
| event_hut1_challenge_00003 | 2行 |
| event_hut1_challenge_00004 | 3行 |
| event_hut1_charaget01_00001 | 2行 |
| event_hut1_charaget01_00002 | 2行 |
| event_hut1_charaget01_00003 | 3行 |
| event_hut1_charaget01_00004 | 3行 |
| event_hut1_charaget02_00001 | 2行 |
| event_hut1_charaget02_00002 | 2行 |
| event_hut1_charaget02_00003 | 2行 |
| event_hut1_charaget02_00004 | 2行 |
| event_hut1_charaget02_00005 | 3行 |
| event_hut1_charaget02_00006 | 3行 |
| event_hut1_savage_00001 | 3行 |
| event_hut1_savage_00002 | 3行 |
| event_hut1_savage_00003 | 4行 |
| pvp_hut_01 | 3行 |
| pvp_hut_02 | 3行 |
| raid_hut1_00001 | 4行 |

### コマエフェクト

| コンテンツ種別 | 使用エフェクト |
|-------------|-------------|
| challenge | `None`（一部 `SlipDamage` 350） |
| charaget | `None`（エフェクトなし） |
| savage | `SlipDamage`、`Poison` |
| pvp | `None`、`AttackPowerUp` |
| raid | `SlipDamage`、`Burn` |

---

## シーケンスパターン（MstAutoPlayerSequence）

### event_hut1_1day_00001 のシーケンス

```
要素1: ElapsedTime=500 → SummonEnemy(c_hut_00001_hut1_1d1c_Normal_Colorless, count=1)
        enemy_hp_coef=1, enemy_attack_coef=0.5
要素2: ElapsedTime=2000 → SummonEnemy(e_hut_00001_hut1_1d1c_Normal_Colorless, count=1)
        enemy_hp_coef=1, enemy_attack_coef=0.5
```

1日限定コンテンツはシンプルな2要素構成。URキャラ（Normal）と専用雑魚敵の組合せ。

### event_hut1_challenge シーケンス

| ingame_id | シーケンス数 | 主要エネミー | enemy_hp_coef範囲 | enemy_attack_coef範囲 |
|-----------|------------|------------|-----------------|---------------------|
| challenge_00001 | 4 | c_hut_00001（Boss_Green）、enemy_glo_00001 | 30〜65 | 1〜3 |
| challenge_00002 | 11 | c_hut_00001（Boss_Blue）、c_hut_00101（Normal_Blue）、enemy_glo_00001 | 5〜37 | 1〜4.5 |
| challenge_00003 | 5（確認分） | c_hut_00001（Boss_Red）、c_hut_00101/00201（Normal）、enemy_glo_00001 | 5.5〜32 | 1〜6 |
| challenge_00004 | （確認分） | c_hut_00001（Boss_Yellow）、c_hut_00101/00301（Normal）、enemy_glo_00001 | — | — |

#### challenge_00001 の詳細

```
要素1: ElapsedTime=1500 → SummonEnemy(c_hut_00001_hut1_challenge1_Boss_Green, count=1)
        enemy_hp_coef=30, enemy_attack_coef=3
要素2: ElapsedTime=0 → SummonEnemy(e_glo_00001_hut1_challenge_Normal_Colorless, count=1)
        enemy_hp_coef=65, enemy_attack_coef=1
要素3: ElapsedTime=500 → SummonEnemy(e_glo_00001_hut1_challenge_Normal_Colorless, count=1)
        enemy_hp_coef=65, enemy_attack_coef=1
要素4: ElapsedTime=1000 → SummonEnemy(e_glo_00001_hut1_challenge_Normal_Colorless, count=1)
        enemy_hp_coef=65, enemy_attack_coef=1
```

### event_hut1_charaget01 シーケンス

| ingame_id | シーケンス数 | 主要構成 | enemy_hp_coef（ボス） |
|-----------|------------|---------|---------------------|
| charaget01_00001 | 5 | c_hut_00001（Normal）＋e_glo_00001×4（InitialSummon） | 1.5 |
| charaget01_00002 | 6 | c_hut_00101（Boss）＋c_hut_00001（Normal）＋e_glo_00001×4 | 5 |
| charaget01_00003 | 10 | c_hut_00201（Boss）＋c_hut_00301（Boss）＋c_hut_00001（Normal）＋e_glo_00001×6 | 8 |
| charaget01_00004 | 11 | c_hut_00201（Boss）＋c_hut_00101（Boss）＋c_hut_00001（Normal）＋e_glo_00001×6＋大量補充 | 9〜10 |

**特徴**: InitialSummon（初期配置）でGLO汎用敵を複数配置し、ElapsedTimeでボスを後から召喚する構成。

#### charaget01_00001 の詳細

```
要素1: ElapsedTime=1000 → SummonEnemy(c_hut_00001_hut1_charaget01_Normal_Colorless, count=1)
        enemy_hp_coef=1.5, enemy_attack_coef=0.7
要素2〜5: InitialSummon=0 → SummonEnemy(e_glo_00001_hut1_charaget01_Normal_Blue, count=1各)
           enemy_hp_coef=4, enemy_attack_coef=1.5
```

### event_hut1_charaget02 シーケンス

| ingame_id | シーケンス数 | 主要エネミー | 特徴 |
|-----------|------------|------------|------|
| charaget02_00001 | 4 | c_hut_00101（Normal）、e_glo_00001×大量 | ElapsedTimeで15体召喚 |
| charaget02_00002 | 4 | c_hut_00001（Boss）、c_hut_00101（Normal）、e_glo_00001×大量 | OutpostHpPercentage=99でボス |
| charaget02_00003 | 5 | c_hut_00201（Normal×3）、e_glo_00001×大量 | FriendUnitDeadで繰り返し |
| charaget02_00004 | 5 | c_hut_00001（Boss）、c_hut_00201（Normal）、e_glo_00001×大量 | OutpostHpPercentage=99でボス |
| charaget02_00005 | 7 | c_hut_00301（Normal×3）、e_glo_00001（EnterTargetKomaIndex） | コマ到達トリガーあり |
| charaget02_00006 | 7 | c_hut_00001（Boss）、c_hut_00301（Normal）、e_glo_00001（EnterTargetKomaIndex） | コマ到達トリガーあり |

### event_hut1_savage シーケンス

| ingame_id | シーケンス数 | 主要エネミー | enemy_hp_coef（典型値） |
|-----------|------------|------------|---------------------|
| savage_00001 | 8 | e_glo_00001（30体）、c_hut_00001（Boss）、c_hut_00101（Boss） | 6〜8 |
| savage_00002 | 6 | e_glo_00001（30体）、poison版glo_00001（10体）、c_hut_00001/00101/00201（Boss各1） | 4〜8 |
| savage_00003 | 8 | e_glo_00001×tank（1体）、e_glo_00001×30体、c_hut_00001/00101/00201/00301（Boss各1） | 3.5〜12 |

**特徴**: savageではGLO汎用敵を大量召喚（30〜50体）し、コマ到達またはFriendUnitDeadトリガーで複数のキャラボスが登場する。

### raid_hut1_00001 シーケンス（グループ切替式）

6グループ（初期 + w1〜w6、w6 → w1 ループ）の波構成:

| グループ | 主要エネミー | enemy_hp_coef範囲 |
|---------|------------|-----------------|
| 初期（グループなし） | e_glo_00001（3体）、e_hut_00001（Normal_Yellow） | 9〜10 |
| w1（FriendUnitDead=3で移行） | c_hut_00101（Boss）、c_hut_00001（Normal×補充）、e_glo_00001（多数） | 8〜30 |
| w2（FriendUnitDead=4で移行） | c_hut_00201（Boss）、c_hut_00001（Normal×補充）、e_glo_00001（多数） | 12〜35 |
| w3（FriendUnitDead=9で移行） | e_hut_00001（Boss_Yellow）、c_hut_00001/00101（Normal）、e_glo_00001 | 30〜70 |
| w4（FriendUnitDead=?で移行） | c_hut_00001（Boss）、c_hut_00201/00301（Normal）、e_glo_00001 | 50〜150 |
| w5（FriendUnitDead=20で移行） | c_hut_00001（Boss）、c_hut_00101/00201/00301（Normal）、e_glo_00001 | 100〜500 |
| w6（FriendUnitDead=26で移行） | c_hut_00001（Boss）、e_glo_00001（Boss＋Normal大量） | 70〜200 |

---

## コンテンツ種別ごとの特徴比較

### BGM（bgm_asset_key）パターン

| 用途 | BGM |
|-----|-----|
| 通常バトル（1day、charaget） | SSE_SBG_003_002 |
| チャレンジ（challenge） | SSE_SBG_003_009（通常）+ SSE_SBG_003_007（ボス） |
| サベージ・PVP | SSE_SBG_003_007 |
| レイド | SSE_SBG_003_008 |

**注**: hut では `SSE_SBG_003_009` が challenge のメインBGMに使われている（spy と異なる）。

### 使用エネミーキャラの違い

| 種別 | 主力雑魚 | ボス役 |
|-----|---------|-------|
| challenge | enemy_glo_00001（大量）| chara_hut_00001（各色） |
| charaget01 | enemy_glo_00001（InitialSummon） | chara_hut_00101〜00301 |
| charaget02 | enemy_glo_00001（大量） | chara_hut_00001、00101〜00301 |
| savage | enemy_glo_00001（大量）、e_glo（poison/tank亜種） | chara_hut_00001〜00301 |
| raid | enemy_glo_00001（大量）、enemy_hut_00001 | chara_hut_00001〜00301 |

> **重要**: hutには作品専用の通常雑魚敵 `enemy_hut_00001` が存在するが、**使用されるのはraidとevent_1dayのみ**。それ以外の大半のコンテンツでは `enemy_glo_00001`（GLO汎用敵）が主力雑魚として使用されている。

---

## dungeon設計向けの推奨パラメータ

> dungeonのMstInGameエントリは現時点では存在しない（これから生成する対象）。
> 以下は既存のevent/charaget/challenge パターンからdungeon向けパラメータを推定するための参考情報。

### dungeonブロック仕様（CLAUDE.mdより）

| 種別 | インゲームID例 | MstEnemyOutpost HP | コマ行数 | ボスの有無 |
|------|--------------|-------------------|---------|----------|
| normal | `dungeon_hut_normal_00001` | 100（固定） | 3行（固定） | なし |
| boss | `dungeon_hut_boss_00001` | 1,000（固定） | 1行（固定） | あり |

### dungeon normalブロックの推奨パラメータ

- **BGM**: `SSE_SBG_003_002`（通常BGM）
- **loop_background_asset_key**: 既存コンテンツでは空。hut専用背景アセットが存在しないため、他作品の汎用背景またはglobal汎用背景を使用することを検討
- **使用エネミー**: `enemy_glo_00001`（メイン）
  - hutには専用雑魚敵 `enemy_hut_00001` が存在するが、既存コンテンツでの使用実績がraidと1dayに限られる
  - dungeon向けには `enemy_glo_00001` を主軸として設計することが自然
- **コマアセット**: `glo_00014`（hutのチャレンジ・charaget02・PVPで使用される汎用アセット）
- **enemy_hp_coef**: dungeonはアウトポストHP=100と非常に低いため、1.0〜1.5程度が目安
- **コマエフェクト**: `None`（dungeon normalでは効果なし）

### dungeon bossブロックの推奨パラメータ

- **BGM**: `SSE_SBG_003_002`（通常BGM）または `SSE_SBG_003_004`（ボス登場時）
- **ボス**: `chara_hut_00001`（ひたむきギタリスト 鳩野 ちひろ）
  - 推奨パラメータID: `c_hut_00001_hut1_charaget01_Normal_Colorless`を参考に新規作成
  - role_type: Defense（URキャラの特性）
  - move_speed: 35、well_distance: 0.21、attack_power: 100、attack_combo_cycle: 5

### 雑魚敵パラメータ参考値（dungeon向け）

dungeonのアウトポストHP=100を基準に、enemy_hp_coefで調整:

| エネミー | 推奨 character_unit_kind | 推奨 color | 基本HP | move_speed | well_distance |
|--------|--------------------------|----------|-------|-----------|--------------|
| enemy_glo_00001 | Normal | Colorless / Blue | 1,000 | 40 | 0.2 |

---

## まとめ・パターン特徴

### hutシリーズの特徴

1. **normal / hard / veryhard ブロックが存在しない**
   - 他の多くの作品（spy, chi等）はnormal〜veryhardまで段階的なブロックを持つが、hutはイベント・PVP・レイドのみ
   - このため、通常難易度のシーケンスパターンの参考にできる既存データが少ない

2. **専用雑魚敵 `enemy_hut_00001`（鷹見 項希）は使用頻度が低い**
   - 使用実績: event_hut1_1day（1day限定）と raid_hut1（レイド）のみ
   - 大半のコンテンツでは `enemy_glo_00001`（GLO汎用敵）が主力雑魚

3. **コマアセットはすべて汎用（glo_XXXXX）**
   - `glo_00014`（challenge・charaget02・PVP）
   - `glo_00024`（charaget01）
   - `glo_00038`（savage）
   - `glo_00039`（raid）
   - hut専用の `hut_XXXXX` アセットは存在しない

4. **loop_background_asset_key が未設定**
   - 全コンテンツで空（hut専用の背景ループアセットなし）

5. **エネミーパラメータの命名規則**
   - 雑魚敵: `e_{enemy_id}_hut1_{用途}_{unit_kind}_{color}`
   - URキャラ: `c_hut_{chara_id}_{イベント名}_{unit_kind}_{color}`

6. **URキャラ `chara_hut_00001` のロールタイプは Defense**
   - 他作品のURキャラ（Attack系が多い）とは異なりDefense固定
   - dungeon bossとして登場させる際もDefenseとして設計するのが適切

### dungeonコンテンツ生成時の留意点

1. **dungeon normalブロック（3行固定）** は charaget01（3行）のパターンが最も参考になる
2. **専用雑魚敵 `enemy_hut_00001` は使わない**（ CLAUDE.md の「専用雑魚なし作品はglo汎用敵に変更」方針に従い、dungeon向けは `enemy_glo_00001` を使用）
3. **コマアセット**: `glo_00014` を推奨（challnegeやPVPでも使用実績あり）
4. **BGM**: `SSE_SBG_003_002`（通常バトルBGM）が標準
5. **loop_background_asset_key**: hutには専用背景がないため、他作品同様の汎用背景か空白で対応
