# チェンソーマン インゲームパターン分析

## 概要

- series_id: chi
- URキャラ: chara_chi_00002 (Yellow / Technical)
- インゲームコンテンツ数: 18件（normal x6, hard x6, veryhard x6）
- dungeonコンテンツ: 現時点では存在しない（未生成）
- 背景BGM: SSE_SBG_003_001（全コンテンツ共通）

---

## コンテンツ種別一覧

### normal（通常）

| ingame_id | loop_background_asset_key | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef |
|-----------|---------------------------|------------|----------------------|--------------------------|
| normal_chi_00001 | glo_00016 | 50,000 | 1.0 | 1.0 |
| normal_chi_00002 | glo_00016 | 52,000 | 1.0 | 1.0 |
| normal_chi_00003 | glo_00008 | 54,000 | 1.0 | 1.0 |
| normal_chi_00004 | dan_00006 | 56,000 | 1.0 | 1.0 |
| normal_chi_00005 | glo_00010 | 58,000 | 1.0 | 1.0 |
| normal_chi_00006 | glo_00016 | 60,000 | 1.0 | 1.0 |

### hard（難）

| ingame_id | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef | boss_enemy_hp_coef | boss_enemy_attack_coef |
|-----------|------------|----------------------|--------------------------|--------------------|-----------------------|
| hard_chi_00001 | 100,000 | 2.0 | 2.0 | 1.0 | 1.0 |
| hard_chi_00002 | 105,000 | 2.0 | 2.0 | 2.5 | 2.7 |
| hard_chi_00003 | 110,000 | 2.0 | 2.0 | 1.8 | 2.3 |
| hard_chi_00004 | 115,000 | 2.0 | 3.0 | 5.5 | 5.5 |
| hard_chi_00005 | 120,000 | 2.0 | 2.0 | 2.5 | 4.0 |
| hard_chi_00006 | 125,000 | 2.0 | 2.0 | 3.0 | 2.0 |

### veryhard（超難）

| ingame_id | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef |
|-----------|------------|----------------------|--------------------------|
| veryhard_chi_00001 | 200,000 | 1.0 | 1.0 |
| veryhard_chi_00002 | 205,000 | 1.0 | 1.0 |
| veryhard_chi_00003 | 210,000 | 1.0 | 1.0 |
| veryhard_chi_00004 | 215,000 | 1.0 | 1.0 |
| veryhard_chi_00005 | 220,000 | 1.0 | 1.0 |
| veryhard_chi_00006 | 225,000 | 1.0 | 1.0 |

> veryharコードのcoef値が1.0なのは、シーケンスで参照するパラメータID自体が高HPに設定されているため（後述）

---

## エネミー別パラメータ

### generalエネミー（normal/hard共通で使用）

**エネミーID → 日本語名対応**:
- `enemy_chi_00001` → ゾンビの悪魔
- `enemy_chi_00101` → ゾンビ
- `enemy_chi_00201` → コウモリの悪魔

| id | mst_enemy_character_id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|------------------------|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| e_chi_00001_general_Boss_Yellow | enemy_chi_00001（ゾンビの悪魔） | Boss | Attack | Yellow | 300,000 | 600 | 40 | 1 |
| e_chi_00101_general_Normal_Colorless | enemy_chi_00101（ゾンビ） | Normal | Defense | Colorless | 5,000 | 320 | 35 | 1 |
| e_chi_00101_general_Normal_Yellow | enemy_chi_00101（ゾンビ） | Normal | Technical | Yellow | 13,000 | 720 | 35 | 1 |
| e_chi_00201_general_Normal_Yellow | enemy_chi_00201（コウモリの悪魔） | Normal | Attack | Yellow | 200,000 | 320 | 50 | 1 |
| e_chi_00201_general_Boss_Yellow | enemy_chi_00201（コウモリの悪魔） | Boss | Attack | Yellow | 500,000 | 320 | 50 | 7 |

### キャラエネミー（normal/hard で使用）

**キャラID → 日本語名対応**:
- `chara_chi_00001` → デンジ
- `chara_chi_00002` → 悪魔が恐れる悪魔 チェンソーマン（URキャラ）
- `chara_chi_00201` → 早川 アキ
- `chara_chi_00301` → パワー

| id | mst_enemy_character_id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|------------------------|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| c_chi_00201_general_Normal_Colorless | chara_chi_00201（早川 アキ） | Normal | Attack | Yellow | 100,000 | 240 | 45 | 7 |
| c_chi_00201_general_Normal_Yellow | chara_chi_00201（早川 アキ） | Normal | Attack | Yellow | 100,000 | 240 | 45 | 7 |
| c_chi_00201_general_Boss_Yellow | chara_chi_00201（早川 アキ） | Boss | Attack | Yellow | 500,000 | 400 | 45 | 7 |
| c_chi_00301_general_Normal_Yellow | chara_chi_00301（パワー） | Normal | Attack | Yellow | 250,000 | 400 | 45 | 7 |
| c_chi_00002_general_Boss_Yellow | chara_chi_00002（悪魔が恐れる悪魔 チェンソーマン） | Boss | Technical | Yellow | 400,000 | 450 | 50 | 5 |

### veryhardエネミー（chi_vh系）

| id | mst_enemy_character_id | character_unit_kind | role_type | color | hp | attack_power | move_speed | 備考 |
|----|------------------------|---------------------|-----------|-------|-----|--------------|------------|------|
| e_chi_00001_general_chi_vh_Normal_Red | enemy_chi_00001 | Normal | Technical | Red | 250,000 | 700 | 35 | zombie能力付き |
| e_chi_00001_general_chi_vh2_Boss_Yellow | enemy_chi_00001 | Boss | Technical | Yellow | 700,000 | 600 | 35 | gust_3_zombie |
| e_chi_00001_general_chi_vh_Normal_Yellow | enemy_chi_00001 | Normal | Technical | Yellow | 150,000 | 400 | 35 | gust_3_zombie |
| e_chi_00001_general_chi_vh_Boss_Yellow | enemy_chi_00001 | Boss | Technical | Yellow | 700,000 | 700 | 35 | gust_3_zombie |
| e_chi_00101_general_chi_vh_Normal_Green | enemy_chi_00101 | Normal | Attack | Green | 16,000 | 650 | 45 | gust_1_zombie |
| e_chi_00101_general_chi_vh_Normal_Blue | enemy_chi_00101 | Normal | Attack | Blue | 18,000 | 550 | 45 | gust_1_zombie |
| e_chi_00101_general_chi_vh_Normal_Red | enemy_chi_00101 | Normal | Attack | Red | 14,000 | 500 | 45 | gust_1_zombie |
| e_chi_00101_general_chi_vh_big_Normal_Yellow | enemy_chi_00101 | Normal | Attack | Yellow | 100,000 | 1,500 | 45 | gust_3_zombie（大型） |
| e_chi_00101_general_chi_vh_big_Normal_Blue | enemy_chi_00101 | Normal | Attack | Blue | 70,000 | 1,000 | 45 | gust_3_zombie（大型） |
| e_chi_00101_general_chi_vh_big_Normal_Red | enemy_chi_00101 | Normal | Attack | Red | 125,000 | 1,300 | 45 | gust_3_zombie（大型） |
| e_chi_00201_general_chi_vh_Normal_Blue | enemy_chi_00201 | Normal | Attack | Blue | 300,000 | 1,500 | 55 | - |
| e_chi_00201_general_chi_vh_Boss_Blue | enemy_chi_00201 | Boss | Attack | Blue | 800,000 | 2,000 | 55 | - |
| e_chi_00201_general_chi_vh_Normal_Yellow | enemy_chi_00201 | Normal | Attack | Yellow | 280,000 | 1,000 | 55 | - |
| e_chi_00201_general_chi_vh2_Normal_Yellow | enemy_chi_00201 | Normal | Attack | Yellow | 600,000 | 500 | 55 | vh2（より強力） |
| e_chi_00201_general_chi_vh_Boss_Yellow | enemy_chi_00201 | Boss | Attack | Yellow | 470,000 | 1,200 | 55 | - |

### veryhardキャラエネミー（chi_vh系）

| id | mst_enemy_character_id | character_unit_kind | hp | attack_power | 変身先 |
|----|------------------------|---------------------|----|--------------|--------|
| c_chi_00201_general_chi_vh_Normal_Green | chara_chi_00201 | Normal | 700,000 | 500 | - |
| c_chi_00201_general_chi_vh_Boss_Red | chara_chi_00201 | Boss | 700,000 | 300 | - |
| c_chi_00201_general_chi_vh_Normal_Yellow | chara_chi_00201 | Normal | 700,000 | 500 | - |
| c_chi_00301_general_chi_vh_Normal_Green | chara_chi_00301 | Normal | 420,000 | 200 | - |
| c_chi_00002_general_chi_vh_Boss_Blue | chara_chi_00002 | Boss | 1,200,000 | 800 | - |
| c_chi_00001_general_chi_vh2_Normal_Blue | chara_chi_00001 | Normal | 385,000 | 200 | - |
| c_chi_00001_general_chi_vh_Boss_Blue | chara_chi_00001 | Boss | 500,000 | 500 | c_chi_00002_general_chi_vh_Boss_Blue（HP30%で変身） |

### GLO汎用エネミー（veryhardで使用）

| id | mst_enemy_character_id | color | hp | attack_power | move_speed |
|----|------------------------|-------|-----|--------------|------------|
| e_glo_00001_general_chi_vh_Normal_Blue | enemy_glo_00001 | Blue | 70,000 | 600 | 65 |
| e_glo_00001_general_chi_vh_Normal_Red | enemy_glo_00001 | Red | 15,000 | 550 | 65 |
| e_glo_00001_general_chi_vh_Normal_Yellow | enemy_glo_00001 | Yellow | 16,000 | 400 | 65 |
| e_glo_00001_general_chi_vh_Normal_Green | enemy_glo_00001 | Green | 110,000 | 750 | 65 |
| e_glo_00001_general_chi_vh_Normal_Colorless | enemy_glo_00001 | Colorless | 5,000 | 1,000 | 65 |
| e_glo_00001_general_chi_vh_big_Normal_Red | enemy_glo_00001 | Red | 150,000 | 800 | 55 |
| e_glo_00001_general_chi_vh_big_Normal_Yellow | enemy_glo_00001 | Yellow | 220,000 | 800 | 55 |

---

## シーケンスパターン（MstAutoPlayerSequence）

### normal_chi_00001（最初のステージ / e_chi_00101中心）

- 使用エネミー: `e_chi_00101_general_Normal_Colorless`（初期）→ `e_chi_00101_general_Normal_Yellow`（後半）
- 総イベント数: 20
- 進行パターン:
  1. ElapsedTime250: Colorless x2（間隔350）
  2. ElapsedTime700: Colorless x2（間隔350）
  3. ElapsedTime1200: Colorless x1
  4. FriendUnitDead3: Colorless x10（間隔500）
  5. ElapsedTime1500: Yellow x1
  6. ElapsedTime1700: Yellow x2（間隔500）
  7. FriendUnitDead5: Yellow x2（間隔500）
  8. ElapsedTime2300: Yellow x1
  9. FriendUnitDead8: Yellow x3（Fall演出、summon_position 1.9）
  10. FriendUnitDead8: Yellow x3（Fall演出、summon_position 1.8）
  11. FriendUnitDead8: Colorless x20（間隔500）
  12. ElapsedTime3000: Yellow x1
  13-15. FriendUnitDead12: Yellow x3（間隔750、各positionずらし）
  16-18. ElapsedTime4500/4600/4700: Yellow x3（間隔750）
  19-20. OutpostDamage1: Yellow x99（間隔250、Fall演出、OutpostへのリーチでLoop）

### normal_chi_00002（e_chi_00101中心 + e_chi_00001_Boss登場）

- 使用エネミー: `e_chi_00101_general_Normal_Colorless`（初期）→ `e_chi_00101_general_Normal_Yellow` + `e_chi_00001_general_Boss_Yellow`（後半）
- 総イベント数: 11
- 特徴: ElapsedTime2500でBoss（e_chi_00001）が1体登場

### normal_chi_00003（c_chi_00201中心）

- 使用エネミー: `c_chi_00201_general_Normal_Colorless`（先頭）→ `c_chi_00201_general_Boss_Yellow`
- 総イベント数: 2
- シンプルな2ステップ進行（前者死亡後に後者出現）

### normal_chi_00004（e_chi_00201中心）

- 使用エネミー: `e_chi_00201_general_Normal_Yellow`
- 総イベント数: 1
- ElapsedTime750で登場

### normal_chi_00005（e_chi_00201_Boss中心）

- 使用エネミー: `e_chi_00201_general_Boss_Yellow`
- 総イベント数: 1
- ElapsedTime250で登場

### normal_chi_00006（c_chi三体連続 + 量産型）

- 使用エネミー: `c_chi_00201_general_Normal_Yellow` → `c_chi_00301_general_Normal_Yellow` → `c_chi_00002_general_Boss_Yellow` + `e_chi_00101_general_Normal_Colorless`（補助）
- 総イベント数: 5
- 最終ボスはURキャラ（chara_chi_00002）の強化版

---

### hard_chi（シーケンス独自設定あるのは00003と00005のみ、他はnormalから流用）

| ingame_id | mst_auto_player_sequence_id（参照先） |
|-----------|--------------------------------------|
| hard_chi_00001 | normal_chi_00001 |
| hard_chi_00002 | normal_chi_00002 |
| hard_chi_00003 | hard_chi_00003（独自） |
| hard_chi_00004 | normal_chi_00004 |
| hard_chi_00005 | hard_chi_00005（独自） |
| hard_chi_00006 | normal_chi_00006 |

#### hard_chi_00003の独自シーケンス

- `c_chi_00201_general_Normal_Colorless` → 死亡後 `c_chi_00201_general_Boss_Yellow`（action_delay 75）

#### hard_chi_00005の独自シーケンス

- `e_chi_00201_general_Boss_Yellow`（ElapsedTime500で単体出現）

---

### veryhard_chi（全6ステージ、chi_vh系パラメータを使用）

#### veryhard_chi_00001 (20イベント)

- メインエネミー: `e_chi_00101_general_chi_vh_Normal_Green`（量産型）
- サブエネミー: `e_glo_00001_general_chi_vh_Normal_Blue`（GLO汎用）
- 特殊: `e_chi_00101_general_chi_vh_big_Normal_Yellow`（Boss aura）がFriendUnitDead4/10/13で登場
- 最終ボス: `e_chi_00001_general_chi_vh_Boss_Yellow`（FriendUnitDead12、delay450）
- OutpostHP50%トリガー: big_Normal_Yellow登場

#### veryhard_chi_00002 (21イベント)

- メインエネミー: `e_glo_00001_general_chi_vh_Normal_Red`（量産型）、`e_glo_00001_general_chi_vh_Normal_Green`（中型）
- 特殊: `e_glo_00001_general_chi_vh_big_Normal_Yellow`（Boss aura）がFriendUnitDead10/12で登場
- 中間ボス: `e_chi_00201_general_chi_vh_Normal_Yellow`（FriendUnitDead3）
- 最終ボス: `e_chi_00201_general_chi_vh_Boss_Yellow`（FriendUnitDead14）
- OutpostHP70/50%トリガー: glo_green/big_yellow登場
- 定期出現: `e_glo_00001_general_chi_vh_Normal_Colorless`（ElapsedTime2500）

#### veryhard_chi_00003 (17イベント)

- メインエネミー: `e_chi_00101_general_chi_vh_Normal_Green`
- サブエネミー: `e_glo_00001_general_chi_vh_big_Normal_Red`（大型）
- 中間キャラ: `e_chi_00201_general_chi_vh_Normal_Yellow`（FriendUnitDead3）、`e_chi_00001_general_chi_vh_Normal_Yellow`（FriendUnitDead3）
- 最終ボス: `e_chi_00001_general_chi_vh_Boss_Yellow`（FriendUnitDead11）
- 特殊: vh2版登場 `e_chi_00201_general_chi_vh2_Normal_Yellow`（FriendUnitDead11）

#### veryhard_chi_00004 (18イベント)

- メインエネミー: `e_chi_00101_general_chi_vh_Normal_Green`
- サブエネミー: `e_chi_00101_general_chi_vh_Normal_Blue`、`e_chi_00101_general_chi_vh_big_Normal_Red`（大型Boss aura）
- 最終ボス群: `c_chi_00001_general_chi_vh2_Normal_Blue`、`c_chi_00301_general_chi_vh_Normal_Green`、`c_chi_00201_general_chi_vh_Boss_Red`（FriendUnitDead10に三体同時）

#### veryhard_chi_00005 (19イベント)

- メインエネミー: `e_chi_00101_general_chi_vh_Normal_Red`
- サブエネミー: `e_chi_00101_general_chi_vh_big_Normal_Blue`（Boss aura大型）
- 中間ボス: `e_chi_00201_general_chi_vh_Normal_Blue`（FriendUnitDead8）
- 最終ボス: `e_chi_00201_general_chi_vh_Boss_Blue`（FriendUnitDead15）
- OutpostHP50%: `e_chi_00101_general_chi_vh_Normal_Red` x2

#### veryhard_chi_00006 (18イベント)

- メインエネミー: `e_chi_00101_general_chi_vh_Normal_Green`（量産型）
- サブエネミー: `e_chi_00101_general_chi_vh_Normal_Blue`、`e_chi_00001_general_chi_vh_Normal_Red`
- 最終ボス: `c_chi_00001_general_chi_vh_Boss_Blue`（FriendUnitDead10、HP30%で`c_chi_00002_general_chi_vh_Boss_Blue`に変身）
- OutpostDamage/OutpostHP30%トリガーあり

---

## コンテンツ種別ごとの特徴比較

### OutpostHP（アウトポストHP）スケール

| 種別 | 範囲 | 増加幅 |
|------|------|--------|
| normal | 50,000 ～ 60,000 | +2,000/ステージ |
| hard | 100,000 ～ 125,000 | +5,000/ステージ |
| veryhard | 200,000 ～ 225,000 | +5,000/ステージ |

### 雑魚エネミーHP（normalコンテンツのbase値）

| エネミーID | HP（Colorless） | HP（Yellow） | move_speed |
|-----------|----------------|-------------|------------|
| enemy_chi_00101（normal用） | 5,000 | 13,000 | 35 |
| enemy_chi_00101（veryhard用/Green） | 16,000 | - | 45 |
| enemy_chi_00101（veryhard用/big Yellow） | 100,000 | - | 45 |

### ボスエネミーHP比較

| エネミー | normalでの使用 | veryhardでの使用 | HP（normal） | HP（veryhard） |
|---------|---------------|----------------|--------------|----------------|
| enemy_chi_00001 Boss | ○（normal_00002） | ○ | 300,000 | 700,000 |
| enemy_chi_00201 Boss | ○（normal_00005） | ○ | 500,000 | 470,000〜800,000 |
| chara_chi_00002 Boss | ○（normal_00006） | ○ | 400,000 | 1,200,000 |

### シーケンスパターンの違い

| 種別 | シーケンス複雑度 | 特記事項 |
|------|----------------|---------|
| normal | 1〜20イベント | 単純→複合へ進化、後半はOutpostDamageトリガー |
| hard | normalを流用（00003/00005は独自） | normalより難易度係数（coef）で強化 |
| veryhard | 17〜21イベント（全独自） | chi_vh専用パラメータ、GLO汎用敵も混在、変身ボスあり |

### 使用エネミー比較

| エネミーカテゴリ | normal | hard | veryhard |
|----------------|--------|------|----------|
| enemy_chi_00101 | 使用（Normal_Colorless, Normal_Yellow） | 流用 | 使用（chi_vh系, big版あり） |
| enemy_chi_00001 | 使用（Boss） | 流用 | 使用（chi_vh_Boss） |
| enemy_chi_00201 | 使用（Normal, Boss） | 流用 | 使用（chi_vh_Boss） |
| chara_chi_00x | 使用（00201, 00301, 00002） | 流用 | 使用（chi_vh版） |
| enemy_glo_00001 | 未使用 | 未使用 | 使用（chi_vh系） |

---

## dungeon（限界チャレンジ）向けの考察

### 現状

dungeon_chi_* のMstInGameエントリは現時点で存在しない。これから生成する。

### 雑魚敵の選択

CLAUDE.mdの仕様に基づき：

- **normalブロック用の雑魚敵**: `enemy_chi_00101`がメイン雑魚として最適
  - `enemy_chi_00101_general_Normal_Colorless`（HP 5,000, Def, speed 35）
  - `enemy_chi_00101_general_Normal_Yellow`（HP 13,000, Tech, speed 35）
  - dungeonは100HP固定なので、コンテンツIDには既存パラメータを参照する設計が必要
- **bossブロック用**: `chara_chi_00002`（悪魔が恐れる悪魔 チェンソーマン / URキャラ）がボスとして最適
  - `c_chi_00002_general_Boss_Yellow`（HP 400,000, Tech, speed 50, combo x5）

### パラメータ参考値（既存コンテンツより）

| 参考コンテンツ | 雑魚HP（coef=1） | 攻撃力 |
|---------------|-----------------|--------|
| normal（outpost HP: 50,000〜60,000） | 5,000〜13,000 | 320〜720 |
| hard（outpost HP: 100,000〜125,000） | 5,000〜13,000（x2補正） | 320〜720（x2〜3補正） |

### 背景アセット

normalコンテンツで使用されている背景アセット（参考）：
- `glo_00016`（normal_chi_00001/00002/00006）
- `glo_00008`（normal_chi_00003）
- `dan_00006`（normal_chi_00004）
- `glo_00010`（normal_chi_00005）

dungeonブロック用は chi専用背景アセット `chi_000XX` 系を優先確認すること。

---

## まとめ・パターン特徴

1. **BGM統一**: 全18コンテンツで `SSE_SBG_003_001` を使用
2. **雑魚敵は enemy_chi_00101 が中心**: Normal/Hard では Colorless（弱）→ Yellow（強）の2段階で使い分け
3. **ボスは作品を代表するキャラが順に登場**: chi_00001（デンジ）→ chi_00201（早川 アキ）→ chi_00002（悪魔が恐れる悪魔 チェンソーマン）の順にノーマルで登場
4. **Veryhardは専用パラメータセットを使用**: `chi_vh` サフィックスの付いた高HPバリアントを使用、GLO汎用敵も導入
5. **Hardはnormalのシーケンス流用が多い**: `mst_auto_player_sequence_id`でnormalと同じシーケンスを参照し、coef値で難易度を上げる設計
6. **変身ボスの存在**: veryhard_chi_00006 では `c_chi_00001_general_chi_vh_Boss_Blue` がHP30%で `c_chi_00002_general_chi_vh_Boss_Blue` に変身
7. **dungeon用データは未生成**: これから `/masterdata-ingame-creator` スキルで normal_00001 と boss_00001 を生成する予定
