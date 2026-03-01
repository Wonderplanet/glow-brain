# 君のことが大大大大大好きな100人の彼女 インゲームパターン分析

## 概要

- series_id: kim
- URキャラ: chara_kim_00001 (Blue / Defense) — 溢れる母性 花園 羽々里
- インゲームコンテンツ数: 21件（1day x1, challenge x4, charaget01 x4, charaget02 x6, savage x3, pvp x2, raid x1）
- dungeonコンテンツ: 現時点では存在しない（未生成）
- kim専用の雑魚敵は存在しない（GLO汎用敵 `enemy_glo_00001` を使用）

---

## コンテンツ種別一覧

### 1day（1日1コマ）

| ingame_id | bgm_asset_key | boss_bgm_asset_key | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef |
|-----------|---------------|-------------------|------------|----------------------|--------------------------|
| event_kim1_1day_00001 | SSE_SBG_003_002 | — | 500 | 1.0 | 1.0 |

> コマ行数: 2行（glo_00011 x2）

### challenge（チャレンジ）

| ingame_id | bgm_asset_key | boss_bgm_asset_key | outpost_hp | コマ背景アセット |
|-----------|---------------|-------------------|------------|----------------|
| event_kim1_challenge_00001 | SSE_SBG_003_009 | SSE_SBG_003_007 | 30,000 | glo_00011（3行） |
| event_kim1_challenge_00002 | SSE_SBG_003_009 | SSE_SBG_003_007 | 40,000 | glo_00011（3行） |
| event_kim1_challenge_00003 | SSE_SBG_003_009 | SSE_SBG_003_007 | 60,000 | glo_00011（3行） |
| event_kim1_challenge_00004 | SSE_SBG_003_009 | SSE_SBG_003_007 | 80,000 | kim_00001（4行） |

> challenge_00004 のみ kim専用背景アセット（kim_00001）を使用

### charaget01（キャラゲット01）

| ingame_id | bgm_asset_key | boss_bgm_asset_key | outpost_hp | コマ背景アセット |
|-----------|---------------|-------------------|------------|----------------|
| event_kim1_charaget01_00001 | SSE_SBG_003_002 | SSE_SBG_003_004 | 15,000 | glo_00029（2行） |
| event_kim1_charaget01_00002 | SSE_SBG_003_002 | SSE_SBG_003_004 | 25,000 | glo_00013（2行） |
| event_kim1_charaget01_00003 | SSE_SBG_003_002 | SSE_SBG_003_004 | 30,000 | glo_00014（3行） |
| event_kim1_charaget01_00004 | SSE_SBG_003_002 | SSE_SBG_003_004 | 45,000 | glo_00011（3行） |

### charaget02（キャラゲット02）

| ingame_id | bgm_asset_key | boss_bgm_asset_key | outpost_hp | コマ背景アセット |
|-----------|---------------|-------------------|------------|----------------|
| event_kim1_charaget02_00001 | SSE_SBG_003_002 | SSE_SBG_003_004 | 25,000 | glo_00011（2行） |
| event_kim1_charaget02_00002 | SSE_SBG_003_002 | SSE_SBG_003_004 | 25,000 | glo_00011（2行） |
| event_kim1_charaget02_00003 | SSE_SBG_003_002 | SSE_SBG_003_004 | 30,000 | glo_00011（2行） |
| event_kim1_charaget02_00004 | SSE_SBG_003_002 | SSE_SBG_003_004 | 30,000 | glo_00011（2行） |
| event_kim1_charaget02_00005 | SSE_SBG_003_002 | SSE_SBG_003_004 | 40,000 | kim_00002（3行） |
| event_kim1_charaget02_00006 | SSE_SBG_003_002 | SSE_SBG_003_004 | 45,000 | kim_00001（3行） |

> charaget02_00005/00006 のみ kim専用背景アセットを使用

### savage（サベージ）

| ingame_id | bgm_asset_key | outpost_hp | コマ背景アセット |
|-----------|---------------|------------|----------------|
| event_kim1_savage_00001 | SSE_SBG_003_007 | 120,000 | glo_00011（3行） |
| event_kim1_savage_00002 | SSE_SBG_003_007 | 150,000 | glo_00011（4行） |
| event_kim1_savage_00003 | SSE_SBG_003_007 | 200,000 | kim_00001（3行） |

### pvp / raid

| ingame_id | bgm_asset_key | outpost_hp | コマ背景アセット |
|-----------|---------------|------------|----------------|
| pvp_kim_01 | SSE_SBG_003_007 | 100 | kim_00001（3行） |
| pvp_kim_02 | SSE_SBG_003_007 | 100 | 不明（pvp用共通） |
| raid_kim1_00001 | SSE_SBG_003_008 | 1,000,000 | 不明（raid用） |

---

## エネミーID → 日本語名対応表

### キャラエネミー

| asset_key | 日本語名 |
|-----------|---------|
| chara_kim_00001 | 溢れる母性 花園 羽々里（URキャラ） |
| chara_kim_00101 | 花園 羽香里 |
| chara_kim_00201 | 院田 唐音 |
| chara_kim_00301 | 好本 静 |

### 雑魚エネミー

kim専用の雑魚エネミー（`enemy_kim_*`）は存在しない。全コンテンツで GLO汎用エネミー `enemy_glo_00001` を使用。

---

## エネミー別パラメータ詳細

### キャラエネミー（chara_kim系）

| id | mst_enemy_character_id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|------------------------|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| c_kim_00001_kim1_advent_Boss_Red | chara_kim_00001（溢れる母性 花園 羽々里） | Boss | Technical | Red | 10,000 | 100 | 35 | 5 |
| c_kim_00001_kim1_challenge_Boss_Red | chara_kim_00001 | Boss | Defense | Red | 50,000 | 100 | 40 | 5 |
| c_kim_00001_kim1_charaget02_Boss_Red | chara_kim_00001 | Boss | Defense | Red | 10,000 | 100 | 40 | 5 |
| c_kim_00001_kim1_savage03_Boss_Red | chara_kim_00001 | Boss | Defense | Red | 50,000 | 300 | 40 | 3 |
| c_kim_00101_kim1_1d1c_Normal_Colorless | chara_kim_00101（花園 羽香里） | Normal | Attack | Colorless | 10,000 | 100 | 35 | 6 |
| c_kim_00101_kim1_advent_Boss_Red | chara_kim_00101 | Boss | Attack | Red | 10,000 | 100 | 35 | 4 |
| c_kim_00101_kim1_advent_Normal_Red | chara_kim_00101 | Normal | Attack | Red | 1,000 | 100 | 35 | 4 |
| c_kim_00101_kim1_challenge_Boss_Green | chara_kim_00101 | Boss | Attack | Green | 50,000 | 300 | 35 | 4 |
| c_kim_00101_kim1_challenge_Normal_Green | chara_kim_00101 | Normal | Attack | Green | 10,000 | 300 | 35 | 4 |
| c_kim_00101_kim1_charaget01_Boss_Colorless | chara_kim_00101 | Boss | Attack | Colorless | 10,000 | 100 | 30 | 5 |
| c_kim_00101_kim1_charaget01_Normal_Colorless | chara_kim_00101 | Normal | Attack | Colorless | 10,000 | 100 | 30 | 5 |
| c_kim_00101_kim1_charaget02_Boss_Red | chara_kim_00101 | Boss | Attack | Red | 10,000 | 100 | 30 | 5 |
| c_kim_00101_kim1_savage01_Boss_Blue | chara_kim_00101 | Boss | Attack | Blue | 100,000 | 500 | 30 | 7 |
| c_kim_00101_kim1_savage02_Boss_Yellow | chara_kim_00101 | Boss | Defense | Yellow | 100,000 | 500 | 30 | 5 |
| c_kim_00101_kim1_savage03_Boss_Red | chara_kim_00101 | Boss | Attack | Red | 100,000 | 500 | 30 | 5 |
| c_kim_00201_kim1_1d1c_Normal_Colorless | chara_kim_00201（院田 唐音） | Normal | Technical | Colorless | 10,000 | 100 | 34 | 6 |
| c_kim_00201_kim1_advent_Boss_Red | chara_kim_00201 | Boss | Technical | Red | 10,000 | 100 | 35 | 6 |
| c_kim_00201_kim1_challenge_Boss_Red | chara_kim_00201 | Boss | Technical | Red | 50,000 | 300 | 32 | 4 |
| c_kim_00201_kim1_challenge_Normal_Red | chara_kim_00201 | Normal | Technical | Red | 10,000 | 300 | 32 | 4 |
| c_kim_00201_kim1_charaget01_Boss_Colorless | chara_kim_00201 | Boss | Technical | Colorless | 10,000 | 100 | 35 | 4 |
| c_kim_00201_kim1_charaget01_Normal_Colorless | chara_kim_00201 | Normal | Technical | Colorless | 10,000 | 100 | 35 | 4 |
| c_kim_00201_kim1_charaget02_Boss_Red | chara_kim_00201 | Boss | Technical | Red | 10,000 | 100 | 35 | 4 |
| c_kim_00201_kim1_savage01_Boss_Blue | chara_kim_00201 | Boss | Technical | Blue | 100,000 | 500 | 35 | 5 |
| c_kim_00201_kim1_savage02_Boss_Yellow | chara_kim_00201 | Boss | Technical | Yellow | 100,000 | 500 | 35 | 5 |
| c_kim_00201_kim1_savage03_Boss_Red | chara_kim_00201 | Boss | Technical | Red | 100,000 | 500 | 35 | 5 |
| c_kim_00301_kim1_advent_Boss_Yellow | chara_kim_00301（好本 静） | Boss | Support | Yellow | 10,000 | 100 | 32 | 3 |
| c_kim_00301_kim1_advent_Normal_Yellow | chara_kim_00301 | Normal | Support | Yellow | 1,000 | 100 | 32 | 3 |
| c_kim_00301_kim1_challenge_Boss_Yellow | chara_kim_00301 | Boss | Support | Yellow | 50,000 | 300 | 40 | 5 |
| c_kim_00301_kim1_charaget01_Boss_Colorless | chara_kim_00301 | Boss | Support | Colorless | 10,000 | 100 | 32 | 5 |
| c_kim_00301_kim1_charaget02_Boss_Red | chara_kim_00301 | Boss | Support | Red | 10,000 | 100 | 32 | 5 |
| c_kim_00301_kim1_savage02_Boss_Yellow | chara_kim_00301 | Boss | Support | Yellow | 100,000 | 300 | 32 | 4 |
| c_kim_00301_kim1_savage03_Boss_Red | chara_kim_00301 | Boss | Support | Red | 100,000 | 300 | 32 | 4 |

### GLO汎用エネミー（enemy_glo_00001 kim用パラメータ）

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| e_glo_00001_kim1_advent_Boss_Red | Boss | Attack | Red | 10,000 | 100 | 35 | 1 |
| e_glo_00001_kim1_advent_Normal_Yellow | Normal | Defense | Yellow | 1,000 | 100 | 40 | 1 |
| e_glo_00001_kim1_challenge_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 100 | 40 | 1 |
| e_glo_00001_kim1_challenge_Normal_Red | Normal | Attack | Red | 10,000 | 100 | 40 | 1 |
| e_glo_00001_kim1_charaget01_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 100 | 40 | 1 |
| e_glo_00001_kim1_charaget02_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 100 | 40 | 1 |
| e_glo_00001_kim1_savage_Normal_Blue | Normal | Defense | Blue | 100,000 | 200 | 32 | 1 |
| e_glo_00001_kim1_savage_Normal_Red | Normal | Attack | Red | 100,000 | 200 | 40 | 1 |
| e_glo_00001_kim1_savage_Normal_Yellow | Normal | Attack | Yellow | 100,000 | 200 | 40 | 1 |

---

## シーケンスパターン

### event_kim1_1day_00001（2イベント）

- `c_kim_00101_kim1_1d1c_Normal_Colorless`（花園 羽香里）: InitialSummon, position 0.8
- `c_kim_00201_kim1_1d1c_Normal_Colorless`（院田 唐音）: InitialSummon, position 1.6
- 2体同時配置のシンプルな構成

### event_kim1_challenge_00001（8イベント）

- Boss: `c_kim_00101_kim1_challenge_Boss_Green`（花園 羽香里 Green Boss）初期配置（position 2.4）
- 雑魚: `e_glo_00001_kim1_challenge_Normal_Colorless` 初期配置（position 2.15）
- その後 EnterTargetKomaIndex4 / ElapsedTime300 で雑魚追加
- FriendUnitDead4: 雑魚 x7（間隔1,000）
- ElapsedTime800: 雑魚 x2（間隔2,200）
- ElapsedTime7300: 雑魚 x10（間隔1,300）
- FriendUnitDead2: 雑魚 x1

### event_kim1_challenge_00002（9イベント）

- Boss: `c_kim_00201_kim1_challenge_Boss_Red`（院田 唐音 Red Boss）ElapsedTime2000
- 初期: `e_glo_00001_kim1_challenge_Normal_Red` x2（position 0.7 / 2.4）
- FriendUnitDead2: 雑魚Red x7（間隔800）、その後Colorless追加
- ElapsedTime7000: Colorless x1
- ElapsedTime7200: Red x6（間隔1,000）

### event_kim1_challenge_00003（10イベント）

- Boss: `c_kim_00301_kim1_challenge_Boss_Yellow`（好本 静 Yellow Boss）初期配置（position 0.85）
- 雑魚: Colorless / Red 混合
- FriendUnitDead3以降で雑魚量産

### event_kim1_challenge_00004（12イベント、URキャラ登場）

- Boss: `c_kim_00001_kim1_challenge_Boss_Red`（溢れる母性 花園 羽々里 / URキャラ）FriendUnitDead2
- 初期: `c_kim_00101_kim1_challenge_Normal_Green`、`c_kim_00201_kim1_challenge_Normal_Red`、`e_glo_00001`x2
- EnterTargetKomaIndex2: 雑魚Red x3段階的追加
- FriendUnitDead2: Colorless / Red 量産
- ElapsedTime7000: Red x10（間隔2,000）
- kim専用背景（kim_00001）使用、4コマ行

### event_kim1_charaget01_00001（3イベント）

- ElapsedTime1000: `c_kim_00101_kim1_charaget01_Boss_Colorless` x1
- ElapsedTime2000: `c_kim_00201_kim1_charaget01_Boss_Colorless` x1
- ElapsedTime300: `e_glo_00001_kim1_charaget01_Normal_Colorless` x15（間隔500）
- Bossを時間差で2体配置、雑魚15体でボリューム出す構成

### event_kim1_savage_00001（7イベント）

- 初期: `e_glo_00001_kim1_savage_Normal_Blue`（HP 100,000）x1（position）
- FriendUnitDead1 -> グループw1切り替え
- w1グループ: Blue x50（間隔450）、Blue x5（間隔1,250）、Blue x50（間隔725）
- ElapsedTimeSinceGroupActivated1000: `c_kim_00101_kim1_savage01_Boss_Blue` x1
- ElapsedTimeSinceGroupActivated900: `c_kim_00201_kim1_savage01_Boss_Blue` x1
- 高HPの雑魚大量 + キャラBoss2体の構成

### event_kim1_savage_00002（9イベント）

- ElapsedTime0: `e_glo_00001_kim1_savage_Normal_Yellow`（HP 100,000）x60（間隔500）
- 以降ElapsedTime1000/1600/2100/2800/3600で追加（合計360体以上の大量生成）
- ElapsedTime1200: `c_kim_00101_kim1_savage02_Boss_Yellow` x1
- ElapsedTime2000: `c_kim_00201_kim1_savage02_Boss_Yellow` x1
- ElapsedTime2500: `c_kim_00301_kim1_savage02_Boss_Yellow` x1
- Yellow雑魚大量 + 3キャラBoss順次登場

### event_kim1_savage_00003（11イベント、URキャラ登場）

- ElapsedTime0: `e_glo_00001_kim1_savage_Normal_Red` x6（間隔300）
- ElapsedTime1500: Red x30（間隔650）
- ElapsedTime1600: `c_kim_00001_kim1_savage03_Boss_Red`（URキャラ）x1
- FriendUnitDead3: グループw1切り替え
- w1: Red x50（間隔800）、Red x50（間隔600）、Red単体
- FriendUnitDead6（3回）: 00101/00201/00301 savage03_Boss_Red 各1体
- FriendUnitDead6(10番目): URキャラ再登場
- kim専用背景（kim_00001）使用

---

## コンテンツ種別ごとの特徴比較

### OutpostHP スケール

| 種別 | 範囲 | 備考 |
|------|------|------|
| 1day | 500 | 超軽量 |
| charaget01 | 15,000〜45,000 | +5,000〜10,000/ステージ |
| charaget02 | 25,000〜45,000 | 前半固定、後半増加 |
| challenge | 30,000〜80,000 | +10,000〜20,000/ステージ |
| savage | 120,000〜200,000 | +30,000〜50,000/ステージ |
| raid | 1,000,000 | 最大規模 |

### BGMパターン

| 種別 | BGM | ボスBGM |
|------|-----|---------|
| 1day / charaget | SSE_SBG_003_002 | SSE_SBG_003_004 |
| challenge | SSE_SBG_003_009 | SSE_SBG_003_007 |
| savage / pvp | SSE_SBG_003_007 | — |
| raid | SSE_SBG_003_008 | — |

### 背景アセットパターン

| 背景アセット | 使用コンテンツ |
|-------------|--------------|
| glo_00011 | challenge_00001〜00003 / charaget01_00004 / charaget02_00001〜00004 / savage_00001〜00002 / 1day_00001 |
| glo_00029 | charaget01_00001 |
| glo_00013 | charaget01_00002 |
| glo_00014 | charaget01_00003 |
| kim_00001 | challenge_00004 / charaget02_00006 / savage_00003 / pvp_kim_01 |
| kim_00002 | charaget02_00005 |

> kim専用背景は `kim_00001`（メイン）と `kim_00002` の2種類が確認されている。

### 使用エネミー比較

| カテゴリ | 1day/charaget | challenge | savage | raid |
|---------|--------------|-----------|--------|------|
| enemy_glo_00001 | 使用（Normal_Colorless） | 使用（Normal_Colorless/Red） | 使用（Normal_Blue/Red/Yellow） | 使用（Normal_Yellow） |
| chara_kim_00001（URキャラ） | 未使用 | 使用（challenge_00004 Boss） | 使用（savage_00003 Boss） | 未使用 |
| chara_kim_00101 | 使用（Normal） | 使用（Normal/Boss） | 使用（Boss） | 使用（Normal） |
| chara_kim_00201 | 使用（Normal） | 使用（Normal/Boss） | 使用（Boss） | 使用（Boss） |
| chara_kim_00301 | 使用（Boss） | 使用（Boss） | 使用（Boss） | 使用（Boss/Normal） |

---

## dungeon設計向けの推奨パラメータ

### 基本方針

kim作品は**専用雑魚エネミーが存在しない**（kim専用の `enemy_kim_*` が未定義）ため、CLAUDE.mdの仕様に基づき**GLO汎用エネミー `enemy_glo_00001`** を雑魚として使用する。

### normalブロック（dungeon_kim_normal_00001）向け推奨

| 項目 | 推奨値 | 根拠 |
|------|--------|------|
| 雑魚エネミー（メイン） | `e_glo_00001_kim1_challenge_Normal_Colorless` | challenge系で使用、HP 10,000 / ATK 100 |
| 雑魚エネミー（サブ） | `e_glo_00001_kim1_challenge_Normal_Red` | challenge系で使用、HP 10,000 / ATK 100 |
| アウトポストHP | 100（dungeon仕様固定） | CLAUDE.md仕様 |
| OutpostHP係数 | 1.0 | 全コンテンツ共通 |
| BGM | SSE_SBG_003_002 | charaget系で使用 |
| 背景アセット | kim_00001 | kim専用背景（メインアセット） |
| コマ行数 | 3行 | dungeon仕様（normalブロック固定） |

### bossブロック（dungeon_kim_boss_00001）向け推奨

| 項目 | 推奨値 | 根拠 |
|------|--------|------|
| ボスエネミー | `c_kim_00001_kim1_challenge_Boss_Red` | URキャラ（溢れる母性 花園 羽々里）、HP 50,000 / Defense / Red |
| 雑魚（補助） | `e_glo_00001_kim1_challenge_Normal_Colorless` | challenge系との統一感 |
| アウトポストHP | 1,000（dungeon仕様固定） | CLAUDE.md仕様 |
| BGM | SSE_SBG_003_009 | challenge系で使用（ボス感） |
| ボスBGM | SSE_SBG_003_007 | challenge系のボスBGM |
| 背景アセット | kim_00001 | kim専用背景 |
| コマ行数 | 1行 | dungeon仕様（bossブロック固定） |

### 参考HP値（既存コンテンツより）

| エネミー | challenge用HP | savage用HP | 特記 |
|---------|--------------|-----------|------|
| e_glo_00001（Normal/Colorless） | 10,000 | — | challenge標準 |
| e_glo_00001（Normal/Blue） | — | 100,000 | savage標準 |
| c_kim_00001 Boss（URキャラ） | 50,000 | 50,000 | challenge/savage03 |
| c_kim_00101 Boss | 50,000 | 100,000 | challenge/savage |
| c_kim_00201 Boss | 50,000 | 100,000 | challenge/savage |
| c_kim_00301 Boss | 50,000 | 100,000 | challenge/savage |

---

## まとめ・パターン特徴

1. **kim専用雑魚エネミーなし**: `enemy_kim_*` は存在しない。全コンテンツで `enemy_glo_00001` を使用している。dungeon生成でも同様にGLO汎用敵を採用する。

2. **BGMは2系統**: charaget/1day系（SSE_SBG_003_002、ボス時 SSE_SBG_003_004）と challenge/savage系（SSE_SBG_003_009 / SSE_SBG_003_007）に分かれる。

3. **kim専用背景アセット**: `kim_00001`（メイン）と `kim_00002` の2種類が存在。上位コンテンツ（challenge_00004, charaget02_00005〜00006, savage_00003, pvp）で使用されており、dungeon向けに最適。

4. **URキャラは最高難易度コンテンツに登場**: `chara_kim_00001`（溢れる母性 花園 羽々里）は challenge_00004 と savage_00003 にのみ登場する。dungeonのbossブロックに最適。

5. **全コンテンツでcoef=1.0**: normal_enemy_hp_coef / attack_coef / boss_enemy_hp_coef / attack_coef が全て 1.0。パラメータID自体にHP・攻撃力が設定されているため、coef調整は行わない設計。

6. **savageは大量召喚型**: savage_00002 では Yellow雑魚を1波あたり60体 x 6波 = 360体以上召喚する大規模設計。

7. **キャラ4種が対称的に使われる**: 00101（花園 羽香里）/ 00201（院田 唐音）/ 00301（好本 静）がほぼ同等の扱いで各コンテンツに登場。00001（URキャラ）は最上位コンテンツのラスボスとして登場。

8. **dungeonコンテンツは未生成**: dungeon_kim_normal_00001 / dungeon_kim_boss_00001 は `/masterdata-ingame-creator` スキルで生成予定。
