# SPY×FAMILY インゲームパターン分析

## 概要

- series_id: `spy`
- URキャラ:
  - `chara_spy_00001` (Colorless / Special)
  - `chara_spy_00101` (Yellow / Attack)
  - `chara_spy_00201` (Red / Attack)
  - `chara_spy_00501` (Blue / Defense)
- インゲームコンテンツ数: 44件（MstInGame）
- MstPage: 43件（event_spy1_charaget02_00004は event_spy1_charaget02_00003と同じページIDを参照）

---

## コンテンツ種別一覧

### normal（通常ブロック）

| ingame_id | bgm_asset_key | loop_background_asset_key | mst_enemy_outpost_id | outpost_hp | artwork_asset_key |
|-----------|--------------|--------------------------|----------------------|-----------|------------------|
| normal_spy_00001 | SSE_SBG_003_002 | spy_00005 | normal_spy_00001 | 5,000 | spy_0001 |
| normal_spy_00002 | SSE_SBG_003_002 | spy_00005 | normal_spy_00002 | 5,000 | spy_0001 |
| normal_spy_00003 | SSE_SBG_003_002 | spy_00002 | normal_spy_00003 | 5,000 | spy_0001 |
| normal_spy_00004 | SSE_SBG_003_001 | spy_00002 | normal_spy_00004 | 5,000 | spy_0001 |
| normal_spy_00005 | SSE_SBG_003_003 | spy_00002 | normal_spy_00005 | 5,000 | spy_0001 |
| normal_spy_00006 | SSE_SBG_003_002 | spy_00001 | normal_spy_00006 | 5,000 | spy_0001 |

- normalブロックの共通仕様: アウトポストHP=5,000、ボスBGMなし
- loop_background_asset_keyはブロックによって異なる（`spy_00005`, `spy_00002`, `spy_00001`）

### hard（ハードブロック）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp | artwork_asset_key |
|-----------|--------------|----------------------|-----------|------------------|
| hard_spy_00001 | SSE_SBG_003_002 | hard_spy_00001 | 50,000 | spy_0002 |
| hard_spy_00002 | SSE_SBG_003_002 | hard_spy_00002 | 50,000 | spy_0002 |
| hard_spy_00003 | SSE_SBG_003_002 | hard_spy_00003 | 50,000 | spy_0002 |
| hard_spy_00004 | SSE_SBG_003_002 | hard_spy_00004 | 50,000 | spy_0002 |
| hard_spy_00005 | SSE_SBG_003_002 | hard_spy_00005 | 50,000 | spy_0002 |
| hard_spy_00006 | SSE_SBG_003_002 | hard_spy_00006 | 50,000 | spy_0002 |

### veryhard（超ハードブロック）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp | artwork_asset_key |
|-----------|--------------|----------------------|-----------|------------------|
| veryhard_spy_00001 | SSE_SBG_003_002 | veryhard_spy_00001 | 150,000 | spy_0003 |
| veryhard_spy_00002 | SSE_SBG_003_002 | veryhard_spy_00002 | 150,000 | spy_0003 |
| veryhard_spy_00003 | SSE_SBG_003_002 | veryhard_spy_00003 | 150,000 | spy_0003 |
| veryhard_spy_00004 | SSE_SBG_003_002 | veryhard_spy_00004 | 150,000 | spy_0003 |
| veryhard_spy_00005 | SSE_SBG_003_002 | veryhard_spy_00005 | 150,000 | spy_0003 |
| veryhard_spy_00006 | SSE_SBG_003_002 | veryhard_spy_00006 | 150,000 | spy_0003 |

### event（イベントブロック：event_spy1）

イベントの形式:
- `event_spy1_1day_00001` — 1日限定イベント（アウトポストHP: 500）
- `event_spy1_challenge01_00001〜00004` — チャレンジ（boss_bgm=SSE_SBG_003_004、HP: 20,000〜80,000）
- `event_spy1_charaget01_00001〜00008` — キャラゲット01（HP: 5,000〜50,000）
- `event_spy1_charaget02_00001〜00008` — キャラゲット02（HP: 5,000〜50,000）
- `event_spy1_savage_00001〜00002` — サベージ（HP: 80,000〜100,000）

### pvp（PVPブロック）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id |
|-----------|--------------|----------------------|
| pvp_spy_01 | SSE_SBG_003_007 | pvp |
| pvp_spy_02 | SSE_SBG_003_007 | pvp |

### raid（レイドボス）

| ingame_id | bgm_asset_key | mst_enemy_outpost_id | outpost_hp | is_damage_invalidation |
|-----------|--------------|----------------------|-----------|----------------------|
| raid_spy1_00001 | SSE_SBG_003_007 | raid_spy1_00001 | 1,000,000 | 1（無効化） |

---

## アウトポストHP スケール比較

| コンテンツ種別 | アウトポストHP | 係数比（normal基準） |
|--------------|-------------|---------------------|
| normal | 5,000 | 1.0x |
| hard | 50,000 | 10x |
| veryhard | 150,000 | 30x |
| event (1day) | 500 | 0.1x |
| event (challenge, 最低) | 20,000 | 4x |
| event (charaget, 最低) | 5,000 | 1x |
| event (savage, 最低) | 80,000 | 16x |
| raid | 1,000,000 | 200x |

---

## エネミー別パラメータ（MstEnemyStageParameter）

### 雑魚敵: enemy_spy_00001

generalグループ（通常・ハード・超ハード別）:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_spy_00001_general_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 34 | 0.4 | 50 | 1 | 300 |
| e_spy_00001_general_n_Normal_Blue | Normal | Defense | Blue | 1,000 | 34 | 0.4 | 50 | 1 | 400 |
| e_spy_00001_general_n_Boss_Blue | Boss | Attack | Blue | 10,000 | 34 | 0.4 | 50 | 1 | 300 |
| e_spy_00001_general_vh_Normal_Yellow | Normal | Defense | Yellow | 1,000 | 34 | 0.4 | 50 | 1 | 300 |

その他用途別:

| parameter_id | 用途 | HP | move_speed | attack_power |
|-------------|-----|---|---|---|
| e_spy_00001_damianget_Normal_Red | イベント | 5,000 | 25 | 300 |
| e_spy_00001_frankyget_Normal_Colorless | イベント | 5,000 | 25 | 300 |
| e_spy_00001_spy1challenge_Normal_Blue/Green/Yellow | チャレンジ | 5,000 | 25 | 300 |
| e_spy_00001_spy1savage_Normal_Colorless | サベージ | 5,000 | 70 | 300 |

### 雑魚敵: enemy_spy_00101

generalグループ:

| parameter_id | character_unit_kind | role_type | color | HP | move_speed | well_distance | attack_power | attack_combo_cycle | drop_battle_point |
|-------------|--------------------|-----------|----|---|---|---|---|---|---|
| e_spy_00101_general_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 31 | 0.2 | 50 | 1 | 200 |
| e_spy_00101_general_n_Boss_Blue | Boss | Attack | Blue | 10,000 | 31 | 0.2 | 50 | 1 | 500 |
| e_spy_00101_general_n_Boss_Colorless | Boss | Attack | Colorless | 10,000 | 31 | 0.2 | 50 | 1 | 500 |
| e_spy_00101_general_vh_Normal_Blue | Normal | Attack | Blue | 1,000 | 31 | 0.2 | 100 | 1 | 200 |
| e_spy_00101_general_vh_Normal_Green | Normal | Attack | Green | 1,000 | 31 | 0.2 | 100 | 1 | 200 |
| e_spy_00101_general_vh_Boss_Blue | Boss | Attack | Blue | 10,000 | 31 | 0.2 | 100 | 1 | 500 |
| e_spy_00101_general_vh_Boss_Green | Boss | Attack | Green | 10,000 | 31 | 0.2 | 100 | 1 | 500 |

### URキャラ（ボスとして登場）: chara_spy_00101

| parameter_id | 用途 | character_unit_kind | role_type | color | HP | move_speed | attack_power | drop_battle_point |
|-------------|-----|--------------------|-----------|----|---|---|---|---|
| c_spy_00101_general_n_Boss_Blue | normal用ボス | Boss | Attack | Blue | 10,000 | 31 | 50 | 500 |
| c_spy_00101_general_n_Boss_Red | normal用ボス | Boss | Attack | Red | 10,000 | 31 | 50 | 500 |
| c_spy_00101_general_h_Boss_Blue | hard用ボス | Boss | Attack | Blue | 10,000 | 31 | 50 | 120 |
| c_spy_00101_general_h_Boss_Red | hard用ボス | Boss | Attack | Red | 10,000 | 31 | 50 | 120 |
| c_spy_00101_general_vh_Boss_Blue | veryhard用ボス | Boss | Attack | Blue | 10,000 | 31 | 100 | 120 |
| c_spy_00101_general_vh_Boss_Colorless | veryhard用ボス | Boss | Attack | Colorless | 10,000 | 31 | 100 | 120 |
| c_spy_00101_general_vh_Boss_Red | veryhard用ボス | Boss | Attack | Red | 10,000 | 31 | 100 | 120 |

### URキャラ（ボスとして登場）: chara_spy_00201

| parameter_id | 用途 | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|-----|--------------------|-----------|----|---|---|---|
| c_spy_00201_general_n_Boss_Blue | normal用ボス | Boss | Attack | Blue | 10,000 | 45 | 50 |
| c_spy_00201_general_n_Boss_Colorless | normal用ボス | Boss | Attack | Colorless | 10,000 | 45 | 50 |
| c_spy_00201_general_h_Boss_Blue | hard用ボス | Boss | Attack | Blue | 10,000 | 45 | 50 |
| c_spy_00201_general_h_Boss_Colorless | hard用ボス | Boss | Attack | Colorless | 10,000 | 45 | 50 |
| c_spy_00201_general_vh_Boss_Blue | veryhard用ボス | Boss | Attack | Blue | 10,000 | 45 | 100 |
| c_spy_00201_general_vh_Boss_Yellow | veryhard用ボス | Boss | Attack | Yellow | 10,000 | 45 | 100 |

### URキャラ（ボスとして登場）: chara_spy_00401（注: URではなくSSR相当と思われる）

| parameter_id | 用途 | character_unit_kind | role_type | color | HP | move_speed | attack_power |
|-------------|-----|--------------------|-----------|----|---|---|---|
| c_spy_00401_general_n_Boss_Blue | normal用ボス | Boss | Defense | Blue | 10,000 | 31 | 50 |
| c_spy_00401_general_n_Boss_Colorless | normal用ボス | Boss | Defense | Colorless | 10,000 | 31 | 50 |
| c_spy_00401_general_h_Boss_Blue | hard用ボス | Boss | Defense | Blue | 10,000 | 31 | 50 |
| c_spy_00401_general_h_Boss_Colorless | hard用ボス | Boss | Defense | Colorless | 10,000 | 31 | 50 |
| c_spy_00401_general_vh_Boss_Blue | veryhard用ボス | Boss | Defense | Blue | 10,000 | 31 | 100 |
| c_spy_00401_general_vh_Boss_Green | veryhard用ボス | Boss | Defense | Green | 10,000 | 31 | 100 |

---

## コマライン（MstKomaLine）パターン

### normalブロックのコマ行数

| ingame_id | 行数 | コマ構成 |
|-----------|-----|---------|
| normal_spy_00001 | 2行 | 行1: spy_00005×2、行2: spy_00005×2 |
| normal_spy_00002 | 2行 | 行1: spy_00005×2、行2: spy_00005×1 |
| normal_spy_00003 | 3行 | 行1: spy_00002×2、行2: spy_00002×3、行3: spy_00002×2 |
| normal_spy_00004 | 4行 | 行1: spy_00002×2、行2: spy_00002×2、行3: spy_00002×3、行4: spy_00002×1 |
| normal_spy_00005 | 4行 | 行1: spy_00006×2、行2: spy_00006×1、行3: spy_00006×2、行4: spy_00006×1 |
| normal_spy_00006 | 3行 | 行1: spy_00001×2、行2: spy_00001×1、行3: spy_00001×2 |

> ※ コマアセットキーは `spy_00001`〜`spy_00006` が存在する。

### hardブロックのコマ行数

| ingame_id | 行数 | コマ構成（主要） |
|-----------|-----|---------|
| hard_spy_00001 | 2行 | spy_00005×2 |
| hard_spy_00002 | 2行 | spy_00005×2、spy_00005×1 |
| hard_spy_00003 | 3行 | spy_00002×2、spy_00002×3、spy_00002×2 |
| hard_spy_00004 | 4行 | spy_00002系 |
| hard_spy_00005 | 3行 | spy_00006×2、spy_00006×1、spy_00006×2 |
| hard_spy_00006 | 3行 | spy_00001×2、spy_00001×1、spy_00001×2 |

### veryhardブロックのコマ行数

| ingame_id | 行数 | コマ構成（主要） |
|-----------|-----|---------|
| veryhard_spy_00001 | 2行 | spy_00005×2、spy_00005×1 |
| veryhard_spy_00002 | 3行 | spy_00005×2、spy_00005×3、spy_00005×1（※IDが一部重複） |
| veryhard_spy_00003 | 3行 | spy_00002×1、spy_00002×3、spy_00002×2 |
| veryhard_spy_00004 | 1行 | spy_00002×3（height: 0.8） |
| veryhard_spy_00005 | 2行 | spy_00006×1、spy_00006×3 |
| veryhard_spy_00006 | 3行 | spy_00001×2、spy_00001×1、spy_00001×2 |

---

## シーケンスパターン（MstAutoPlayerSequence）

### normalブロックのシーケンス

normalブロックのエネミーHP係数とシーケンス概要:

| ingame_id | シーケンス数 | 使用エネミー | enemy_hp_coef | enemy_attack_coef |
|-----------|------------|------------|--------------|------------------|
| normal_spy_00001 | 1 | e_spy_00101_general_n_Normal_Colorless | 1.5 | 2 |
| normal_spy_00002 | 2 | e_spy_00101系 | 1.5 | 2〜4 |
| normal_spy_00003 | 3 | e_spy_00001系 | 1.5 | 2 |
| normal_spy_00004 | 11 | e_spy_00001系（グループ切替） | 1.5 | 2 |
| normal_spy_00005 | 6 | e_spy_00001系、chara_spy_00201/00401 | 1.5〜8 | 1.6〜4 |
| normal_spy_00006 | 12 | e_spy_00001系、e_spy_00101系、chara_spy_00101/00201/00401 | 1.5〜8 | 0.6〜4 |

#### normal_spy_00001 の詳細シーケンス

```
要素1: ElapsedTime=650 → SummonEnemy(e_spy_00101_general_n_Normal_Colorless, count=1)
        enemy_hp_coef=1.5, enemy_attack_coef=2
```

単純な1シーケンス構成（時間経過でColorless雑魚が1体召喚される）。

#### normal_spy_00002 の詳細シーケンス

```
要素1: InitialSummon=1 → SummonEnemy(e_spy_00101_general_n_Normal_Colorless, count=1, pos=1.6)
        enemy_hp_coef=1.5, enemy_attack_coef=2
要素2: FriendUnitDead=1 → SummonEnemy(e_spy_00101_general_n_Boss_Colorless, count=1)
        enemy_hp_coef=1.5, enemy_attack_coef=4
```

#### normal_spy_00003〜00006

`enemy_spy_00001`（ヨル・フォージャー系？）を主軸に使用。ボス役として `enemy_spy_00001_general_n_Boss_Blue` や URキャラ系パラメータを召喚する構成。

### hardブロックのシーケンス

| ingame_id | シーケンス数 | 主要enemy_hp_coef範囲 |
|-----------|------------|---------------------|
| hard_spy_00001 | 2 | 16〜18 |
| hard_spy_00002 | 3 | 15〜18 |
| hard_spy_00003 | 6 | 13.5〜38.5 |
| hard_spy_00004 | 13 | 13〜16 |
| hard_spy_00005 | 12 | 13〜29 |
| hard_spy_00006 | 22（グループ切替含む） | 1.9〜38.5 |

hardのenemy_hp_coef は 13〜40 程度（normal比で約10倍）。

### veryhardブロックのシーケンス

| ingame_id | シーケンス数 | 主要enemy_hp_coef範囲 |
|-----------|------------|---------------------|
| veryhard_spy_00001 | 10 | 2.8〜42 |
| veryhard_spy_00002 | 10 | 28〜80 |
| veryhard_spy_00003 | 11 | 2.8〜75 |
| veryhard_spy_00004 | 10 | 1〜40 |
| veryhard_spy_00005 | 9 | 7.5〜58 |
| veryhard_spy_00006 | 22（グループ切替含む） | 3.25〜135 |

veryhardのenemy_hp_coef は 30〜135 程度（normal比で約20〜90倍）。

---

## コンテンツ種別ごとの特徴比較

### アウトポストHP比較

| 種別 | アウトポストHP |
|-----|-------------|
| normal | 5,000 |
| hard | 50,000 |
| veryhard | 150,000 |

### エネミー難易度係数（enemy_hp_coef）比較

| 種別 | enemy_hp_coef（典型値） | コンテンツHP係数との一致 |
|-----|----------------------|----------------------|
| normal | 1.5 | 一致（outpost×1.5程度） |
| hard | 13〜20 | 一致（outpost×10倍） |
| veryhard | 28〜42（ノーマル敵）/ 75〜135（ボス） | 一致（outpost×30倍） |

### 使用エネミーキャラの違い

| 種別 | 主力雑魚 | ボス役 | 汎用補助敵 |
|-----|---------|-------|---------|
| normal | enemy_spy_00101（序盤）/ enemy_spy_00001（中盤以降） | chara_spy_00101/00201/00401 | なし |
| hard | enemy_spy_00101/00001 | chara_spy_00101/00201/00401 | enemy_glo_00001 |
| veryhard | enemy_spy_00101/00001 | chara_spy_00101/00201/00401 | enemy_glo_00001 |

> **重要**: `enemy_glo_00001` （GLO汎用敵）は hard および veryhard ブロックから登場する。normalブロックでは使用されない。

### BGM（bgm_asset_key）パターン

| 用途 | BGM |
|-----|-----|
| 通常バトル（normal/hard/veryhard） | SSE_SBG_003_002 |
| ボスBGM（イベントチャレンジ等） | SSE_SBG_003_004 |
| PVP/レイド | SSE_SBG_003_007 |
| ノーマル別パターン | SSE_SBG_003_001、SSE_SBG_003_003 |

### loop_background_asset_key（背景アセット）パターン

- `spy_00001`〜`spy_00006` の6種類が存在する
- normalブロックで確認できるもの: `spy_00005`（00001〜00002）、`spy_00002`（00003〜00005）、`spy_00001`（00006）

### コマエフェクト（KomaLine）のパターン

エフェクト種別一覧（確認分）:
- `None` — エフェクトなし
- `AttackPowerUp` — 攻撃力アップ（パラメータ: 10〜30%アップ）
- `AttackPowerDown` — 攻撃力ダウン（パラメータ: 50%ダウン）
- `Gust` — 突風（パラメータ: 200〜400）
- `Darkness` — 暗闇
- `SlipDamage` — スリップダメージ（パラメータ: 400）
- `Poison` — 毒（パラメータ: 750）

normalブロックでは `None`, `AttackPowerUp`, `Gust` が主に使用される。
veryhard以降では `Darkness`, `SlipDamage`, `Poison` などの強力なエフェクトが追加される。

---

## dungeon（限界チャレンジ）参照情報

> ※ dungeon用のデータは現時点でMstInGameに存在しない（生成対象）。
> 以下は既存のnormalブロックパターンからdungeon向けパラメータを推定するための参考情報。

### dungeonブロック仕様（CLAUDE.mdより）

| 種別 | インゲームID例 | MstEnemyOutpost HP | コマ行数 | ボスの有無 |
|------|--------------|-------------------|---------|----------|
| normal | `dungeon_spy_normal_00001` | 100（固定） | 3行（固定） | なし |
| boss | `dungeon_spy_boss_00001` | 1,000（固定） | 1行（固定） | あり |

### dungeon normalブロックの推奨パラメータ（参考）

normalブロックパターンを参考に:
- BGM: `SSE_SBG_003_002`（通常BGM）
- loop_background_asset_key: `spy_00005`（normalで最初に使用される背景）
- 使用エネミー: `enemy_spy_00001`（メイン）、`enemy_spy_00101`（サブ）
- enemy_hp_coef: 通常normalの1.5前後を基準に調整
- コマライン: 3行固定、各行にspy系アセット使用

---

## まとめ・パターン特徴

### 雑魚敵の使用ルール

1. **enemy_spy_00001**（ヨル系？）と **enemy_spy_00101**（アーニャ系？）が主力雑魚
2. generalパラメータIDの命名規則: `e_{enemy_id}_general_{難易度}_{unit_kind}_{color}`
   - 難易度: `n`（normal）, `h`（hard）, `vh`（veryhard）
   - unit_kind: `Normal`, `Boss`
3. **enemy_glo_00001**（GLO汎用敵）はhard/veryhard帯から補助として登場

### ボスエネミーのキャラ

ゲームキャラ（c_spy_XXXXX_general_*）がボスとして登場:
- `c_spy_00101_general_n_Boss_*` — Yellow: ヨル（通常難易度ボス）
- `c_spy_00201_general_n_Boss_*` — Red: ロイド（通常難易度ボス）
- `c_spy_00401_general_n_Boss_*` — Colorless: ボンド（通常難易度ボス）

### シーケンスのトリガー種別

確認された condition_type:
- `ElapsedTime` — 経過時間トリガー
- `InitialSummon` — 初期召喚（特定位置に配置）
- `FriendUnitDead` — 味方ユニット死亡トリガー
- `FriendUnitDead` + `SwitchSequenceGroup` — グループ切替（高難易度向け）
- `OutpostHpPercentage` — アウトポストHP割合トリガー
- `EnterTargetKomaIndex` — 特定コマインデックス到達トリガー
- `DarknessKomaCleared` — 暗闇コマクリアトリガー
- `ElapsedTimeSinceSequenceGroupActivated` — グループ活性化からの経過時間

### dungeon生成時の留意点

1. **normalブロック（3行固定）** は `normal_spy_00003`（3行）が最も参考になる構成
2. **アセットキー**:
   - BGM: `SSE_SBG_003_002`
   - 背景: `spy_00005`（明るめの背景として初期normalで使用）
3. **enemy_hp_coef**: dungeonはnormalブロックの1.5倍程度が目安
4. **コマアセット**: `spy_00001`〜`spy_00006`から適切なものを選択
5. **GLO汎用敵**は使用しない（dungeon normalは作品専用雑魚のみ）
