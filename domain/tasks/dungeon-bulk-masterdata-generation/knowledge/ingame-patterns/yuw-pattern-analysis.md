# 2.5次元の誘惑（yuw）インゲームパターン分析

## 概要

- series_id: `yuw`
- URキャラ（5体）:
  - `chara_yuw_00001` (Yellow / Attack) — リリエルに捧ぐ愛 天乃 リリサ
  - `chara_yuw_00101` (Green / Technical) — コスプレに託す乙女心 橘 美花莉
  - `chara_yuw_00102` (Green / Support) ※FestivalUR — （未確認、MstEnemyStageParameterには未登場）
  - `chara_yuw_00301` (Blue / Support) — 勇気を纏うコスプレ 乃愛
  - `chara_yuw_00401` (Red / Defense) — 伝えたいウチの想い 喜咲 アリア
- インゲームコンテンツ数: 29件（MstInGame）
- 調査日: 2026-03-01

### 重要な特徴

- **専用の雑魚敵（enemy_yuw_*）は存在しない**
- 全コンテンツでキャラ自身（chara_yuw_*）が敵として使われている
- GLO汎用敵（enemy_glo_00001）はサベージ・レイドでのみ使用
- 雑魚敵の使用傾向は「多体分散型」（6キャラを均等に使用）

---

## コンテンツ種別一覧とアウトポストHP

### event（イベントブロック: event_yuw1）

| ingame_id | bgm_asset_key | boss_bgm_asset_key | mst_enemy_outpost_id | outpost_hp |
|-----------|--------------|-------------------|----------------------|-----------|
| event_yuw1_1day_00001 | SSE_SBG_003_006 | — | event_yuw1_1day_00001 | 500 |
| event_yuw1_challenge01_00001 | SSE_SBG_003_007 | — | event_yuw1_challenge01_00001 | 30,000 |
| event_yuw1_challenge01_00002 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_challenge01_00002 | 40,000 |
| event_yuw1_challenge01_00003 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_challenge01_00003 | 60,000 |
| event_yuw1_challenge01_00004 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_challenge01_00004 | 80,000 |
| event_yuw1_charaget01_00001 | SSE_SBG_003_006 | — | event_yuw1_charaget01_00001 | 20,000 |
| event_yuw1_charaget01_00002 | SSE_SBG_003_006 | — | event_yuw1_charaget01_00002 | 20,000 |
| event_yuw1_charaget01_00003 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_charaget01_00003 | 25,000 |
| event_yuw1_charaget01_00004 | SSE_SBG_003_006 | — | event_yuw1_charaget01_00004 | 25,000 |
| event_yuw1_charaget01_00005 | SSE_SBG_003_006 | — | event_yuw1_charaget01_00005 | 30,000 |
| event_yuw1_charaget01_00006 | SSE_SBG_003_007 | — | event_yuw1_charaget01_00006 | 30,000 |
| event_yuw1_charaget01_00007 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_charaget01_00007 | 30,000 |
| event_yuw1_charaget01_00008 | SSE_SBG_003_007 | — | event_yuw1_charaget01_00008 | 40,000 |
| event_yuw1_charaget02_00001 | SSE_SBG_003_006 | — | event_yuw1_charaget02_00001 | 20,000 |
| event_yuw1_charaget02_00002 | SSE_SBG_003_007 | — | event_yuw1_charaget02_00002 | 20,000 |
| event_yuw1_charaget02_00003 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_charaget02_00003 | 25,000 |
| event_yuw1_charaget02_00004 | SSE_SBG_003_006 | — | event_yuw1_charaget02_00004 | 25,000 |
| event_yuw1_charaget02_00005 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_charaget02_00005 | 30,000 |
| event_yuw1_charaget02_00006 | SSE_SBG_003_007 | — | event_yuw1_charaget02_00006 | 30,000 |
| event_yuw1_charaget02_00007 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_charaget02_00007 | 30,000 |
| event_yuw1_charaget02_00008 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_charaget02_00008 | 40,000 |
| event_yuw1_savage_00001 | SSE_SBG_003_007 | — | event_yuw1_savage_00001 | 120,000 |
| event_yuw1_savage_00002 | SSE_SBG_003_007 | — | event_yuw1_savage_00002 | 150,000 |
| event_yuw1_savage_00003 | SSE_SBG_003_007 | — | event_yuw1_savage_00003 | 180,000 |
| event_yuw1_savage02_00001 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_savage02_00001 | 500,000 |
| event_yuw1_savage02_00002 | SSE_SBG_003_006 | SSE_SBG_003_007 | event_yuw1_savage02_00002 | 750,000 |

### pvp（PVP）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id |
|-----------|--------------|----------------------|
| pvp_yuw_01 | SSE_SBG_003_007 | pvp |
| pvp_yuw_02 | SSE_SBG_003_007 | pvp |

### raid（レイドボス）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp |
|-----------|--------------|----------------------|-----------|
| raid_yuw1_00001 | SSE_SBG_003_007 | raid_yuw1_00001 | 1,000,000 |

> **loop_background_asset_key（背景アセット）は全コンテンツで空（未設定）**。

---

## アウトポストHP スケール比較

| コンテンツ種別 | アウトポストHP | 参考 |
|--------------|-------------|------|
| event (1day) | 500 | |
| event (charaget 最低) | 20,000 | |
| event (challenge 最低) | 30,000 | |
| event (savage 最低) | 120,000 | |
| event (savage02 最低) | 500,000 | 高難易度サベージ |
| raid | 1,000,000 | |
| **dungeon_normal（生成対象）** | **100（固定）** | CLAUDE.md仕様 |
| **dungeon_boss（生成対象）** | **1,000（固定）** | CLAUDE.md仕様 |

---

## エネミーID → 日本語名対応表

### キャラ系エネミー（chara_yuw_*）

| asset_key | 日本語名 | 役割 |
|-----------|---------|------|
| chara_yuw_00001 | リリエルに捧ぐ愛 天乃 リリサ | UR Yellow/Attack |
| chara_yuw_00101 | コスプレに託す乙女心 橘 美花莉 | UR Green/Technical |
| chara_yuw_00102 | （FestivalUR、未確認） | UR Green/Support |
| chara_yuw_00201 | 羽生 まゆり | SR相当 |
| chara_yuw_00301 | 勇気を纏うコスプレ 乃愛 | UR Blue/Support |
| chara_yuw_00401 | 伝えたいウチの想い 喜咲 アリア | UR Red/Defense |
| chara_yuw_00501 | 753♡ | （SR相当） |
| chara_yuw_00601 | 奥村 正宗 | （SR相当） |

### GLO汎用敵（サベージ・レイドのみ使用）

| asset_key | 用途 |
|-----------|------|
| enemy_glo_00001 | サベージ・レイドの雑魚補助 |

---

## エネミー別パラメータ詳細（MstEnemyStageParameter）

### chara_yuw_00001（リリエルに捧ぐ愛 天乃 リリサ）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| c_yuw_00001_1d1c_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 30 | 0.24 | 300 | 4 | 1,000 |
| c_yuw_00001_753get_Normal_Green | Normal | Attack | Green | 50,000 | 34 | 0.24 | 500 | 5 | 100 |
| c_yuw_00001_753get_Boss_Green | Boss | Attack | Green | 50,000 | 34 | 0.24 | 500 | 5 | 100 |
| c_yuw_00001_challenge_Normal_Red | Normal | Attack | Red | 50,000 | 34 | 0.24 | 500 | 5 | 100 |
| c_yuw_00001_challenge_Normal_Green | Normal | Attack | Green | 50,000 | 34 | 0.24 | 500 | 5 | 100 |
| c_yuw_00001_challenge_Boss_Green | Boss | Technical | Green | 50,000 | 34 | 0.24 | 500 | 3 | 100 |
| c_yuw_00001_okumuraget_Normal_Blue | Normal | Attack | Blue | 50,000 | 34 | 0.24 | 500 | 5 | 100 |
| c_yuw_00001_okumuraget_Boss_Blue | Boss | Attack | Blue | 50,000 | 34 | 0.24 | 500 | 5 | 100 |
| c_yuw_00001raid_00001_Normal_Green | Normal | Attack | Green | 10,000 | 34 | 0.24 | 100 | 5 | 100 |
| c_yuw_00001raid_00001_Boss_Green | Boss | Attack | Green | 10,000 | 34 | 0.24 | 100 | 5 | 100 |

### chara_yuw_00101（コスプレに託す乙女心 橘 美花莉）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| c_yuw_00101_753get_Normal_Green | Normal | Technical | Green | 50,000 | 29 | 0.25 | 300 | 5 | 100 |
| c_yuw_00101_challenge_Normal_Red | Normal | Attack | Red | 50,000 | 29 | 0.25 | 300 | 5 | 100 |
| c_yuw_00101_challenge_Normal_Green | Normal | Attack | Green | 50,000 | 29 | 0.25 | 300 | 5 | 100 |
| c_yuw_00101_challengekb_Boss_Red | Boss | Technical | Red | 50,000 | 29 | 0.25 | 300 | 3 | 100 |
| c_yuw_00101_okumuraget_Boss_Blue | Boss | Technical | Blue | 50,000 | 29 | 0.25 | 300 | 5 | 100 |
| c_yuw_00101raid_00001_Normal_Green | Normal | Technical | Green | 10,000 | 29 | 0.25 | 100 | 3 | 100 |
| c_yuw_00101raid_00001_Boss_Green | Boss | Technical | Green | 10,000 | 29 | 0.25 | 100 | 3 | 100 |

### chara_yuw_00301（勇気を纏うコスプレ 乃愛）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| c_yuw_00301_okumuraget_Normal_Colorless | Normal | Technical | Colorless | 50,000 | 29 | 0.23 | 300 | 4 | 500 |
| c_yuw_00301_challenge_Normal_Green | Normal | Technical | Green | 50,000 | 29 | 0.26 | 300 | 4 | 500 |
| c_yuw_00301_challengeburn_Boss_Red | Boss | Technical | Red | 50,000 | 29 | 0.26 | 300 | 4 | 500 |
| c_yuw_00301_okumuraget_Boss_Blue | Boss | Technical | Blue | 50,000 | 29 | 0.23 | 300 | 4 | 500 |
| c_yuw_00301raid_00001_Boss_Red | Boss | Technical | Red | 10,000 | 29 | 0.26 | 100 | 4 | 100 |

### chara_yuw_00401（伝えたいウチの想い 喜咲 アリア）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| c_yuw_00401_okumuraget_Normal_Blue | Normal | Defense | Blue | 50,000 | 30 | 0.17 | 300 | 6 | 100 |
| c_yuw_00401_challenge_Normal_Green | Normal | Defense | Green | 50,000 | 30 | 0.17 | 300 | 6 | 100 |
| c_yuw_00401_challenge_Boss_Green | Boss | Defense | Green | 50,000 | 30 | 0.17 | 300 | 6 | 100 |
| c_yuw_00401_okumuraget_Boss_Blue | Boss | Defense | Blue | 50,000 | 30 | 0.17 | 300 | 6 | 100 |
| c_yuw_00401raid_00001_Boss_Red | Boss | Defense | Red | 10,000 | 30 | 0.17 | 100 | 6 | 100 |

### キャラ移動速度まとめ（dungeon設計参考）

| キャラ | move_speed | well_distance | 特徴 |
|-------|-----------|---------------|------|
| chara_yuw_00001 | 34 | 0.24 | 最速・Attack |
| chara_yuw_00101 | 29 | 0.25 | Technical |
| chara_yuw_00301 | 29 | 0.23〜0.26 | Technical/Support |
| chara_yuw_00401 | 30 | 0.17 | Defense・接近型 |
| chara_yuw_00201 | 27 | 0.26 | やや低速 |
| chara_yuw_00501 | 26〜28 | 0.3 | 低速 |
| chara_yuw_00601 | 25〜30 | 0.19〜0.21 | Defense |

---

## シーケンスパターン

### コンテンツ別シーケンス数

| sequence_set_id | シーケンス数 |
|----------------|------------|
| event_yuw1_1day_00001 | 2 |
| event_yuw1_challenge01_00001 | 1 |
| event_yuw1_challenge01_00002 | 4（グループ切替含む） |
| event_yuw1_challenge01_00003 | 3 |
| event_yuw1_challenge01_00004 | 8 |
| event_yuw1_charaget01_00001〜00008 | 1〜2 |
| event_yuw1_charaget02_00001〜00008 | 1〜4 |
| event_yuw1_savage_00001 | 10 |
| event_yuw1_savage_00002 | 9（グループ切替含む） |
| event_yuw1_savage_00003 | 12（グループ切替含む） |
| event_yuw1_savage02_00001 | 2 |
| event_yuw1_savage02_00002 | 3 |
| raid_yuw1_00001 | 49（多段グループ切替） |

### charaget01のシーケンス概要

| ingame_id | シーケンス数 | 使用エネミー | enemy_hp_coef |
|-----------|------------|------------|--------------|
| charaget01_00001 | 1 | c_yuw_00601_753get_Boss_Colorless | 2 |
| charaget01_00002 | 1 | c_yuw_00001_753get_Normal_Green | 2 |
| charaget01_00003 | 2 | c_yuw_00101_753get_Normal_Green / c_yuw_00001_753get_Boss_Green | 1.5〜2.4 |
| charaget01_00004 | 1 | c_yuw_00201_753get_Normal_Green | 3 |
| charaget01_00005 | 2 | c_yuw_00201_753get_Normal_Green（2回） | 2〜3 |
| charaget01_00006 | 1 | c_yuw_00501_753get_Boss_Green | 3.5 |
| charaget01_00007 | 2 | c_yuw_00201_753get_Normal_Green / c_yuw_00501_753get_Boss_Green | 3〜4.5 |
| charaget01_00008 | 2 | c_yuw_00501_753get_Boss_Green（2回） | 2〜4 |

### charaget02のシーケンス概要

| ingame_id | シーケンス数 | 使用エネミー | enemy_hp_coef |
|-----------|------------|------------|--------------|
| charaget02_00001 | 1 | c_yuw_00301_okumuraget_Normal_Colorless | 2 |
| charaget02_00002 | 1 | c_yuw_00301_okumuraget_Boss_Blue | 2 |
| charaget02_00003 | 2 | c_yuw_00001_okumuraget_Normal_Blue / c_yuw_00301_okumuraget_Boss_Blue | 1.5〜2.4 |
| charaget02_00004 | 1 | c_yuw_00401_okumuraget_Normal_Blue | 3 |
| charaget02_00005 | 3 | c_yuw_00001_okumuraget_Normal_Blue（→Boss Blue） | 2〜5 |
| charaget02_00006 | 4 | c_yuw_00001/00101/00301/00401_okumuraget_Boss_Blue（連鎖） | 2〜2.5 |
| charaget02_00007 | 1 | c_yuw_00601_okumuraget_Boss_Blue | 5 |
| charaget02_00008 | 3 | c_yuw_00601_okumuraget_Normal_Blue（→Boss Blue） | 2〜4 |

### challenge01のシーケンス概要

| ingame_id | シーケンス数 | 使用エネミー | enemy_hp_coef |
|-----------|------------|------------|--------------|
| challenge01_00001 | 1 | c_yuw_00001_okumuraget_Boss_Blue | 7 |
| challenge01_00002 | 4（グループ切替） | c_yuw_00001_challenge_Normal_Red → c_yuw_00101_challengekb_Boss_Red | 4.2〜5.8 |
| challenge01_00003 | 3 | c_yuw_00001/00101/00301（FriendUnitDead連鎖） | 4.8〜9.3 |
| challenge01_00004 | 8 | c_yuw_00001/00101/00301/00401（複数体同時＋FriendUnitDead） | 3〜10 |

### サベージのシーケンス概要

サベージは `e_glo_00001` (GLO汎用敵) が主力で、yuwキャラはボスとして登場:
- `savage_00001`: `e_glo_00001_savagetank_Normal_Colorless` + `e_glo_00001_savageyuwburn_Normal_Red` → `c_yuw_00501_savage_Boss_Red`（ボス）
- `savage_00002`: `e_glo_00001_savage_Normal_Green` → `c_yuw_00201_savage_Boss_Green`（ボス）
- `savage_00003`: 同上（3段構成）

---

## コマライン（MstKomaLine）パターン

### コマアセットキー一覧（yuwシリーズで確認できるもの）

| アセットキー | 使用コンテンツ |
|------------|--------------|
| `yuw_00001` | pvp（PVP専用） |
| `yuw_00002` | charaget02（一部） |
| `yuw_00003` | challenge, raid |
| `yuw_00004` | raid（一部） |
| `glo_00008` | charaget01（glo汎用） |
| `glo_00011` | charaget01, 1day（glo汎用） |
| `glo_00024` | savage（glo汎用） |
| `glo_00033` | savage02（glo汎用） |

### コンテンツ別コマ構成例

**1day（2行）:**
```
行1: glo_00011×2
行2: glo_00011×2
```

**challenge（2〜3行）:**
```
行1: yuw_00003×2
行2: yuw_00003×2〜3
行3: yuw_00003×2（challenge01_00002のみ）
```

**charaget01（2行）:**
```
行1: glo_00008×2
行2: glo_00008×2
```

**pvp（3行）:**
```
行1: yuw_00001×2（一方にGust）
行2: yuw_00001×1（Gust）
行3: yuw_00001×2
```

**raid（4行）:**
```
行1: yuw_00003×1
行2: yuw_00003×2
行3: yuw_00004×1
行4: yuw_00003×3
```

### コマエフェクト一覧（yuwで確認できるもの）

| エフェクト | 使用コンテンツ |
|----------|--------------|
| `None` | 全コンテンツ共通 |
| `Gust` | pvp_yuw（Gust、風 |
| `AttackPowerDown` | savage_00002〜00003 |

---

## コンテンツ種別ごとの特徴比較

### BGM パターン

| 用途 | BGM |
|-----|-----|
| イベント全般（charaget/challenge） | SSE_SBG_003_006（yuw固有） |
| ボスBGM | SSE_SBG_003_007 |
| PVP / レイド / サベージ（初期） | SSE_SBG_003_007 |

> **重要**: yuwの通常BGMは `SSE_SBG_003_006`（他作品の `SSE_SBG_003_002` に相当する位置づけ）

### アウトポストHP スケール（イベント内）

| 種別 | HP範囲 |
|-----|--------|
| 1day | 500 |
| charaget | 20,000〜40,000 |
| challenge | 30,000〜80,000 |
| savage | 120,000〜180,000 |
| savage02（高難易度） | 500,000〜750,000 |
| raid | 1,000,000 |

### 使用エネミーキャラの違い

| 種別 | 主力エネミー | ボス役 |
|-----|------------|-------|
| charaget01 | c_yuw_00601, c_yuw_00001, c_yuw_00201, c_yuw_00501 | c_yuw_00001/00501_753get_Boss |
| charaget02 | c_yuw_00301, c_yuw_00001, c_yuw_00401, c_yuw_00601 | c_yuw_00301/00401_okumuraget_Boss |
| challenge | c_yuw_00001, c_yuw_00101, c_yuw_00301, c_yuw_00401 | c_yuw_00001/00101/00401_challenge_Boss |
| savage | e_glo_00001（主力）| c_yuw_00201/00501_savage_Boss |
| raid | e_glo_00001（補助）| c_yuw_00001/00101/00201/00301/00401/00601（全キャラ登場） |

---

## dungeon設計向けの推奨パラメータ

### dungeonブロック仕様（CLAUDE.mdより）

| 種別 | ingame_id例 | MstEnemyOutpost HP | コマ行数 | ボスの有無 |
|-----|------------|-------------------|---------|----------|
| normal | `dungeon_yuw_normal_00001` | 100（固定） | 3行（固定） | なし |
| boss | `dungeon_yuw_boss_00001` | 1,000（固定） | 1行（固定） | あり |

### dungeon normalブロックの推奨パラメータ

| 項目 | 推奨値 | 根拠 |
|-----|-------|------|
| BGM | `SSE_SBG_003_006` | yuwの通常イベントBGM |
| loop_background_asset_key | `yuw_00001` | pvpで使われるyuw固有背景（最も汎用的） |
| 使用コマアセット | `yuw_00001` | pvpでの使用実績あり、dungeon向け3行構成に適合 |
| 主力エネミー | `chara_yuw_00001`（天乃 リリサ） | 使用頻度1位（9回）、move_speed=34で最速 |
| サブエネミー | `chara_yuw_00101`（橘 美花莉） | 使用頻度同率3位（5回）、Technical |
| enemy_hp_coef | 1.5〜2.0 | 既存charaget序盤のenemy_hp_coef（2.0）を参考 |
| enemy_attack_coef | 1.0〜1.5 | 既存charaget序盤のenemy_attack_coef（0.8〜1.5）を参考 |

### dungeon bossブロックの推奨パラメータ

| 項目 | 推奨値 | 根拠 |
|-----|-------|------|
| BGM | `SSE_SBG_003_006` | 通常バトルBGM |
| boss_bgm_asset_key | `SSE_SBG_003_007` | ボスBGM（challenge/savage02で使用） |
| ボスエネミー | `chara_yuw_00001`（Normal_Green or Blue） | 最も多用されるキャラ |
| ボス候補（URキャラ） | chara_yuw_00101/00301/00401 | 各URキャラを順番にボスとして登場 |
| enemy_hp_coef（ノーマル） | 1.5〜2.0 | dungeon normalの雑魚に合わせる |
| enemy_hp_coef（ボス） | 5〜10 | challenge（7.0）〜charaget02_00007（5.0）を参考 |

### コマアセットの選択方針

- yuw固有コマ: `yuw_00001`（pvpで使用、dungeon向けに最も推奨）、`yuw_00003`（challenge/raidで使用）
- glo汎用コマ: dungeon normalでは作品専用を優先すること

---

## まとめ・パターン特徴

### yuwシリーズの最大の特徴: 専用雑魚敵が存在しない

1. **`enemy_yuw_*` は存在しない** — 全コンテンツでキャラ自身（`chara_yuw_*`）が敵として使われている
2. dungeon生成時も同様に `chara_yuw_*` を主力エネミーとして使用する必要がある
3. GLO汎用敵（`enemy_glo_00001`）はサベージ・レイドのみ使用 — **dungeon normalではGLO汎用敵は使用しない**

### 使用エネミーランキング（MstAutoPlayerSequence調査より）

| 順位 | MstEnemyCharacter.id | 使用回数 | 用途 |
|------|----------------------|---------|------|
| 1位 | chara_yuw_00001 | 9 | charaget01/02/challenge/raid |
| 2位 | chara_yuw_00601 | 7 | charaget01/raid |
| 3位 | chara_yuw_00201 | 5 | charaget01/savage/raid |
| 3位 | chara_yuw_00101 | 5 | charaget02/challenge/raid |
| 5位 | chara_yuw_00301 | 3 | charaget02/challenge/raid |
| 6位 | chara_yuw_00401 | 2 | charaget02/challenge/raid |

### parameter_id 命名規則

yuwのパラメータID命名: `c_{chara_id}_{コンテンツ種別}_{unit_kind}_{color}`

例:
- `c_yuw_00001_753get_Normal_Green` — charaget01用、通常、Green色
- `c_yuw_00001_challenge_Boss_Green` — challenge用、ボス、Green色
- `c_yuw_00001raid_00001_Normal_Green` — raid用、通常、Green色

dungeon向けに新規パラメータIDを作成する場合の命名案:
- `c_yuw_00001_dungeon_Normal_Yellow` — dungeon用、通常、Yellow色（キャラ固有色に合わせる）
- `c_yuw_00001_dungeon_Boss_Yellow` — dungeon用、ボス、Yellow色

### シーケンスのトリガー種別（yuwで確認）

| condition_type | 意味 | 使用場面 |
|--------------|------|---------|
| `ElapsedTime` | 経過時間 | charaget/challenge全般 |
| `InitialSummon` | 初期召喚（位置指定あり） | charaget01 |
| `EnterTargetKomaIndex` | コマ到達トリガー | charaget全般 |
| `FriendUnitDead` | 味方ユニット死亡 | challenge上位/charaget後半 |
| `FriendUnitDead` + `SwitchSequenceGroup` | グループ切替 | challenge02/savage02 |
| `ElapsedTimeSinceSequenceGroupActivated` | グループ活性後の経過時間 | グループ切替後 |

### dungeon生成時の留意点

1. **normalブロック（3行固定）**: コマアセット `yuw_00001` を使用し、pvpと同様の2〜3コマ/行構成が自然
2. **主力エネミー**: `chara_yuw_00001`（Yellow/Attack、move_speed=34）+ `chara_yuw_00101`（Green/Technical、move_speed=29）の2体体制を推奨
3. **URキャラのdungeon用パラメータ新規作成が必要**（既存パラメータは他コンテンツ向けでHP=50,000など高すぎる）
4. **BGM**: 通常は `SSE_SBG_003_006`、bossブロックのボスBGMは `SSE_SBG_003_007`
5. **GLO汎用敵不使用**: dungeonのnormal/bossブロックではGLO汎用敵を使わず、yuwキャラのみで構成する
6. **dungeon用パラメータ命名**: `c_yuw_{ID}_dungeon_{unit_kind}_{color}` 形式を推奨
