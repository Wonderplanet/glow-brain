# 怪獣８号（kai）インゲームパターン分析

## 概要

- series_id: `kai`
- URキャラ:
  - `chara_kai_00002` 隠された英雄の姿 怪獣８号 (Green / Attack)
  - `chara_kai_00102` 識別怪獣兵器6 市川 レノ (Yellow / Technical)
  - `chara_kai_00201` 第三部隊隊長 亜白 ミナ (Blue / Attack)
  - `chara_kai_00701` 識別怪獣兵器1 鳴海 弦 (Yellow / Defense) ※FestivalUR
- インゲームコンテンツ数: 42件（MstInGame）
- MstPage: normalブロックは6件

---

## コンテンツ種別一覧

### normal（通常ブロック）

| ingame_id | bgm_asset_key | loop_background_asset_key | mst_enemy_outpost_id | outpost_hp | artwork_asset_key |
|-----------|--------------|--------------------------|----------------------|-----------|------------------|
| normal_kai_00001 | SSE_SBG_003_001 | glo_00010 | normal_kai_00001 | 60,000 | （未確認） |
| normal_kai_00002 | SSE_SBG_003_001 | glo_00003 | normal_kai_00002 | 62,000 | （未確認） |
| normal_kai_00003 | SSE_SBG_003_001 | kai_00001 | normal_kai_00003 | 64,000 | （未確認） |
| normal_kai_00004 | SSE_SBG_003_001 | kai_00001 | normal_kai_00004 | 66,000 | （未確認） |
| normal_kai_00005 | SSE_SBG_003_001 | kai_00001 | normal_kai_00005 | 68,000 | （未確認） |
| normal_kai_00006 | SSE_SBG_003_001 | glo_00010 | normal_kai_00006 | 70,000 | （未確認） |

- normalブロックの共通仕様: BGM=SSE_SBG_003_001（kai専用BGM）、ボスBGMなし
- loop_background_asset_keyはブロックによって異なる（`glo_00010`, `glo_00003`, `kai_00001`）
- **SPY×FAMILYと異なり、BGMは SSE_SBG_003_001（怪獣８号専用BGM）を使用**
- **アウトポストHPはブロック進行にあわせて段階的に増加（60,000〜70,000）**

### hard（ハードブロック）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp | normal_coef | boss_coef |
|-----------|--------------|----------------------|-----------|------------|---------|
| hard_kai_00001 | SSE_SBG_003_001 | hard_kai_00001 | 120,000 | 2.5 / 2.0 | 2.5 / 2.0 |
| hard_kai_00002 | SSE_SBG_003_001 | hard_kai_00002 | 125,000 | 1.8 / 1.4 | 1.8 / 1.4 |
| hard_kai_00003 | SSE_SBG_003_001 | hard_kai_00003 | 130,000 | 1.6 / 1.4 | 1.8 / 1.4 |
| hard_kai_00004 | SSE_SBG_003_001 | hard_kai_00004 | 135,000 | 1.6 / 1.4 | 2.0 / 1.5 |
| hard_kai_00005 | SSE_SBG_003_001 | hard_kai_00005 | 140,000 | 2.0 / 1.5 | 2.6 / 2.0 |
| hard_kai_00006 | SSE_SBG_003_001 | hard_kai_00006 | 145,000 | 2.0 / 1.5 | 2.0 / 2.2 |

> コef表記は「HP係数 / 攻撃係数」。hardはnormalと同じシーケンス（normal_kai_XXXXX参照）を使い、MstInGameの係数で難易度調整。

### veryhard（超ハードブロック）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp |
|-----------|--------------|----------------------|-----------|
| veryhard_kai_00001 | SSE_SBG_003_001 | veryhard_kai_00001 | 250,000 |
| veryhard_kai_00002 | SSE_SBG_003_001 | veryhard_kai_00002 | 255,000 |
| veryhard_kai_00003 | SSE_SBG_003_001 | veryhard_kai_00003 | 260,000 |
| veryhard_kai_00004 | SSE_SBG_003_001 | veryhard_kai_00004 | 265,000 |
| veryhard_kai_00005 | SSE_SBG_003_001 | veryhard_kai_00005 | 270,000 |
| veryhard_kai_00006 | SSE_SBG_003_001 | veryhard_kai_00006 | 275,000 |

- veryhardは専用シーケンスを持ち（veryhard_kai_XXXXX）、係数はMstInGmae側がすべて1.0（シーケンス内で高いHPを直接設定）

### event（イベントブロック：event_kai1）

- `event_kai1_1day_00001` — 1日限定（HP: 500）
- `event_kai1_challenge01_00001〜00004` — チャレンジ（HP: 20,000〜80,000）
- `event_kai1_charaget01_00001〜00008` — キャラゲット01（HP: 5,000〜50,000）
- `event_kai1_charaget02_00001〜00008` — キャラゲット02（HP: 5,000〜50,000）
- `event_kai1_savage_00001〜00002` — サベージ（HP: 80,000〜100,000）

### raid（レイドボス）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp |
|-----------|--------------|----------------------|-----------|
| raid_kai_00001 | SSE_SBG_003_001 | raid_kai_00001 | 1,000,000 |

---

## アウトポストHP スケール比較

| コンテンツ種別 | アウトポストHP（代表値） | 係数比（normal最小基準） |
|--------------|----------------------|---------------------|
| normal | 60,000〜70,000 | 1.0x |
| hard | 120,000〜145,000 | 約2x |
| veryhard | 250,000〜275,000 | 約4x |
| event (1day) | 500 | 0.008x |
| event (challenge, 最低) | 20,000 | 0.33x |
| event (charaget, 最低) | 5,000 | 0.08x |
| event (savage, 最低) | 80,000 | 1.3x |
| raid | 1,000,000 | 16.7x |

---

## エネミーID → 日本語名対応表

| asset_key | 日本語名 |
|-----------|--------|
| chara_kai_00001 | 日比野 カフカ |
| chara_kai_00002 | 隠された英雄の姿 怪獣８号 |
| chara_kai_00101 | 市川 レノ |
| chara_kai_00102 | 識別怪獣兵器6 市川 レノ |
| chara_kai_00201 | 第三部隊隊長 亜白 ミナ |
| chara_kai_00301 | 四ノ宮 キコル |
| chara_kai_00401 | 保科 宗四郎 |
| chara_kai_00501 | 四ノ宮 功 |
| chara_kai_00601 | 古橋 伊春 |
| chara_kai_00701 | 識別怪獣兵器1 鳴海 弦 |
| enemy_kai_00001 | 怪獣 本獣 |
| enemy_kai_00101 | 怪獣 余獣 |
| enemy_kai_00201 | 怪獣９号 |
| enemy_kai_00301 | 蜘蛛の怪獣 |
| enemy_kai_00401 | 怪獣１０号 |

---

## エネミー別パラメータ詳細（MstEnemyStageParameter）

### 雑魚敵: enemy_kai_00001（怪獣 本獣）

generalグループ（通常難易度）:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_kai_00001_general_Normal_Yellow | Normal | Attack | Yellow | 135,000 | 40 | 0.11 | 700 | 1 | 10 |
| e_kai_00001_general_Boss_Yellow | Boss | Attack | Yellow | 440,000 | 40 | 0.11 | 450 | 1 | 10 |

veryhardグループ（kai_vh）:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|--------------------|-----------|----|---|---|---|
| e_kai_00001_general_kai_vh_Normal_Blue | Normal | Attack | Blue | 100,000 | 35 | 600 |
| e_kai_00001_general_kai_vh_Normal_Green | Normal | Attack | Green | 250,000 | 65 | 800 |
| e_kai_00001_general_kai_vh_Normal_Red | Normal | Attack | Red | 120,000 | 35 | 800 |
| e_kai_00001_general_kai_vh_Boss_Blue | Boss | Attack | Blue | 1,500,000 | 55 | 1,000 |
| e_kai_00001_general_kai_vh_Boss_Green | Boss | Attack | Green | 610,000 | 35 | 1,200 |

### 雑魚敵: enemy_kai_00101（怪獣 余獣）

generalグループ（通常難易度）:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_kai_00101_general_Normal_Colorless | Normal | Defense | Colorless | 25,000 | 45 | 0.11 | 350 | 1 | 10 |
| e_kai_00101_general_Normal_Green | Normal | Attack | Green | 67,000 | 45 | 0.11 | 800 | 1 | 10 |
| e_kai_00101_general_Normal_Yellow | Normal | Attack | Yellow | 220,000 | 45 | 0.2 | 1,000 | 1 | 10 |

veryhardグループ（kai_vh）:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|--------------------|-----------|----|---|---|---|
| e_kai_00101_general_kai_vh_Normal_Blue | Normal | Attack | Blue | 90,000 | 55 | 700 |
| e_kai_00101_general_kai_vh_Normal_Green | Normal | Attack | Green | 80,000 | 55 | 600 |
| e_kai_00101_general_kai_vh_Normal_Red | Normal | Attack | Red | 30,000 | 65 | 300 |
| e_kai_00101_general_kai_vh_Normal_Yellow | Normal | Attack | Yellow | 40,000 | 65 | 400 |

### 雑魚敵: enemy_kai_00301（蜘蛛の怪獣）

normalブロック専用（general_1）:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power |
|-------------|--------------------|-----------|----|---|---|---|---|
| e_kai_00301_general_1_Normal_Colorless | Normal | Defense | Colorless | 150,000 | 45 | 0.2 | 600 |
| e_kai_00301_general_1_Boss_Green | Boss | Attack | Green | 450,000 | 45 | 0.2 | 1,200 |

generalグループ（通常難易度）:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|--------------------|-----------|----|---|---|---|
| e_kai_00301_general_Normal_Colorless | Normal | Defense | Colorless | 18,000 | 45 | 350 |
| e_kai_00301_general_Normal_Green | Normal | Attack | Green | 40,000 | 45 | 550 |

veryhardグループ（kai_vh）:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|--------------------|-----------|----|---|---|---|
| e_kai_00301_general_kai_vh_Normal_Blue | Normal | Attack | Blue | 250,000 | 45 | 1,200 |
| e_kai_00301_general_kai_vh_Normal_Green | Normal | Defense | Green | 120,000 | 45 | 1,000 |
| e_kai_00301_general_kai_vh_Normal_Yellow | Normal | Defense | Yellow | 190,000 | 45 | 1,000 |
| e_kai_00301_general_kai_vh_Boss_Green | Boss | Defense | Green | 500,000 | 45 | 1,500 |

### GLO汎用敵: enemy_glo_00001（kai向けveryhard用）

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|--------------------|-----------|----|---|---|---|
| e_glo_00001_general_kai_vh_Normal_Red | Normal | Attack | Red | 19,000 | 65 | 400 |
| e_glo_00001_general_kai_vh_Normal_Green | Normal | Attack | Green | 25,000 | 65 | 800 |
| e_glo_00001_general_kai_vh_Normal_Blue | Normal | Attack | Blue | 17,000 | 65 | 600 |
| e_glo_00001_general_kai_vh_big_Normal_Red | Normal | Attack | Red | 180,000 | 65 | 200 |
| e_glo_00001_general_kai_vh_big_Normal_Green | Normal | Attack | Green | 200,000 | 65 | 400 |

### キャラクターエネミー（ボス役）: chara_kai_00001（日比野 カフカ）

| parameter_id | 用途 | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|-----|--------------------|-----------|----|---|---|---|
| c_kai_00001_general_Normal_Yellow | normal/hardボス | Normal | Technical | Yellow | 150,000 | 35 | 700 |
| c_kai_00001_general_kai_vh_Boss_Yellow | veryhard用ボス | Boss | Attack | Yellow | 300,000 | 45 | 800 |

### キャラクターエネミー（ボス役）: chara_kai_00002（怪獣８号）

| parameter_id | 用途 | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|-----|--------------------|-----------|----|---|---|---|
| c_kai_00002_general_Boss_Yellow | normal/hardボス | Boss | Support | Yellow | 700,000 | 45 | 1,700 |
| c_kai_00002_general_kai_vh_Boss_Red | veryhard用ボス | Boss | Attack | Red | 700,000 | 55 | 1,200 |

### キャラクターエネミー（ボス役）: chara_kai_00301（四ノ宮 キコル）

| parameter_id | 用途 | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|-----|--------------------|-----------|----|---|---|---|
| c_kai_00301_general_Normal_Yellow | normal/hardボス | Normal | Attack | Yellow | 300,000 | 55 | 800 |
| c_kai_00301_general_kai_vh_Boss_Green | veryhard用ボス | Boss | Attack | Green | 1,000,000 | 50 | 1,000 |

---

## コマライン（MstKomaLine）パターン

### normalブロックのコマ行数

| ingame_id | 行数 | コマ構成 |
|-----------|-----|---------|
| normal_kai_00001 | 2行 | 行1: glo_00010×2、行2: glo_00010×2 |
| normal_kai_00002 | 3行 | 行1: glo_00003×2、行2: glo_00003×1、行3: glo_00003×3 |
| normal_kai_00003 | 3行 | 行1: kai_00001×2、行2: kai_00001×3、行3: kai_00001×2 |
| normal_kai_00004 | 3行 | 行1: kai_00001×2、行2: kai_00001×3、行3: kai_00001×1 |
| normal_kai_00005 | 4行 | 行1: kai_00001×2、行2: kai_00001×2、行3: kai_00001×3、行4: kai_00001×1 |
| normal_kai_00006 | 3行 | 行1: glo_00010×2、行2: glo_00010×2、行3: glo_00010×4 |

> 使用コマアセットキー: `kai_00001`（作品専用）、`glo_00010`（GLO汎用）、`glo_00003`（GLO汎用）
> height（行高）は全行で0.55で統一。コマエフェクトはnormalブロックでは全て `None`。

---

## シーケンスパターン（MstAutoPlayerSequence）

### normalブロックのシーケンス

| ingame_id | シーケンス数 | 主な使用エネミー | enemy_hp_coef |
|-----------|------------|----------------|--------------|
| normal_kai_00001 | 2 | e_kai_00301（蜘蛛の怪獣、_1系） | 1.0 |
| normal_kai_00002 | 19 | e_kai_00301（蜘蛛の怪獣） | 0.9〜1.0 |
| normal_kai_00003 | 19 | e_kai_00101（怪獣 余獣） | 0.9〜1.0 |
| normal_kai_00004 | 20 | e_kai_00101（怪獣 余獣）＋e_kai_00001ボス | 1.0 |
| normal_kai_00005 | 22 | e_kai_00101（怪獣 余獣）＋e_kai_00001変身ギミック | 1.0 |
| normal_kai_00006 | 6 | e_kai_00101、c_kai_00001（カフカ）、c_kai_00301（キコル）、c_kai_00002（怪獣8号） | 1.0 |

#### normal_kai_00001 の詳細シーケンス

```
要素1: ElapsedTime=350  → SummonEnemy(e_kai_00301_general_1_Normal_Colorless, count=1, pos=1.2)
       enemy_hp_coef=1, enemy_attack_coef=1
要素2: FriendUnitDead=1 → SummonEnemy(e_kai_00301_general_1_Boss_Green, count=1)
       enemy_hp_coef=1, enemy_attack_coef=1
```

シンプルな2シーケンス構成。蜘蛛の怪獣（_1系、高HPバリアント）のNormal×1が死亡後にBossが出現する。

#### normal_kai_00002 の詳細シーケンス概要

19シーケンス構成。`e_kai_00301_general_Normal_Colorless`（HP:18,000）と `e_kai_00301_general_Normal_Green`（HP:40,000）を交互に大量召喚する時間差ウェーブ形式。

#### normal_kai_00003 の詳細シーケンス概要

19シーケンス構成。`e_kai_00101_general_Normal_Colorless`（HP:25,000）と `e_kai_00101_general_Normal_Green`（HP:67,000）を主力に使用。`FriendUnitDead`トリガーと`ElapsedTime`を組み合わせてウェーブを制御。

#### normal_kai_00004 の詳細シーケンス概要

20シーケンス構成。00003と同様に`e_kai_00101`系を主力に使用。終盤に`e_kai_00001_general_Boss_Yellow`（HP:440,000）が登場。

#### normal_kai_00005 の詳細シーケンス概要

22シーケンス構成（最多）。`e_kai_00101`系を主力としつつ、`SummonGimmickObject`（kai_honju_enemy）を使ったギミック（本獣コクーン）を配置し、`TransformGimmickObjectToEnemy`でe_kai_00001に変身させる演出ギミックが特徴的。また `e_kai_00201_general_Boss_Yellow`（怪獣9号）が終盤に登場。

#### normal_kai_00006 の詳細シーケンス概要

6シーケンス構成（最少）。`InitialSummon`で`c_kai_00001_general_Normal_Yellow`（日比野カフカ、HP:150,000）を召喚後、`FriendUnitDead`チェーンで`c_kai_00301_general_Normal_Yellow`（四ノ宮キコル）→`c_kai_00002_general_Boss_Yellow`（怪獣８号、HP:700,000）と連続ボスが登場。

### hardブロックのシーケンス

hardはnormalと同じシーケンスID（normal_kai_XXXXX）を参照し、MstInGameの係数で難易度調整:

| ingame_id | シーケンスID参照 | normal_hp_coef | boss_hp_coef |
|-----------|----------------|---------------|-------------|
| hard_kai_00001 | normal_kai_00001 | 2.5 | 2.5 |
| hard_kai_00002 | normal_kai_00002 | 1.8 | 1.8 |
| hard_kai_00003 | normal_kai_00003 | 1.6 | 1.8 |
| hard_kai_00004 | normal_kai_00004 | 1.6 | 2.0 |
| hard_kai_00005 | normal_kai_00005 | 2.0 | 2.6 |
| hard_kai_00006 | normal_kai_00006 | 2.0 | 2.0 |

### veryhardブロックのシーケンス

| ingame_id | シーケンス数 | 主な使用エネミー |
|-----------|------------|----------------|
| veryhard_kai_00001 | 20 | e_glo_00001（GLO汎用、kai_vh用）＋e_kai_00301（kai_vh） |
| veryhard_kai_00002 | 19 | e_kai_00101（kai_vh系）＋e_kai_00001（kai_vh） |
| veryhard_kai_00003 | 28 | 複合構成 |
| veryhard_kai_00004 | 21 | 複合構成 |
| veryhard_kai_00005 | 19 | 複合構成 |
| veryhard_kai_00006 | 29 | e_kai_00101（kai_vh）＋e_kai_00301（kai_vh）＋e_kai_00201（kai_vh2）＋kai_honju_enemyギミック変身 |

veryhard_kai_00001は `e_glo_00001_general_kai_vh_Normal_Red`（HP:19,000）から開始し、`e_kai_00301_general_kai_vh_Normal_Green`（HP:120,000）への切り替えを`FriendUnitDead`で制御。

veryhard_kai_00006の特徴:
- `e_kai_00101_general_kai_vh_Normal_Yellow`（HP:40,000）で開始
- `e_kai_00301_general_kai_vh_Normal_Blue`（HP:250,000）が中盤登場
- `e_kai_00201_general_kai_vh2_Normal_Red`（HP:650,000）が後半登場
- `e_kai_00201_general_kai_vh2_Boss_Red`（HP:1,500,000）が最終ボス
- `SummonGimmickObject`（kai_honju_enemy_vh）×5体から`TransformGimmickObjectToEnemy`で`e_kai_00001_general_kai_vh_Normal_Green`（HP:250,000）に変身

---

## コンテンツ種別ごとの特徴比較

### アウトポストHP比較

| 種別 | アウトポストHP |
|-----|-------------|
| normal | 60,000〜70,000 |
| hard | 120,000〜145,000 |
| veryhard | 250,000〜275,000 |

### 使用エネミーキャラの違い

| 種別 | 主力雑魚 | ボス役 | 汎用補助敵 |
|-----|---------|-------|---------|
| normal | e_kai_00301（序盤）/ e_kai_00101（中盤以降）/ e_kai_00001（ギミック） | c_kai_00001, c_kai_00301, c_kai_00002 | なし |
| hard | 同シーケンス、係数で強化 | 同上 | なし |
| veryhard | e_kai_00101（kai_vh）/ e_kai_00301（kai_vh）/ e_kai_00201（kai_vh2） | c_kai_00001（kai_vh）, c_kai_00002（kai_vh）, c_kai_00301（kai_vh） | e_glo_00001（kai_vh用） |

> **重要**: `enemy_glo_00001`（GLO汎用敵）は veryhard ブロックから登場する。normal/hardブロックでは使用されない。

### BGM（bgm_asset_key）パターン

| 用途 | BGM |
|-----|-----|
| 通常バトル（normal/hard/veryhard/raid） | SSE_SBG_003_001 |

> **SPY×FAMILYとの重要な違い**: kaiは全コンテンツで `SSE_SBG_003_001` を使用。spyは `SSE_SBG_003_002` を使用。boss_bgm_asset_keyの設定なし。

### loop_background_asset_key（背景アセット）パターン

- normalブロック: `glo_00010`（00001, 00006）、`glo_00003`（00002）、`kai_00001`（00003, 00004, 00005）
- hard/veryhard/raidブロック: 未設定（空）

### コマアセットパターン

使用アセットキー（normalブロック確認分）:
- `kai_00001` — kai専用コマアセット（03〜05番で使用）
- `glo_00010` — GLO汎用コマアセット（01, 06番で使用）
- `glo_00003` — GLO汎用コマアセット（02番で使用）

---

## dungeon（限界チャレンジ）参照情報

> ※ dungeon用のデータは現時点でMstInGameに存在しない（生成対象）。
> 以下は既存のnormalブロックパターンからdungeon向けパラメータを推定するための参考情報。

### dungeonブロック仕様（CLAUDE.mdより）

| 種別 | インゲームID例 | MstEnemyOutpost HP | コマ行数 | ボスの有無 |
|------|--------------|-------------------|---------|----------|
| normal | `dungeon_kai_normal_00001` | 100（固定） | 3行（固定） | なし |
| boss | `dungeon_kai_boss_00001` | 1,000（固定） | 1行（固定） | あり |

### dungeon normalブロックの推奨パラメータ

- **BGM**: `SSE_SBG_003_001`（kai全コンテンツで統一使用）
- **loop_background_asset_key**: `kai_00001`（normalブロック中で最も多用される背景）
- **コマアセット**: `kai_00001`（3行固定、各行に2〜3コマ配置）
- **使用エネミー**: `enemy_kai_00101`（怪獣 余獣）をメイン推奨
  - `e_kai_00101_general_Normal_Colorless`（HP:25,000）— 序盤
  - `e_kai_00101_general_Normal_Green`（HP:67,000）— 中盤以降
- **敵パラメータ目安（dungeon normalブロック=low difficulty）**:
  - 雑魚HP: 25,000〜67,000（normalブロックの低〜中レンジ）
  - enemy_hp_coef: 1.0（係数なし）
  - MstEnemyOutpost HP: 100（dungeon固定仕様）
- **シーケンス構成**: normalブロック（2〜3シーケンス）を参考に
  - `ElapsedTime` + `FriendUnitDead` の組み合わせ
  - カフカや余獣を2〜3体召喚してウェーブ制御

### dungeon bossブロックの推奨パラメータ

- **BGM**: `SSE_SBG_003_001`
- **ボスエネミー候補**:
  - `e_kai_00101_general_Normal_Yellow`（HP:220,000）— 簡易ボス
  - `e_kai_00001_general_Boss_Yellow`（HP:440,000）— 中難度ボス
  - `c_kai_00002_general_Boss_Yellow`（HP:700,000、怪獣８号）— 高難度ボス
- **コマアセット**: `kai_00001`（1行固定）

---

## まとめ・パターン特徴

### kaiの雑魚敵使用ルール

1. **enemy_kai_00101**（怪獣 余獣）がnormal中盤〜の主力雑魚（03〜06番で使用）
2. **enemy_kai_00301**（蜘蛛の怪獣）はnormal序盤（01〜02番）で使用
3. **enemy_kai_00001**（怪獣 本獣）はギミック変身や終盤ボスとして使用
4. generalパラメータIDの命名規則: `e_{enemy_id}_general_{suffix}_{unit_kind}_{color}`
   - suffix: なし（normal用）, `kai_vh`（veryhard用）, `kai_vh2`（veryhard最高難度用）
   - unit_kind: `Normal`, `Boss`
5. **enemy_glo_00001**（GLO汎用敵）はveryhard帯から `kai_vh` 専用パラメータで補助として登場

### kaiの特徴的なギミック

- **本獣コクーン変身ギミック**: `SummonGimmickObject`（kai_honju_enemy / kai_honju_enemy_vh）を配置し、時間経過後に`TransformGimmickObjectToEnemy`でe_kai_00001に変身させる演出
  - normal_kai_00005 および veryhard_kai_00006 で使用
- **連続ボスチェーン**: normal_kai_00006では `FriendUnitDead` トリガーで3段階ボスが連続登場（カフカ→キコル→怪獣８号）

### ボスエネミーのキャラ

- `c_kai_00001_general_Normal_Yellow` — 日比野 カフカ（HP:150,000）
- `c_kai_00002_general_Boss_Yellow` — 怪獣８号（HP:700,000）
- `c_kai_00301_general_Normal_Yellow` — 四ノ宮 キコル（HP:300,000）

### SPY×FAMILYとの主要な相違点

| 項目 | kai | spy |
|-----|-----|-----|
| BGM | SSE_SBG_003_001（全コンテンツ共通） | SSE_SBG_003_002（通常）/ SSE_SBG_003_004（ボス）|
| normalアウトポストHP | 60,000〜70,000（段階増加） | 5,000（固定） |
| hardアウトポストHP | 120,000〜145,000 | 50,000（固定） |
| 難易度スケール方式 | hard: MstInGame係数（1.6〜2.6x）/ veryhard: シーケンス内で高HP直接設定 | hard/veryhard: シーケンス内 enemy_hp_coef（13〜135）で調整 |
| 主力雑魚 | enemy_kai_00101（余獣）/ enemy_kai_00301（蜘蛛）/ enemy_kai_00001（本獣） | enemy_spy_00001（密輸組織の残党）/ enemy_spy_00101（グエン） |
| 特殊ギミック | 本獣コクーン変身ギミック | なし（standard構成） |

### シーケンスのトリガー種別

確認された condition_type:
- `ElapsedTime` — 経過時間トリガー
- `InitialSummon` — 初期召喚（特定位置に配置）
- `FriendUnitDead` — 味方ユニット死亡トリガー
- `OutpostDamage` — アウトポストへのダメージトリガー

### dungeon生成時の留意点

1. **normalブロック（3行固定）** は `normal_kai_00003` または `normal_kai_00004`（共に3行）が最も参考になる
2. **アセットキー**:
   - BGM: `SSE_SBG_003_001`
   - 背景: `kai_00001`（kai作品専用背景）
   - コマ: `kai_00001`
3. **主力雑魚**: `enemy_kai_00101`（怪獣 余獣）
   - dungeon向け推奨パラメータID: `e_kai_00101_general_Normal_Colorless`（HP:25,000）
4. **GLO汎用敵は使用しない**（dungeon normalは作品専用雑魚のみ）
5. **kaiのnormalはHP/攻撃力が高め**: 既存normal基準のエネミーHPが25,000〜440,000と幅広いため、dungeonではColorless系（最も低HP）を基準にする
