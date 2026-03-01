# 幼稚園WARS インゲームパターン分析

## 概要

- series_id: you
- URキャラ: chara_you_00001 (元殺し屋の新人教諭 リタ / Attack / Red)
- インゲームコンテンツ数: 23件
  - event_you1_1day x1
  - event_you1_challenge x4
  - event_you1_charaget01 x6
  - event_you1_charaget02 x6
  - event_you1_savage x3
  - pvp_you x2
  - raid_you1 x1
- dungeonコンテンツ: 現時点では存在しない（未生成）
- 背景アセット: loop_background_asset_key は全コンテンツで空（背景アセットなし）
- BGM: コンテンツ種別によって異なる（後述）

---

## コンテンツ種別一覧とアウトポストHP

| ingame_id | BGM | boss_BGM | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef | boss_enemy_hp_coef | boss_enemy_attack_coef |
|-----------|-----|----------|------------|----------------------|--------------------------|--------------------|-----------------------|
| event_you1_1day_00001 | SSE_SBG_003_001 | - | 500 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_challenge_00001 | SSE_SBG_003_001 | SSE_SBG_003_004 | 30,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_challenge_00002 | SSE_SBG_003_001 | SSE_SBG_003_004 | 40,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_challenge_00003 | SSE_SBG_003_001 | SSE_SBG_003_004 | 60,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_challenge_00004 | SSE_SBG_003_001 | SSE_SBG_003_004 | 80,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget01_00001 | SSE_SBG_003_001 | SSE_SBG_003_004 | 30,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget01_00002 | SSE_SBG_003_001 | SSE_SBG_003_004 | 35,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget01_00003 | SSE_SBG_003_001 | SSE_SBG_003_004 | 40,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget01_00004 | SSE_SBG_003_001 | SSE_SBG_003_004 | 45,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget01_00005 | SSE_SBG_003_001 | SSE_SBG_003_004 | 50,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget01_00006 | SSE_SBG_003_001 | SSE_SBG_003_004 | 60,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget02_00001 | SSE_SBG_003_002 | - | 7,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget02_00002 | SSE_SBG_003_002 | - | 10,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget02_00003 | SSE_SBG_003_002 | - | 12,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget02_00004 | SSE_SBG_003_001 | SSE_SBG_003_004 | 12,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget02_00005 | SSE_SBG_003_001 | SSE_SBG_003_004 | 40,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_charaget02_00006 | SSE_SBG_003_001 | SSE_SBG_003_004 | 70,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_savage_00001 | SSE_SBG_003_007 | - | 100,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_savage_00002 | SSE_SBG_003_007 | - | 150,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| event_you1_savage_00003 | SSE_SBG_003_007 | - | 200,000 | 1.0 | 1.0 | 1.0 | 1.0 |
| pvp_you_01 | SSE_SBG_003_007 | - | pvp（固定） | 1.0 | 1.0 | 1.0 | 1.0 |
| pvp_you_02 | SSE_SBG_003_007 | - | pvp（固定） | 1.0 | 1.0 | 1.0 | 1.0 |
| raid_you1_00001 | SSE_SBG_003_008 | - | 1,000,000 | 1.0 | 1.0 | 1.0 | 1.0 |

> 全コンテンツで normal_enemy_hp_coef / normal_enemy_attack_coef = 1.0。難易度調整はシーケンス側の enemy_hp_coef / enemy_attack_coef で個別に行っている。

### BGMサマリ

| BGM | 使用コンテンツ |
|-----|---------------|
| SSE_SBG_003_001 | 1day / challenge / charaget01 / charaget02（00001〜00003） |
| SSE_SBG_003_002 | charaget02_00001〜00003 |
| SSE_SBG_003_004 | ボスBGM（challenge / charaget01 / charaget02_00004〜00006） |
| SSE_SBG_003_007 | savage / pvp |
| SSE_SBG_003_008 | raid |

---

## エネミーID → 日本語名対応表

| asset_key | 日本語名 |
|-----------|---------|
| chara_you_00001 | 元殺し屋の新人教諭 リタ（URキャラ） |
| chara_you_00101 | ルーク |
| chara_you_00201 | ダグ |
| chara_you_00301 | ハナ |
| enemy_you_00001 | 不良系金髪イケメン |
| enemy_you_00101 | イケメンじゃない殺し屋 |
| enemy_glo_00001 | ファントム（GLO汎用敵） |

---

## エネミー別パラメータ詳細

### MstEnemyStageParameter（エネミーステージパラメータ）

> 全42件のパラメータが存在する。主要なものを以下に整理する。

#### enemy_you_00001（不良系金髪イケメン）系

| id | unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|-----------|-----------|-------|----|--------------|------------|---------------------|
| e_you_00001_you1_advent_Normal_Green | Normal | Defense | Green | 1,000 | 100 | 37 | 0 |
| e_you_00001_you1_advent_Boss_Colorless | Boss | Defense | Colorless | 10,000 | 100 | 37 | 0 |
| e_you_00001_you1_charaget01_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 100 | 37 | 1 |
| e_you_00001_you1_charaget02_Normal_Red | Normal | Defense | Red | 1,000 | 100 | 37 | 1 |
| e_you_00001_you1_savage01_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 100 | 37 | 0 |
| e_you_00001_you1_savage01_Normal_Green | Normal | Attack | Green | 10,000 | 100 | 37 | 0 |
| e_you_00001_you1_savage01_02_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 80 | 37 | 0 |

#### enemy_you_00101（イケメンじゃない殺し屋）系

| id | unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|-----------|-----------|-------|----|--------------|------------|---------------------|
| e_you_00101_you1_advent_Normal_Green | Normal | Attack | Green | 1,000 | 100 | 30 | 0 |
| e_you_00101_you1_advent_Boss_Colorless | Boss | Attack | Colorless | 10,000 | 100 | 30 | 0 |
| e_you_00101_you1_charaget01_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 100 | 30 | 1 |
| e_you_00101_you1_charaget02_Normal_Red | Normal | Attack | Red | 1,000 | 100 | 30 | 1 |
| e_you_00101_you1_savage01_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 100 | 30 | 0 |
| e_you_00101_you1_savage01_Normal_Green | Normal | Attack | Green | 10,000 | 100 | 30 | 0 |

#### chara_you_00001（元殺し屋の新人教諭 リタ / URキャラ）系

| id | unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|-----------|-----------|-------|----|--------------|------------|---------------------|
| c_you_00001_you1_1d1c_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 100 | 45 | 6 |
| c_you_00001_you1_advent_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 100 | 45 | 4 |
| c_you_00001_you1_advent_Boss_Green | Boss | Attack | Green | 10,000 | 100 | 45 | 4 |
| c_you_00001_you1_challenge_Boss_Green | Boss | Attack | Green | 10,000 | 500 | 45 | 5 |
| c_you_00001_you1_charaget01_Boss_Colorless | Boss | Attack | Colorless | 10,000 | 100 | 45 | 6 |
| c_you_00001_you1_charaget02_Boss_Red | Boss | Attack | Red | 10,000 | 500 | 45 | 9 |
| c_you_00001_you1_savage01_Boss_Red | Boss | Attack | Red | 50,000 | 500 | 45 | 4 |

#### chara_you_00101（ルーク）系

| id | unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|-----------|-----------|-------|----|--------------|------------|---------------------|
| c_you_00101_you1_advent_Boss_Green | Boss | Technical | Green | 10,000 | 100 | 40 | 5 |
| c_you_00101_you1_challenge_Boss_Blue | Boss | Technical | Blue | 10,000 | 500 | 40 | 5 |
| c_you_00101_you1_savage01_Boss_Blue | Boss | Technical | Blue | 50,000 | 500 | 40 | 5 |
| c_you_00101_you1_savage01_Boss_Green | Boss | Technical | Green | 50,000 | 500 | 40 | 5 |

#### chara_you_00201（ダグ）系

| id | unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|-----------|-----------|-------|----|--------------|------------|---------------------|
| c_you_00201_you1_advent_Normal_Green | Normal | Technical | Green | 1,000 | 100 | 32 | 4 |
| c_you_00201_you1_advent_Boss_Green | Boss | Technical | Green | 10,000 | 100 | 32 | 4 |
| c_you_00201_you1_challenge_Normal_Green | Normal | Technical | Green | 10,000 | 500 | 32 | 1 |
| c_you_00201_you1_challenge_Boss_Green | Boss | Technical | Green | 10,000 | 500 | 32 | 6 |
| c_you_00201_you1_charaget01_Boss_Yellow | Boss | Technical | Yellow | 10,000 | 100 | 35 | 5 |
| c_you_00201_you1_charaget02_Boss_Red | Boss | Attack | Red | 10,000 | 500 | 32 | 7 |
| c_you_00201_you1_savage01_Boss_Green | Boss | Attack | Green | 50,000 | 500 | 32 | 4 |

#### chara_you_00301（ハナ）系

| id | unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|-----------|-----------|-------|----|--------------|------------|---------------------|
| c_you_00301_you1_advent_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 100 | 35 | 3 |
| c_you_00301_you1_advent_Boss_Green | Boss | Attack | Green | 10,000 | 100 | 35 | 3 |
| c_you_00301_you1_challenge_Normal_Red | Normal | Defense | Red | 10,000 | 500 | 35 | 1 |
| c_you_00301_you1_challenge_Boss_Green | Boss | Attack | Green | 10,000 | 500 | 35 | 8 |
| c_you_00301_you1_charaget02_Normal_Red | Normal | Technical | Red | 1,000 | 100 | 35 | 1 |
| c_you_00301_you1_charaget02_Boss_Red | Boss | Technical | Red | 10,000 | 500 | 35 | 13 |
| c_you_00301_you1_savage01_Boss_Blue | Boss | Technical | Blue | 50,000 | 500 | 35 | 4 |
| c_you_00301_you1_savage01_Boss_Green | Boss | Technical | Green | 50,000 | 500 | 35 | 4 |

#### GLO汎用敵（e_glo_00001）系（challenge専用）

| id | unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|-----------|-----------|-------|----|--------------|------------|---------------------|
| e_glo_00001_you1_challenge_Normal_Blue | Normal | Defense | Blue | 1,000 | 100 | 30 | 1 |
| e_glo_00001_you1_challenge_Normal_Green | Normal | Defense | Green | 1,000 | 100 | 30 | 1 |
| e_glo_00001_you1_challenge_Normal_Red | Normal | Attack | Red | 1,000 | 100 | 30 | 1 |

> GLO汎用敵の基本HP は 1,000 だが、シーケンス側の enemy_hp_coef（22〜44.5倍）で大幅に強化されている。

---

## シーケンスパターン

### event_you1_charaget01 シリーズ（charaget01）

#### charaget01_00001（3イベント）

- 主なエネミー: `c_you_00201_you1_charaget01_Boss_Yellow`（InitialSummon / coef 1.5 / attack_coef 3）、`e_you_00001_you1_charaget01_Normal_Colorless`（×5 / coef 5 / atk 2）、`e_you_00101_you1_charaget01_Normal_Yellow`（×11 / coef 5 / atk 2）
- 特徴: InitialSummonでダグ（Boss）が登場、その後に雑魚量産型

#### charaget01_00002（4イベント）

- 主なエネミー: `c_you_00201_you1_charaget01_Boss_Yellow`（OutpostHPPercentage99% / coef 2）、`c_you_00001_you1_1d1c_Normal_Colorless`（リタ Normal / coef 1.5）、雑魚2種
- 特徴: アウトポストHP99%で即ボス登場、リタ（URキャラ）がNormalユニットとして登場

#### charaget01_00003（4イベント）

- 主なエネミー: `c_you_00201_you1_charaget01_Boss_Yellow`（coef 4）、雑魚2種（coef 8）
- 特徴: OutpostHPPercentage99%トリガー、coef増加で強化

#### charaget01_00004（5イベント）

- 主なエネミー: `c_you_00001_you1_charaget01_Boss_Colorless`（リタ Boss / coef 4）、雑魚2種、Fall4アニメーションで登場
- 特徴: リタ（URキャラ）がBossユニットとして登場（coef 4 / attack_coef 6）

#### charaget01_00005（6イベント）

- 主なエネミー: `c_you_00001_you1_charaget01_Boss_Colorless`（ElapsedTime4500 / coef 5.5）、`c_you_00201_you1_charaget01_Boss_Yellow`（ElapsedTime3000 / coef 8.5）、雑魚+Fall4
- 特徴: 2体ボスが時間差で登場、高coef化

#### charaget01_00006（7イベント）

- 主なエネミー: `c_you_00001_you1_charaget01_Boss_Colorless`（coef 7）、`c_you_00201_you1_charaget01_Boss_Yellow`（coef 7）、雑魚大量（×99）
- 特徴: 2体ボス + 大量雑魚、最高難易度

---

### event_you1_challenge シリーズ

#### challenge_00001（4イベント + GroupChange）

- 主なエネミー: `c_you_00201_you1_challenge_Boss_Green`（ダグ Boss / coef 11.9 / attack_coef 1.2）、`e_glo_00001_you1_challenge_Normal_Green`（GLO汎用 / coef 26.6）
- 特徴: FriendUnitDead1でSequenceGroup切替（w1グループに遷移）
- 進行: InitialSummonでダグ登場 → ElapsedTime400/1500でGLO汎用が追加

#### challenge_00002（7イベント）

- 主なエネミー: `c_you_00201_you1_challenge_Boss_Green`（coef 7）、`c_you_00301_you1_challenge_Boss_Green`（ハナ Boss / coef 16）、GLO汎用（coef 27、delay）
- 特徴: 2体ボスが同時登場（delay違い）、OutpostDamage5000ごとにGLO汎用追加

#### challenge_00003（6イベント）

- 主なエネミー: `c_you_00101_you1_challenge_Boss_Blue`（ルーク Boss / coef 31.9）、GLO汎用Blue（coef 22、×10量産型）
- 特徴: InitialSummonでルークとGLO汎用が同時多数登場

#### challenge_00004（12イベント + GroupChange多段）

- 主なエネミー: `c_you_00301_you1_challenge_Normal_Red`（ハナ Normal / coef 55）、`c_you_00201_you1_challenge_Normal_Green`（ダグ Normal / coef 34）、`c_you_00001_you1_challenge_Boss_Green`（リタ Boss / coef 44.5）、GLO汎用Red/Green
- 特徴: w1→w2→w3と3段階のSequenceGroup切替。最終ウェーブでリタ（URキャラ）が登場

---

### event_you1_charaget02 シリーズ

#### charaget02_00001（1イベント）

- 主なエネミー: `c_you_00301_you1_charaget02_Normal_Red`（ハナ Normal / coef 20 / attack_coef 3.27）
- 特徴: シンプルな1エネミー構成

#### charaget02_00002〜00003（2〜5イベント）

- 主なエネミー: `e_you_00101_you1_charaget02_Normal_Red`（×99量産型、interval 980〜1000）
- 特徴: イケメンじゃない殺し屋の大量出現型

#### charaget02_00004（5イベント）

- 主なエネミー: `e_you_00001_you1_charaget02_Normal_Red`、`e_you_00101_you1_charaget02_Normal_Red`、`c_you_00301_you1_charaget02_Boss_Red`（ハナ Boss / coef 8.4）

#### charaget02_00005（6イベント）

- 主なエネミー: 雑魚2種 + `c_you_00301_you1_charaget02_Boss_Red`（ハナ Boss / coef 6.5）+ `c_you_00001_you1_charaget02_Boss_Red`（リタ Boss / coef 8）
- 特徴: FriendUnitDead6でリタ（URキャラ）登場

#### charaget02_00006（16イベント + GroupChange）

- 主なエネミー: 雑魚2種（×99量産型）+ OutpostHPPercentage70%でw1グループ切替
  - w1: `c_you_00001_you1_charaget02_Boss_Red`（リタ / coef 17）、`c_you_00201_you1_charaget02_Boss_Red`（ダグ / coef 13）、`c_you_00301_you1_charaget02_Boss_Red`（ハナ / coef 9）が時間差登場
  - OutpostHPPercentage30%以降は雑魚量産型継続
- 特徴: 最も複雑なシーケンス。ボス3体が連続登場し、量産型も継続して出現

---

### event_you1_savage シリーズ（savage）

#### savage_00001（19イベント / OutpostHP 100,000）

- 初期出現（InitialSummon）: `c_you_00201_you1_savage01_Boss_Green`（ダグ / coef 3.8）、`e_you_00001_you1_savage01_Normal_Green`×2体、`e_you_00101_you1_savage01_Normal_Green`×1体（配置固定）
- FriendUnitDead1: `c_you_00301_you1_savage01_Boss_Blue`（ハナ / coef 3.2）、雑魚2種（delay50）
- FriendUnitDead4: `c_you_00301_you1_savage01_Boss_Blue`（coef 4.2）、`c_you_00201_you1_savage01_Boss_Green`（coef 4.7）、雑魚Green多数（delay300）
- ElapsedTime（後半）: Colorless系の雑魚が大量出現
- 特徴: ボス2体（ダグ・ハナ）が波状攻撃、最終的にColorless雑魚の掃討戦

#### savage_00002（16イベント / OutpostHP 150,000）

- InitialSummon: `c_you_00001_you1_savage01_Boss_Red`（リタ / coef 38）が先頭で登場
- ElapsedTime5300: `c_you_00101_you1_savage01_Boss_Blue`（ルーク / coef 8）追加
- EnterTargetKomaIndex2/5: Fall0アニメで追加エネミー
- FriendUnitDead2/8: Green系雑魚が波状追加
- 特徴: リタ（URキャラ）がhp_coef 38という高倍率で最序盤から登場

#### savage_00003（22イベント / OutpostHP 200,000）

- InitialSummon: `c_you_00001_you1_savage01_Boss_Red`（リタ / coef 8）、`c_you_00301_you1_savage01_Boss_Green`（ハナ / coef 5）
- FriendUnitDead1: `c_you_00001_you1_savage01_Boss_Red`（リタ / coef 8）再登場
- EnterTargetKomaIndex4: ボス+雑魚Fall0演出（`c_you_00201_you1_savage01_Boss_Green` coef 10登場）
- EnterTargetKomaIndex7: 大量雑魚追加
- OutpostDamage1: `c_you_00001_you1_savage01_Boss_Red`（coef 26）+ `c_you_00101_you1_savage01_Boss_Blue`（delay50）
- ElapsedTime12300〜12400: Colorless雑魚大量追加
- FriendUnitDead1/3: Green雑魚波状追加
- 特徴: リタが複数回登場、EnterTargetKomaIndexとOutpostDamageトリガーが多用される高難度設計

---

## コンテンツ種別ごとの特徴比較

### OutpostHP スケール

| 種別 | 範囲 | 特徴 |
|------|------|------|
| event_you1_1day | 500 | 日替わり（超低難度） |
| event_you1_charaget01 | 30,000 〜 60,000 | +5,000/ステージが基本 |
| event_you1_charaget02 | 7,000 〜 70,000 | 不規則（初期低め→後半急増） |
| event_you1_challenge | 30,000 〜 80,000 | +10,000〜20,000/ステージ |
| event_you1_savage | 100,000 〜 200,000 | +50,000/ステージ |
| raid_you1 | 1,000,000 | レイド（超高HP） |

### 雑魚エネミーのHP（base値）

| エネミー | コンテンツ系統 | base HP | move_speed |
|---------|---------------|---------|------------|
| enemy_you_00001（不良系金髪イケメン） | charaget / advent | 1,000 | 37 |
| enemy_you_00101（イケメンじゃない殺し屋） | charaget / advent | 1,000 | 30 |
| enemy_you_00001（savage版） | savage | 10,000 | 37 |
| enemy_you_00101（savage版） | savage | 10,000 | 30 |
| enemy_glo_00001（GLO汎用） | challenge | 1,000 | 30 |

> 難易度調整はシーケンス内の enemy_hp_coef によって行われる（charaget01: 1.5〜10倍、challenge: 11〜55倍）

### ボスエネミーのbase HP比較

| キャラ | charaget版 HP | savage版 HP | attack_power |
|-------|--------------|-------------|--------------|
| chara_you_00001（リタ） | 10,000（Colorless/Green) | 50,000（Red） | 100〜500 |
| chara_you_00101（ルーク） | 10,000 | 50,000 | 100〜500 |
| chara_you_00201（ダグ） | 10,000 | 50,000 | 100〜500 |
| chara_you_00301（ハナ） | 10,000 | 50,000 | 100〜500 |

> savage版はbase HPが10,000から50,000に増加（5倍）

### シーケンスパターンの違い

| 種別 | シーケンス複雑度 | 主なトリガー | 特記 |
|------|----------------|-------------|------|
| charaget01 | 3〜7イベント | InitialSummon / OutpostHPPercentage / ElapsedTime | URキャラが中盤以降に登場 |
| charaget02 | 1〜16イベント | ElapsedTime / FriendUnitDead / OutpostHPPercentage | 量産型から始まりボス複数登場へ |
| challenge | 4〜12イベント | InitialSummon / FriendUnitDead / OutpostDamage / GroupChange | GLO汎用敵使用、GroupChange多段 |
| savage | 16〜22イベント | InitialSummon / FriendUnitDead / EnterTargetKomaIndex / OutpostDamage | 全4キャラが登場、高難度設計 |

---

## dungeon（限界チャレンジ）向けの推奨パラメータ

### 現状

dungeon_you_* のMstInGameエントリは現時点で存在しない。これから生成する。

### 雑魚敵の選択

CLAUDE.mdの仕様に基づき：

- **normalブロック用（雑魚）**: `enemy_glo_00001` を使用（you専用雑魚敵はcharaget/advent用のみで独立したdungeon向けパラメータが存在しないため）
  - 既存のchallengeコンテンツで `e_glo_00001_you1_challenge_Normal_Green` / `Blue` / `Red` が使われている
  - dungeonのアウトポストHP = 100 固定なので、シーケンスのenemy_hp_coefで調整する設計

- **bossブロック用**: `chara_you_00001`（元殺し屋の新人教諭 リタ / URキャラ）がボスとして最適
  - `c_you_00001_you1_charaget01_Boss_Colorless`（HP 10,000, Attack, Colorless, speed 45, combo x6）
  - または `c_you_00001_you1_charaget02_Boss_Red`（HP 10,000, Attack, Red, speed 45, combo x9）
  - savage版 `c_you_00001_you1_savage01_Boss_Red`（HP 50,000, speed 45, combo x4）

### dungeonブロック仕様

| ブロック種別 | OutpostHP（MstEnemyOutpost） | コマ行数 | ボスの有無 |
|------------|---------------------------|---------|----------|
| normal | 100 | 3行 | なし |
| boss | 1,000 | 1行 | あり |

### 参考パラメータ値（既存コンテンツより）

| 参考コンテンツ | 雑魚HP（coef=1） | 攻撃力 | move_speed |
|---------------|-----------------|--------|------------|
| charaget01（OutpostHP 30,000〜60,000） | 1,000 | 100 | 30〜37 |
| challenge（OutpostHP 30,000〜80,000） | 1,000 | 100（coef 22〜55倍） | 30 |
| savage01（OutpostHP 100,000） | 10,000 | 100 | 30〜37 |

### BGM推奨

- normalブロック: `SSE_SBG_003_001`（charaget01系と同じ）
- bossブロック: `SSE_SBG_003_001` + boss_bgm `SSE_SBG_003_004`（challenge系と同じ）

### 背景アセット（loop_background_asset_key）

既存youコンテンツでは全て空。dungeon向けには作品固有の背景アセット `you_000XX` または汎用背景 `glo_000XX` を使用する必要がある。他作品の実績を参考に選定が必要。

---

## まとめ・パターン特徴

1. **BGMは種別で使い分け**: charaget系は `SSE_SBG_003_001`、challenge/charaget01はboss_bgm `SSE_SBG_003_004`、savage/pvpは `SSE_SBG_003_007`、raidは `SSE_SBG_003_008`
2. **雑魚敵は2種類のみ**: `enemy_you_00001`（不良系金髪イケメン / Defense寄り）と `enemy_you_00101`（イケメンじゃない殺し屋 / Attack）の2種。challengeではGLO汎用敵も使用
3. **全4キャラ（リタ・ルーク・ダグ・ハナ）がエネミーとして登場**: URキャラのリタは後半または高難度コンテンツでボスとして登場
4. **URキャラ（リタ）は多様な状況で登場**:
   - charaget01_00004: Boss_Colorless（coef 4〜7）
   - charaget02_00005〜00006: Boss_Red（coef 8〜17）
   - challenge_00004: Boss_Green（coef 44.5）
   - savage_00002: Boss_Red（coef 38）、savage_00003: Boss_Red（coef 8〜26）
5. **challenge系でGLO汎用敵を使用**: `e_glo_00001_you1_challenge_Normal_Green/Blue/Red` が登場。dungeonでもGLO汎用敵が使いやすい構成
6. **savage系はEnterTargetKomaIndexトリガーを多用**: Fall0アニメーションでの登場が特徴的
7. **coef倍率の幅が広い**: charaget（1.5〜10倍）からchallenge（11〜55倍）まで幅広い強化が行われているが、base HP は低く（1,000〜10,000）設定されている
8. **dungeon用データは未生成**: これから `/masterdata-ingame-creator` スキルで `dungeon_you_normal_00001` と `dungeon_you_boss_00001` を生成する予定
