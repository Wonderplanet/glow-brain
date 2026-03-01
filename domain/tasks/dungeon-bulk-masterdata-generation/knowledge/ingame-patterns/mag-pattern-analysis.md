# 株式会社マジルミエ インゲームパターン分析

## 概要

- series_id: `mag`
- URキャラ: `chara_mag_00001` (Blue / Attack)、`chara_mag_00201` (Red / Attack)
- インゲームコンテンツ数: 45件（`%_mag_%` パターン全体）
- 調査日: 2026-03-01

---

## コンテンツ種別一覧

### normal（通常）

| ingame_id | BGM | 背景アセット | MstEnemyOutpost HP |
|-----------|-----|-------------|-------------------|
| normal_mag_00001 | SSE_SBG_003_001 | mag_00004 | 60,000 |
| normal_mag_00002 | SSE_SBG_003_001 | mag_00004 | 62,000 |
| normal_mag_00003 | SSE_SBG_003_001 | mag_00004 | 64,000 |
| normal_mag_00004 | SSE_SBG_003_001 | mag_00001 | 66,000 |
| normal_mag_00005 | SSE_SBG_003_001 | mag_00003 | 68,000 |
| normal_mag_00006 | SSE_SBG_003_001 | glo_00008 | 70,000 |

> BGM は全て `SSE_SBG_003_001`（作品固有BGM）で統一。
> normalのHP帯は 60,000〜70,000 と他作品より高め（dan: 15,000、spy: 未調査）。
> `normal_mag_00006` のみ背景アセットに汎用 `glo_00008` を使用。

### hard（ハード）

| ingame_id | BGM | MstEnemyOutpost HP |
|-----------|-----|-------------------|
| hard_mag_00001 | SSE_SBG_003_001 | 120,000 |
| hard_mag_00002 | SSE_SBG_003_001 | 125,000 |
| hard_mag_00003 | SSE_SBG_003_001 | 130,000 |
| hard_mag_00004 | SSE_SBG_003_001 | 135,000 |
| hard_mag_00005 | SSE_SBG_003_001 | 140,000 |
| hard_mag_00006 | SSE_SBG_003_001 | 145,000 |

### veryhard（超ハード）

| ingame_id | BGM | MstEnemyOutpost HP |
|-----------|-----|-------------------|
| veryhard_mag_00001 | SSE_SBG_003_001 | 250,000 |
| veryhard_mag_00002 | SSE_SBG_003_001 | 255,000 |
| veryhard_mag_00003 | SSE_SBG_003_001 | 260,000 |
| veryhard_mag_00004 | SSE_SBG_003_001 | 265,000 |
| veryhard_mag_00005 | SSE_SBG_003_001 | 270,000 |
| veryhard_mag_00006 | SSE_SBG_003_001 | 275,000 |

### event（イベント）— event_mag1

| ingame_id | 種別 | MstEnemyOutpost HP |
|-----------|------|-------------------|
| event_mag1_1day_00001 | 1日限定 | 500 |
| event_mag1_challenge01_00001〜00004 | チャレンジ | 40,000〜80,000 |
| event_mag1_charaget01_00001〜00008 | キャラゲット1 | 5,000〜50,000 |
| event_mag1_charaget02_00001〜00008 | キャラゲット2 | 5,000〜50,000 |
| event_mag1_savage_00001〜00002 | 猛攻 | 120,000〜150,000 |

### その他

| ingame_id | 種別 | MstEnemyOutpost HP |
|-----------|------|-------------------|
| pvp_mag_01 | PvP | pvp（共通） |
| raid_mag1_00001 | レイド | 1,000,000 |

---

## エネミーキャラクター一覧

mag で使用されているエネミーキャラクターは以下の通り：

| asset_key | 日本語名 | 種別 | 主な役割 |
|-----------|---------|------|---------|
| enemy_mag_00001 | 冷却系怪異 | 雑魚（主力） | Attack（ノーマル通常に最頻出） |
| enemy_mag_00101 | つらら | 雑魚（主力） | Attack（高速タイプ） |
| enemy_mag_00201 | 建造物寄生型の怪異 (大) | 大型ボス用 | Defense（大型・boss種別多） |
| enemy_mag_00301 | 建造物寄生型の怪異 | 雑魚（後半サブ） | Defense（normal後半で多用） |
| enemy_mag_00401 | 工事現場の怪異 | イベント・猛攻専用 | Defense（special用途） |
| chara_mag_00001 | 新人魔法少女 桜木 カナ | ガチャキャラ | Attack |
| chara_mag_00101 | 越谷 仁美 | ガチャキャラ | Attack |
| chara_mag_00201 | 絶対効率の体現者 土刃 メイ（URキャラ） | ガチャキャラ | Attack |
| chara_mag_00301 | 葵 リリー | ガチャキャラ | Defense |
| chara_mag_00401 | 槇野 あかね | ガチャキャラ | Technical |

> **主力2体体制**: `enemy_mag_00001`（冷却系怪異 / 攻撃型・無色）＋ `enemy_mag_00101`（つらら / 攻撃型・青）が normal 前半（00001〜00003）のメイン雑魚。
> normal 後半（00004〜00006）は `enemy_mag_00301`（建造物寄生型の怪異）が主役に切り替わる。

---

## エネミー別パラメータ詳細

### enemy_mag_00001（冷却系怪異 / 主力・攻撃タイプ）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_mag_00001_general_Normal_Colorless | Normal | Attack | Colorless | 70,000 | 35 | 1,200 | 1 |
| e_mag_00001_general_Boss_Blue | Boss | Attack | Blue | 270,000 | 35 | 3,800 | 1 |
| e_mag_00001_general2_Boss_Blue | Boss | Attack | Blue | 1,000,000 | 35 | 2,500 | 1 |
| e_mag_00001_general_vh_Normal_Blue | Normal | Attack | Blue | 400,000 | 20 | 1,250 | 1 |
| e_mag_00001_general_vh_Boss_Blue | Boss | Attack | Blue | 600,000 | 35 | 1,500 | 1 |
| e_mag_00001_challange_Normal_Yellow | Normal | Technical | Yellow | 100,000 | 25 | 300 | 1 |
| e_mag_00001_mag1_advent_Boss_Green | Boss | Technical | Green | 100,000 | 25 | 300 | 1 |

> `general_Normal_Colorless` が normal ステージ序盤の基本雑魚。HP=70,000、speed=35、attack=1,200 は mag 作品の標準スペック。
> `general_Boss_Blue` / `general2_Boss_Blue` は撃破トリガーで召喚されるボス型（HP270,000〜1,000,000）。

### enemy_mag_00101（つらら / 高速・攻撃タイプ）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_mag_00101_general_Normal_Blue | Normal | Attack | Blue | 10,000 | 100 | 1,500 | 1 |
| e_mag_00101_general_Normal_Colorless | Normal | Attack | Colorless | 20,000 | 100 | 800 | 1 |
| e_mag_00101_general2_Normal_Blue | Normal | Attack | Blue | 20,000 | 100 | 400 | 1 |
| e_mag_00101_general2_Normal_Colorless | Normal | Attack | Colorless | 30,000 | 100 | 700 | 1 |
| e_mag_00101_general_h_Normal_Blue | Normal | Attack | Blue | 8,000 | 100 | 500 | 1 |
| e_mag_00101_general2_h_Normal_Blue | Normal | Attack | Blue | 8,000 | 100 | 500 | 1 |
| e_mag_00101_general_vh_Normal_Blue | Normal | Attack | Blue | 25,000 | 100 | 2,000 | 1 |
| e_mag_00101_general_f_vh_Normal_Blue | Normal | Attack | Blue | 25,000 | 100 | 2,000 | 1 |

> `enemy_mag_00101` は **speed=100** と極めて高速な雑魚（spy_00101: speed=38 と比較して約2.6倍）。
> 大量（count=99）で召喚されるパターンが多く、高速ラッシュが特徴。

### enemy_mag_00301（建造物寄生型の怪異 / サブ雑魚・防御タイプ）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_mag_00301_general_Normal_Colorless | Normal | Defense | Colorless | 8,000 | 45 | 300 | 1 |
| e_mag_00301_general_Normal_Blue | Normal | Defense | Blue | 20,000 | 45 | 500 | 1 |
| e_mag_00301_general_Normal_Red | Normal | Defense | Red | 70,000 | 45 | 700 | 1 |
| e_mag_00301_general_vh_Normal_Green | Normal | Attack | Green | 50,000 | 45 | 700 | 1 |
| e_mag_00301_general_vh_Normal_Yellow | Normal | Attack | Yellow | 100,000 | 45 | 900 | 1 |

> normal ステージ 00004〜00006 で主力として登場。`_Normal_Red`（HP=70,000）は最もタフな雑魚。

### enemy_mag_00201（建造物寄生型の怪異 (大) / 大型ボス型）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_mag_00201_general_Boss_Red | Boss | Attack | Red | 500,000 | 35 | 1,000 | 7 |
| e_mag_00201_general_vh_Boss_Green | Boss | Attack | Green | 600,000 | 35 | 2,000 | 1 |
| e_mag_00201_siget_Boss_Colorless | Boss | Defense | Colorless | 30,000 | 25 | 100 | 1 |
| e_mag_00201_savage_Boss_Yellow | Boss | Defense | Yellow | 300,000 | 25 | 500 | 1 |

> 主に hard/veryhard/savage 等の上位コンテンツで使用。combo=7 の高コンボ攻撃が特徴。

---

## シーケンスパターン分析

### normal_mag_00001（最シンプル・enemy_mag_00001 体制）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | InitialSummon | 2 | SummonEnemy | e_mag_00001_general_Normal_Colorless | 1 | 1 | 1 |
| 2 | FriendUnitDead | 1 | SummonEnemy | e_mag_00001_general2_Boss_Blue | 1 | 1 | 1 |

- 開幕から `enemy_mag_00001`（無色）が 1 体召喚
- 1体撃破で `e_mag_00001_general2_Boss_Blue`（HP=1,000,000 の強ボス）が登場
- 最もシンプルな2ステップ構成

### normal_mag_00002（enemy_mag_00101 ラッシュ追加）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | InitialSummon | 2 | SummonEnemy | e_mag_00001_general_Normal_Colorless | 1 | 1 | 1 |
| 2 | FriendUnitDead | 1 | SummonEnemy | e_mag_00001_general_Boss_Blue | 1 | 1 | 1 |
| 3 | FriendUnitDead | 1 | SummonEnemy | e_mag_00101_general_Normal_Blue | 99 | 1 | 1 |

- 00001 と同様の開幕パターン
- 1体撃破後、`e_mag_00001_general_Boss_Blue`（HP=270,000）と共に `e_mag_00101`（高速・count=99）が大量ラッシュ

### normal_mag_00003（enemy_mag_00101 多重ラッシュ）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | InitialSummon | 2 | SummonEnemy | e_mag_00001_general_Normal_Colorless | 1 | 1 | 1 |
| 2 | FriendUnitDead | 1 | SummonEnemy | e_mag_00001_general_Boss_Blue | 1 | 1 | 1 |
| 3 | FriendUnitDead | 1 | SummonEnemy | e_mag_00101_general_Normal_Blue | 99 | 1 | 1 |
| 4 | FriendUnitDead | 1 | SummonEnemy | e_mag_00101_general_Normal_Colorless | 99 | 1 | 1 |
| 5 | FriendUnitDead | 1 | SummonEnemy | e_mag_00101_general_Normal_Colorless | 99 | 1 | 1 |

- 00002 パターンの拡張版
- 撃破トリガーで `e_mag_00101` の青・無色・無色（それぞれ count=99）が三重ラッシュ

### normal_mag_00004（enemy_mag_00301 体制・ElapsedTime多用）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | ElapsedTime | 100 | SummonEnemy | e_mag_00301_general_Normal_Colorless | 3 | 1 | 1 |
| 2 | ElapsedTime | 1000 | SummonEnemy | e_mag_00301_general_Normal_Colorless | 3 | 1 | 1 |
| 3 | ElapsedTime | 1700 | SummonEnemy | e_mag_00301_general_Normal_Colorless | 3 | 1 | 1 |
| 4 | ElapsedTime | 2000 | SummonEnemy | e_mag_00301_general_Normal_Blue | 1 | 1 | 1 |
| 5 | FriendUnitDead | 4 | SummonEnemy | e_mag_00301_general_Normal_Colorless | 1 | 1 | 1 |
| ... | ... | ... | ... | ... | ... | ... | ... |
| 10 | FriendUnitDead | 7 | SummonEnemy | e_mag_00301_general_Normal_Red | 99 | 1 | 1 |
| 11 | ElapsedTime | 3500 | SummonEnemy | e_mag_00301_general_Normal_Blue | 99 | 1 | 1 |
| 12 | ElapsedTime | 3400 | SummonEnemy | e_mag_00301_general_Normal_Red | 99 | 1 | 1 |
| 13 | OutpostDamage | 1 | SummonEnemy | e_mag_00301_general_Normal_Red | 99 | 1 | 1 |
| 14 | OutpostDamage | 1 | SummonEnemy | e_mag_00301_general_Normal_Red | 99 | 1 | 1 |

- `enemy_mag_00301` に主役が切り替わる最初の面（14行・最複雑クラス）
- `OutpostDamage` トリガー（アウトポストへのダメージ発生時）が初登場
- 3色（Colorless/Blue/Red）を段階的に出現させる複雑構成

### normal_mag_00005（ギミックオブジェクト・マンホール出現）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1〜26 | 複合 | 複合 | SummonEnemy / SummonGimmickObject / TransformGimmickObjectToEnemy | e_mag_00301_* / mag_manhole_enemy | 複合 | 1 | 1 |
| 特徴行 | InitialSummon | 2 | SummonGimmickObject | mag_manhole_enemy | 1 | 1 | 1 |
| 特徴行 | FriendUnitDead | 3 | TransformGimmickObjectToEnemy | e_mag_00301_general_Normal_Colorless | 1 | 1 | 1 |
| 特徴行 | FriendUnitDead | 14 | TransformGimmickObjectToEnemy | e_mag_00301_general_Normal_Blue | 1 | 1 | 1 |

> **mag固有ギミック**: `mag_manhole_enemy`（マンホール型ギミックオブジェクト）が登場し、敵撃破数に応じて `TransformGimmickObjectToEnemy` で実際の敵に変化する特殊ギミックが使用される。
> 全26行と最も複雑な構成（4行ページ構成に対応）。

### normal_mag_00006（chara 混合・最終面）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | ElapsedTime | 350 | SummonEnemy | c_mag_00101_general_Normal_Blue | 1 | 1 | 1 |
| 2 | FriendUnitDead | 1 | SummonEnemy | c_mag_00001_general_Boss_Blue | 1 | 1 | 1 |
| 3 | FriendUnitDead | 1 | SummonEnemy | e_mag_00101_general2_Normal_Blue | 99 | 1 | 1 |
| 4 | FriendUnitDead | 1 | SummonEnemy | e_mag_00101_general2_Normal_Colorless | 99 | 1 | 1 |
| 5 | FriendUnitDead | 1 | SummonEnemy | e_mag_00101_general2_Normal_Colorless | 99 | 1 | 1 |
| 6 | ElapsedTime | 500 | SummonEnemy | e_mag_00301_general_Normal_Colorless | 99 | 0.65 | 0.5 |

- `chara_mag_00101`（越谷 仁美）が先行登場し、倒すと `chara_mag_00001`（桜木 カナ）がボスとして登場
- `e_mag_00101_general2` が三重ラッシュ（それぞれ count=99）
- 最後に `e_mag_00301`（hp_coef=0.65, atk_coef=0.5）が追加（弱体化版）

---

## コマライン構成（normal）

| ingame_id | 行数 | row1 構成 | row2 構成 | row3 構成 | row4 構成 |
|-----------|------|-----------|-----------|-----------|-----------|
| normal_mag_00001 | 3 | koma1(0.5)+koma2(0.5) | koma1(0.25)+koma2(0.5)+koma3(0.25) | koma1(0.25)+koma2(0.25)+koma3(0.5) | - |
| normal_mag_00002 | 3 | koma1(0.75)+koma2(0.25) | koma1(0.5)+koma2(0.5) | koma1(0.25)+koma2(0.75) | - |
| normal_mag_00003 | 3 | koma1(0.4)+koma2(0.2)+koma3(0.4) | koma1(0.25)+koma2(0.5)+koma3(0.25) | koma1(1.0) | - |
| normal_mag_00004 | 3 | koma1(0.25)+koma2(0.75) | koma1(0.25)+koma2(0.25)+koma3(0.25)※ | koma1(0.75)+koma2(0.25) | - |
| normal_mag_00005 | 4 | koma1(0.4)+koma2(0.6) | koma1(0.33)+koma2(0.34)+koma3(0.33) | koma1(1.0) | koma1(0.25)+koma2(0.75) |
| normal_mag_00006 | 3 | koma1(0.6)+koma2(0.4) | koma1(0.5)+koma2(0.5) | koma1(0.25)+koma2(0.5)+koma3(0.25) | - |

> **注意**: `normal_mag_00005` は例外的に **4行構成**（ギミックオブジェクト面）。通常の normal は 3行固定。
> dungeon（限界チャレンジ）の **normal ブロックは 3 行固定**、**boss ブロックは 1 行固定**。

---

## コンテンツ種別ごとの特徴比較

### アウトポスト HP スケール

| 種別 | HP |
|------|-----|
| normal | 60,000〜70,000 |
| hard | 120,000〜145,000 |
| veryhard | 250,000〜275,000 |
| event（charaget最終） | 50,000 |
| event（challenge最終） | 80,000 |
| event（savage最終） | 150,000 |
| raid | 1,000,000 |

> normal HP 60,000〜70,000 は作品固有の高い値。hard は normalの約2倍、veryhard は約4倍のスケール。

### 雑魚敵の使われ方（normal ステージ別）

| ステージ | メイン雑魚 | サブ雑魚 | 特徴 |
|--------|----------|----------|------|
| normal_00001 | enemy_mag_00001 (Colorless) | enemy_mag_00001 (Blue, Boss) | 最シンプル・2ステップ |
| normal_00002 | enemy_mag_00001 (Colorless) | enemy_mag_00001 (Blue, Boss) + enemy_mag_00101 x99 | 高速ラッシュ追加 |
| normal_00003 | enemy_mag_00001 (Colorless) | enemy_mag_00001 (Blue, Boss) + enemy_mag_00101 x99 x3 | 三重ラッシュ |
| normal_00004 | enemy_mag_00301 (3色) | - | ElapsedTime多用・OutpostDamageトリガー |
| normal_00005 | enemy_mag_00301 (3色) | - | マンホールギミック・4行構成 |
| normal_00006 | chara_mag_00101 → chara_mag_00001 | enemy_mag_00101 x99 x3 + enemy_mag_00301 (弱) | chara混合・最終面 |

### hard/veryhard のシーケンス参照先

| hard/veryhard ID | 参照シーケンス（mst_auto_player_sequence_id） |
|-----------------|----------------------------------------------|
| hard_mag_00001 | normal_mag_00001 |
| hard_mag_00002 | normal_mag_00002 |
| hard_mag_00003 | normal_mag_00003 |
| hard_mag_00004 | hard_mag_00004（独自） |
| hard_mag_00005 | hard_mag_00005（独自） |
| hard_mag_00006 | hard_mag_00006（独自） |

> hard の 00001〜00003 は normal と同じシーケンスを使い回し、HP係数・攻撃係数のみ上昇させている。
> veryhard は全て独自シーケンスを保有。

---

## 背景・BGMアセット情報

| アセットキー | 説明 |
|-------------|------|
| mag_00001 | マジルミエ背景1（normal_00004で使用） |
| mag_00003 | マジルミエ背景3（normal_00005で使用） |
| mag_00004 | マジルミエ背景4（normal_00001〜00003で使用、最多） |
| glo_00008 | 汎用背景（normal_00006で使用） |
| SSE_SBG_003_001 | マジルミエ通常BGM（全normalで共通） |

---

## dungeon設計向けの推奨パラメータ

### dungeon normalブロックの想定設計

```
インゲームID: dungeon_mag_normal_00001
BGM: SSE_SBG_003_001（作品BGM）
背景アセット: mag_00004（最も多用）
MstEnemyOutpost HP: 100（dungeon固定）
行数: 3行

使用する雑魚敵（推奨）:
  - メイン: enemy_mag_00001（冷却系怪異 / Attack / Colorless, HP=70,000, speed=35, attack=1,200）
  - サブ: enemy_mag_00101（つらら / Attack / Blue, HP=10,000, speed=100, attack=1,500）

推奨パラメータキー:
  - e_mag_00001_general_Normal_Colorless（HP=70,000, speed=35, atk=1,200）
  - e_mag_00101_general_Normal_Blue（HP=10,000, speed=100, atk=1,500）

シーケンス参考パターン（normal_mag_00001ベース）:
  - condition_type: InitialSummon → SummonEnemy: e_mag_00001_general_Normal_Colorless, count=1
  - condition_type: FriendUnitDead / ElapsedTime → SummonEnemy: e_mag_00101_general_Normal_Blue, count=3〜5
```

### dungeon bossブロックの想定設計

```
インゲームID: dungeon_mag_boss_00001
BGM: SSE_SBG_003_001（作品BGM）
MstEnemyOutpost HP: 1,000（dungeon固定）
行数: 1行

URキャラをボスとして配置（いずれか）:
  - chara_mag_00001（新人魔法少女 桜木 カナ / Blue / Attack）
    → challange スペック: HP=100,000, speed=35, attack=500 が dungeon 向き
  - chara_mag_00201（絶対効率の体現者 土刃 メイ / Red / Attack）
    → challange スペック: HP=100,000, speed=40, attack=500 が dungeon 向き

推奨パラメータキー:
  - c_mag_00001_challange_Boss_Yellow（HP=100,000, speed=35, atk=500）
  - c_mag_00201_challange_Boss_Green（HP=100,000, speed=40, atk=500）
```

---

## まとめ・パターン特徴

1. **2段階体制**: normal 前半（00001〜00003）は `enemy_mag_00001`（冷却系怪異）が主役、後半（00004〜00006）は `enemy_mag_00301`（建造物寄生型の怪異）に切り替わる特徴的な設計。

2. **高速ラッシュが特徴**: `enemy_mag_00101`（つらら）は **speed=100** と全作品中最速クラスの雑魚。count=99 での大量召喚とセットで使用され、速攻型の高難度体験を演出。

3. **mag固有ギミック**: `SummonGimmickObject`（`mag_manhole_enemy`）→ `TransformGimmickObjectToEnemy` というマンホール変形ギミックが mag 専用として実装されており、normal_00005 で活用。

4. **HP スケール**: normal アウトポスト HP は 60,000〜70,000（他作品の dan 15,000 の約4倍）。dungeon では固定値 100（normal）/ 1,000（boss）を使用するため、この差異は関係なし。

5. **dungeon 用の challange スペック存在**: `c_mag_00001_challange_Boss_Yellow`（HP=100,000）、`c_mag_00201_challange_Boss_Green`（HP=100,000）が既に定義されており、dungeon boss にそのまま転用可能。

6. **背景アセット**: `mag_00004` が最頻出背景（normal 00001〜00003）。dungeon normal でもこれを採用するのが自然。
