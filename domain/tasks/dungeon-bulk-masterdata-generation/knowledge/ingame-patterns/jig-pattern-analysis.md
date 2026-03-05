# 地獄楽 インゲームパターン分析

## 概要

- series_id: `jig`
- URキャラ: `chara_jig_00001` (Red / Technical)、`chara_jig_00401` (Colorless / Technical)
- インゲームコンテンツ数: 41件（`%_jig_%` パターン全体）
- 調査日: 2026-03-01

---

## コンテンツ種別一覧

### normal（通常）

| ingame_id | BGM | 背景アセット | MstEnemyOutpost HP |
|-----------|-----|-------------|-------------------|
| normal_jig_00001 | SSE_SBG_003_003 | jig_00003 | 15,000 |
| normal_jig_00002 | SSE_SBG_003_003 | jig_00002 | 15,000 |
| normal_jig_00003 | SSE_SBG_003_003 | jig_00002 | 15,000 |
| normal_jig_00004 | SSE_SBG_003_003 | jig_00002 | 20,000 |
| normal_jig_00005 | SSE_SBG_003_003 | jig_00003 | 20,000 |
| normal_jig_00006 | SSE_SBG_003_003 | jig_00003 | 20,000 |

> BGM は全て `SSE_SBG_003_003`（作品固有BGM）で統一。背景は `jig_00002` と `jig_00003` の2種類。

### hard（ハード）

| ingame_id | BGM | MstEnemyOutpost HP |
|-----------|-----|-------------------|
| hard_jig_00001 | SSE_SBG_003_003 | 40,000 |
| hard_jig_00002 | SSE_SBG_003_003 | 40,000 |
| hard_jig_00003 | SSE_SBG_003_003 | 40,000 |
| hard_jig_00004 | SSE_SBG_003_003 | 50,000 |
| hard_jig_00005 | SSE_SBG_003_003 | 50,000 |
| hard_jig_00006 | SSE_SBG_003_003 | 50,000 |

### veryhard（超ハード）

| ingame_id | BGM | MstEnemyOutpost HP |
|-----------|-----|-------------------|
| veryhard_jig_00001 | SSE_SBG_003_003 | 130,000 |
| veryhard_jig_00002 | SSE_SBG_003_003 | 130,000 |
| veryhard_jig_00003 | SSE_SBG_003_003 | 130,000 |
| veryhard_jig_00004 | SSE_SBG_003_003 | 130,000 |
| veryhard_jig_00005 | SSE_SBG_003_003 | 130,000 |
| veryhard_jig_00006 | SSE_SBG_003_003 | 130,000 |

### event（イベント）— event_jig1

| ingame_id | 種別 | BGM | boss_bgm | MstEnemyOutpost HP |
|-----------|------|-----|----------|-------------------|
| event_jig1_1day_00001 | 1日限定 | SSE_SBG_003_001 | なし | 500 |
| event_jig1_challenge01_00001〜00004 | チャレンジ | SSE_SBG_003_003 | SSE_SBG_003_004 | 30,000〜80,000 |
| event_jig1_charaget01_00001〜00006 | キャラゲット1 | SSE_SBG_003_003 | なし | 30,000〜50,000 |
| event_jig1_charaget02_00001〜00006 | キャラゲット2 | SSE_SBG_003_001 | なし | 30,000〜60,000 |
| event_jig1_savage_00001〜00003 | 猛攻 | SSE_SBG_003_009 | なし | 100,000〜200,000 |

### その他

| ingame_id | 種別 | MstEnemyOutpost HP |
|-----------|------|-------------------|
| pvp_jig_01 / pvp_jig_02 | PvP | pvp（共通） |
| raid_jig1_00001 | レイド | 1,000,000 |

---

## エネミーキャラクター一覧

jigで使用されているエネミーキャラクターは以下の通り（日本語名付き）：

| mst_enemy_character_id | 日本語名 | 種別 | 主な役割 | 備考 |
|------------------------|---------|------|---------|------|
| enemy_jig_00001 | 門神 | 雑魚 | Defense | normal mainquestで主力雑魚 |
| enemy_jig_00201 | 門神 (大) | 雑魚（大型） | Defense | Boss unit_kind。門神の大型版 |
| enemy_jig_00301 | 竈神 魚 | 雑魚 | Attack | normal mainquestでボス役登場 |
| enemy_jig_00401 | 極楽蝶 | 雑魚 | Attack/Technical | 高速タイプ（speed 70）。大量召喚型 |
| enemy_jig_00402 | 極楽蝶 | 雑魚 | Attack/Defense | 極楽蝶の別バリアント |
| enemy_jig_00501 | 山田浅ェ門 源嗣 | 大型ボス | Defense | challenge専用ボス役 |
| enemy_jig_00601 | 朱槿 | 大型ボス | Technical/Attack | advent・charaget専用 |
| chara_jig_00001 | がらんの画眉丸 | ガチャキャラ | Technical | URキャラ（Red）。normal/eventでボス役 |
| chara_jig_00101 | 山田浅ェ門 佐切 | ガチャキャラ | Attack | normalでボス役 |
| chara_jig_00201 | 杠 | ガチャキャラ | Technical | eventでボス役 |
| chara_jig_00301 | 山田浅ェ門 仙汰 | ガチャキャラ | Defense | eventでボス役 |
| chara_jig_00401 | 賊王 亜左 弔兵衛 | ガチャキャラ | Technical | URキャラ（Colorless）。eventでボス役 |
| chara_jig_00501 | 山田浅ェ門 桐馬 | ガチャキャラ | （不明） | eventでボス役 |
| chara_jig_00601 | 民谷 巌鉄斎 | ガチャキャラ | （不明） | event専用 |

> **主力体制**: `enemy_jig_00001`（門神 / 防御型）＋ `enemy_jig_00401`（極楽蝶 / 大量召喚型）が normal のメイン雑魚。
> `enemy_jig_00201`（門神 (大)）はボスユニットとして中型ボス役でも登場。

---

## エネミー別パラメータ詳細

### enemy_jig_00001（門神 / 主力雑魚・防御タイプ）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_jig_00001_mainquest_Normal_Colorless | Normal | Defense | Colorless | 3,500 | 31 | 50 | 1 |
| e_jig_00001_mainquest_Normal_Green | Normal | Defense | Green | 10,000 | 27 | 50 | 1 |
| e_jig_00001_jig1_challenge_Normal_Red | Normal | Defense | Red | 10,000 | 27 | 100 | 1 |
| e_jig_00001_jig1_challenge_Normal_Colorless | Normal | Defense | Colorless | 10,000 | 27 | 100 | 1 |
| e_jig_00001_jig1_charaget01_Normal_Colorless | Normal | Defense | Colorless | 1,000 | 27 | 100 | 1 |

> `enemy_jig_00001` は防御タイプ（Defense）。速度が遅め（speed 27〜31）で壁役。mainquest では HP 3,500〜10,000。

### enemy_jig_00201（門神 (大) / 大型ボス・防御タイプ）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_jig_00201_mainquest_Boss_Green | Boss | Defense | Green | 10,000 | 20 | 50 | 1 |
| e_jig_00201_mainquest_Normal_Green | Normal | Defense | Green | 10,000 | 20 | 50 | 1 |

> `enemy_jig_00201` は speed 20 と最低速で、大型の防御ボス。HP は 10,000 だがシーケンスの hp_coef で実質強化。

### enemy_jig_00301（竈神 魚 / 攻撃タイプ・ボス役）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_jig_00301_mainquest_Normal_Colorless | Normal | Attack | Colorless | 5,000 | 30 | 100 | 1 |
| e_jig_00301_mainquest_Normal_Green | Normal | Attack | Green | 5,000 | 35 | 100 | 1 |
| e_jig_00301_mainquest_Boss_Colorless | Boss | Attack | Colorless | 5,000 | 32 | 100 | 1 |
| e_jig_00301_mainquest_Boss_Green | Boss | Attack | Green | 5,000 | 37 | 100 | 1 |

> `enemy_jig_00301` は攻撃タイプ（Attack）でボス unit_kind も持つ汎用ボス型雑魚。normal ステージでは高 hp_coef で実質ボス役。

### enemy_jig_00401（極楽蝶 / 大量召喚タイプ）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_jig_00401_mainquest_Normal_Colorless | Normal | Attack | Colorless | 3,000 | 32 | 100 | 1 |
| e_jig_00401_mainquest_Normal_Green | Normal | Technical | Green | 3,000 | 25 | 100 | 1 |
| e_jig_00401_jig1_advent_Normal_Colorless | Normal | Technical | Colorless | 1,000 | 70 | 400 | 1 |
| e_jig_00401_jig1_advent_Normal_Yellow | Normal | Technical | Yellow | 1,000 | 70 | 400 | 1 |
| e_jig_00401_jig1_challenge01_Normal_Red | Normal | Attack | Red | 10,000 | 33 | 100 | 1 |
| e_jig_00401_jig1_challenge_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 33 | 100 | 1 |

> `enemy_jig_00401` は大量召喚（10〜30体）に使われる小型雑魚。advent バリアントは speed 70・attack 400 の超高速タイプ。

### enemy_jig_00402（極楽蝶 別バリアント）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_jig_00402_mainquest_Normal_Colorless | Normal | Attack | Colorless | 3,000 | 33 | 100 | 1 |
| e_jig_00402_jig1_charaget01_Normal_Colorless | Normal | Defense | Colorless | 1,000 | 25 | 100 | 1 |
| e_jig_00402_jig1_charaget02_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 37 | 100 | 5 |

> `enemy_jig_00402` は `enemy_jig_00401` の別バリアント。combo 5 のものも存在し、連続攻撃型。

### chara_jig_00001（がらんの画眉丸 / URキャラ・Technical Red）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| c_jig_00001_mainquest_Boss_Red | Boss | Technical | Red | 5,000 | 41 | 100 | 5 |
| c_jig_00001_jig1_challenge_Boss_Red | Boss | Technical | Red | 50,000 | 41 | 100 | 4 |

> mainquest での HP は 5,000。event challenge では 50,000。dungeon boss 用には hp_coef で調整する。

### chara_jig_00401（賊王 亜左 弔兵衛 / URキャラ・Technical Colorless）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| c_jig_00401_jig1_challenge_Boss_Colorless | Boss | Technical | Colorless | 50,000 | 30 | 100 | 4 |
| c_jig_00401_jig1_charaget02_Boss_Red | Boss | Technical | Red | 10,000 | 30 | 100 | 6 |

> `chara_jig_00401` の mainquest 専用パラメータは未登録。challenge での HP は 50,000（speed 30、combo 4）。

---

## シーケンスパターン分析

### normal_jig_00001（基本パターン・URキャラ chara_jig_00101 ボス）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | ElapsedTime | 500 | SummonEnemy | c_jig_00101_mainquest_Boss_Green | 1 | 14 | 2 |

- 500ms 後に `chara_jig_00101`（山田浅ェ門 佐切）が1体召喚（hp_coef 14 = 実質 HP 70,000）
- シンプルな1ステップ構成

### normal_jig_00002（門神 (大) + 極楽蝶・多数）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | ElapsedTime | 1,100 | SummonEnemy | e_jig_00201_mainquest_Boss_Green | 1 | 10 | 5 |
| 2 | ElapsedTime | 100 | SummonEnemy | e_jig_00402_mainquest_Normal_Colorless | 10 | 1.7 | 2 |
| 3 | FriendUnitDead | 1 | SummonEnemy | e_jig_00402_mainquest_Normal_Colorless | 1 | 1.7 | 2 |

- `enemy_jig_00402`（極楽蝶）10体と `enemy_jig_00201`（門神 (大)）1体の組み合わせ
- 敵撃破のたびに極楽蝶が1体補充される

### normal_jig_00003（門神 + 極楽蝶・波状攻撃）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | ElapsedTime | 100 | SummonEnemy | e_jig_00001_mainquest_Normal_Colorless | 20 | 4 | 3 |
| 2 | ElapsedTime | 250 | SummonEnemy | e_jig_00401_mainquest_Normal_Green | 20 | 1.7 | 2.5 |

- `enemy_jig_00001`（門神）20体 + `enemy_jig_00401`（極楽蝶）20体の大量召喚パターン
- 2種雑魚同時大量召喚

### normal_jig_00004（門神 + 極楽蝶 + 竈神魚ボス）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | ElapsedTime | 250 | SummonEnemy | e_jig_00401_mainquest_Normal_Colorless | 30 | 1.7 | 2 |
| 2 | ElapsedTime | 300 | SummonEnemy | e_jig_00001_mainquest_Normal_Colorless | 1 | 4 | 3 |
| 3 | ElapsedTime | 650 | SummonEnemy | e_jig_00001_mainquest_Normal_Colorless | 30 | 4 | 3 |
| 4 | FriendUnitDead | 2 | SummonEnemy | e_jig_00301_mainquest_Boss_Green | 1 | 16 | 4 |

- 極楽蝶30体 → 門神1体 → 門神30体の段階的大量召喚
- 敵2体撃破後に竈神 魚が1体（hp_coef 16 = 実質 HP 80,000）ボス登場

### normal_jig_00005（全種雑魚 + 大型ボス・最大規模）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | FriendUnitDead | 5 | SummonEnemy | e_jig_00401_mainquest_Normal_Green | 30 | 1.7 | 2 |
| 2 | FriendUnitDead | 5 | SummonEnemy | e_jig_00001_mainquest_Normal_Colorless | 30 | 4 | 3 |
| 3 | FriendUnitDead | 5 | SummonEnemy | e_jig_00402_mainquest_Normal_Colorless | 30 | 1.7 | 2.5 |
| 4 | FriendUnitDead | 5 | SummonEnemy | e_jig_00301_mainquest_Boss_Colorless | 1 | 16 | 12 |
| 5 | ElapsedTime | 150 | SummonEnemy | e_jig_00201_mainquest_Boss_Green | 1 | 10 | 5 |

- 5体撃破でまとめて多種大量召喚（極楽蝶・門神・極楽蝶(別)・竈神魚ボス）
- 開幕150ms で門神 (大) が1体先行召喚

### normal_jig_00006（charaボス混合・最終面）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | ElapsedTime | 250 | SummonEnemy | e_jig_00401_mainquest_Normal_Colorless | 1 | 1.7 | 2.5 |
| 2 | ElapsedTime | 400 | SummonEnemy | e_jig_00402_mainquest_Normal_Colorless | 30 | 1.7 | 2.5 |
| 3 | FriendUnitDead | 1 | SummonEnemy | c_jig_00101_mainquest_Normal_Green | 1 | 4.8 | 1.8 |
| 4 | FriendUnitDead | 1 | SummonEnemy | c_jig_00001_mainquest_Boss_Red | 1 | 11 | 4.5 |

- 極楽蝶の小出し → 大量召喚 → 敵撃破後 chara（佐切・画眉丸）が登場するフェーズ移行型

---

## コンテンツ種別ごとの特徴比較

### アウトポスト HP スケール

| 種別 | HP |
|------|-----|
| normal（前半 00001〜00003） | 15,000 |
| normal（後半 00004〜00006） | 20,000 |
| hard（前半 00001〜00003） | 40,000 |
| hard（後半 00004〜00006） | 50,000 |
| veryhard（全6本） | 130,000 |
| event（charaget最終） | 50,000〜60,000 |
| event（challenge最終） | 80,000 |
| event（savage最終） | 200,000 |
| raid | 1,000,000 |

> **jig の特徴**: normal で 2 段階 HP 設計（15,000 → 20,000）、veryhard が 130,000 で全本統一。

### 雑魚敵の使われ方

| 難易度 | メイン雑魚 | 召喚数 | HP係数（シーケンス） | atk係数 |
|--------|----------|-------|---------------------|---------|
| normal | enemy_jig_00001 / enemy_jig_00401 / enemy_jig_00402 | 最大30体 | 1.7〜16 | 2〜12 |
| normal | enemy_jig_00201（大型） | 1体 | 10 | 5 |
| normal | enemy_jig_00301（竈神魚ボス） | 1体 | 16 | 4〜12 |

### シーケンスの複雑さ

| 種別 | シーケンス行数 | 特徴 |
|------|--------------|------|
| normal_jig_00001 | 1 | 最シンプル（ボス1体のみ） |
| normal_jig_00002 | 3 | 大量雑魚 + 撃破補充 |
| normal_jig_00003 | 2 | 2種雑魚同時大量召喚 |
| normal_jig_00004 | 4 | 段階召喚 + 撃破後ボス登場 |
| normal_jig_00005 | 5 | 全種類 + 撃破トリガー同時召喚 |
| normal_jig_00006 | 4 | 大量雑魚 + chara混合フェーズ移行 |

---

## コマライン構成（normal）

| ingame_id | 行数 | row1 構成 | row2 構成 | row3 構成 |
|-----------|------|-----------|-----------|-----------|
| normal_jig_00001 | 2 | jig_00003: koma1(0.25) + koma2(0.75) | jig_00003: koma1(0.6) + koma2(0.4) | - |
| normal_jig_00002 | 2 | jig_00002: koma1(0.6) + koma2(0.4) | jig_00002: koma1(1.0) | - |
| normal_jig_00003 | 3 | jig_00002: koma1(0.25) + koma2(0.25) + koma3(0.5) | jig_00002: koma1(0.6) + koma2(0.4) | jig_00002: koma1(0.4) + koma2(0.2) + koma3(0.4) |
| normal_jig_00004 | 3 | jig_00002: koma1(0.5) + koma2(0.25) + koma3(0.25) | jig_00002: koma1(0.5) + koma2(0.5) | jig_00002: koma1(0.4) + koma2(0.6) |
| normal_jig_00005 | 2 | jig_00003: koma1(0.75) + koma2(0.25) | jig_00003: koma1(1.0) | - |
| normal_jig_00006 | 3 | jig_00003: koma1(0.4) + koma2(0.6) | jig_00003: koma1(0.25) + koma2(0.5) + koma3(0.25) | jig_00003: koma1(0.75) + koma2(0.25) |

> dungeon（限界チャレンジ）の **normal ブロックは 3 行固定**、**boss ブロックは 1 行固定**。

---

## dungeon設計向けの推奨パラメータ

### dungeon normalブロックの想定設計

```
インゲームID: dungeon_jig_normal_00001
BGM: SSE_SBG_003_003（地獄楽作品BGM）
MstEnemyOutpost HP: 100（dungeon固定）
行数: 3行
背景アセット: jig_00002 または jig_00003

使用する雑魚敵:
  - メイン: enemy_jig_00001（門神 / Defense / Colorless or Green, HP=3,500〜10,000, speed=27〜31, attack=50）
  - サブ: enemy_jig_00401（極楽蝶 / Attack, HP=3,000, speed=32, attack=100）

シーケンス参考:
  - normal_jig_00003 パターン（2種雑魚同時大量召喚: 門神20体 + 極楽蝶20体）
  - normal_jig_00002 パターン（極楽蝶10体 + 門神(大)1体ボス構成）

MstEnemyStageParameter推奨:
  - enemy_jig_00001: e_jig_00001_mainquest_Normal_Colorless（HP=3,500, speed=31, attack=50）を流用
  - enemy_jig_00401: e_jig_00401_mainquest_Normal_Colorless（HP=3,000, speed=32, attack=100）を流用
```

### dungeon bossブロックの想定設計

```
インゲームID: dungeon_jig_boss_00001
BGM: SSE_SBG_003_003（地獄楽作品BGM）
MstEnemyOutpost HP: 1,000（dungeon固定）
行数: 1行

URキャラをボスとして配置（いずれか1体を選択）:
  - chara_jig_00001（がらんの画眉丸 / Red / Technical）
    参考: c_jig_00001_mainquest_Boss_Red（HP=5,000, speed=41, combo=5）
    dungeon 用 hp_coef 調整（5〜10倍程度が妥当）
  - chara_jig_00401（賊王 亜左 弔兵衛 / Colorless / Technical）
    参考: c_jig_00401_jig1_challenge_Boss_Colorless（HP=50,000, speed=30, combo=4）
    dungeon 用 hp_coef 調整（0.5〜2倍程度が妥当）

URキャラ2体の使い分け:
  - chara_jig_00001（Red / Technical）: speed 41 の高速ボス
  - chara_jig_00401（Colorless / Technical）: speed 30 のやや遅いボス。combo 4〜6
  → 異なるボスブロックにそれぞれ配置することを推奨
```

---

## 背景・BGMアセット情報

| アセットキー | 説明 |
|-------------|------|
| jig_00002 | 地獄楽背景2（通常ステージ後半・hard/veryhard） |
| jig_00003 | 地獄楽背景3（通常ステージ前半） |
| SSE_SBG_003_003 | 地獄楽通常BGM（normal/hard/veryhard/challenge） |
| SSE_SBG_003_001 | 地獄楽BGM2（1day/charaget02） |
| SSE_SBG_003_004 | 地獄楽ボスBGM（challenge の boss_bgm） |
| SSE_SBG_003_007 | 地獄楽PvP BGM |
| SSE_SBG_003_008 | 地獄楽レイドBGM |
| SSE_SBG_003_009 | 地獄楽猛攻BGM |

---

## まとめ・パターン特徴

1. **2種体制**: `enemy_jig_00001`（門神 / 防御型・低速）と `enemy_jig_00401`（極楽蝶 / 攻撃型・高速）が normal の主力雑魚2種。`enemy_jig_00402` も極楽蝶の別バリアントとして補完。

2. **大量召喚型**: normal ステージは 10〜30体の大量召喚が基本。danと同様に多数の雑魚で構成されるが、jig は `FriendUnitDead` トリガー（撃破連動型）も多用する。

3. **門神 (大) ミニボス**: `enemy_jig_00201`（門神 (大)）はボスユニットとして登場し、hp_coef 10〜16 で実質 HP 100,000〜160,000 相当の強敵として機能する。

4. **竈神魚ボス役**: `enemy_jig_00301`（竈神 魚）が hp_coef 16 のボスとして登場するパターンあり（実質 HP 80,000 相当）。Attack タイプで攻撃的なボス役。

5. **HP スケール**: normal アウトポスト HP は前半 15,000・後半 20,000 の2段階設計。veryhard は 130,000 で全本統一（danの 100,000〜150,000 よりやや高め）。

6. **URキャラ2体の使い分け**: `chara_jig_00001`（Red / Technical / speed 41）と `chara_jig_00401`（Colorless / Technical / speed 30）はどちらも Technical ロール。dungeon bossブロックでは2体それぞれを別ブロックに配置することが想定される。

7. **dungeon固定値**: dungeon normal は HP=100、dungeon boss は HP=1,000 が仕様（他コンテンツとは桁が異なる）。

8. **BGM**: 通常BGM `SSE_SBG_003_003` が normal/hard/veryhard で統一使用。dungeon にも同じBGMを引き継ぐことが自然。
