# ダンダダン インゲームパターン分析

## 概要

- series_id: `dan`
- URキャラ: `chara_dan_00002` (Blue / Attack)、`chara_dan_00202` (Green / Attack)
- インゲームコンテンツ数: 43件（`%_dan_%` パターン全体）
- 調査日: 2026-03-01

---

## コンテンツ種別一覧

### normal（通常）

| ingame_id | BGM | 背景アセット | MstEnemyOutpost HP |
|-----------|-----|-------------|-------------------|
| normal_dan_00001 | SSE_SBG_003_001 | dan_00005 | 15,000 |
| normal_dan_00002 | SSE_SBG_003_001 | dan_00006 | 15,000 |
| normal_dan_00003 | SSE_SBG_003_001 | dan_00007 | 15,000 |
| normal_dan_00004 | SSE_SBG_003_001 | dan_00007 | 15,000 |
| normal_dan_00005 | SSE_SBG_003_001 | dan_00005 | 15,000 |
| normal_dan_00006 | SSE_SBG_003_001 | dan_00007 | 15,000 |

> BGM は全て `SSE_SBG_003_001`（作品固有BGM）で統一。

### hard（ハード）

| ingame_id | BGM | MstEnemyOutpost HP |
|-----------|-----|-------------------|
| hard_dan_00001 | SSE_SBG_003_001 | 50,000 |
| hard_dan_00002 | SSE_SBG_003_001 | 50,000 |
| hard_dan_00003 | SSE_SBG_003_001 | 50,000 |
| hard_dan_00004 | SSE_SBG_003_001 | 50,000 |
| hard_dan_00005 | SSE_SBG_003_001 | 50,000 |
| hard_dan_00006 | SSE_SBG_003_001 | 50,000 |

### veryhard（超ハード）

| ingame_id | BGM | MstEnemyOutpost HP |
|-----------|-----|-------------------|
| veryhard_dan_00001 | SSE_SBG_003_001 | 150,000 |
| veryhard_dan_00002 | SSE_SBG_003_001 | 100,000 |
| veryhard_dan_00003 | SSE_SBG_003_001 | 150,000 |
| veryhard_dan_00004 | SSE_SBG_003_001 | 150,000 |
| veryhard_dan_00005 | SSE_SBG_003_001 | 150,000 |
| veryhard_dan_00006 | SSE_SBG_003_001 | 150,000 |

### event（イベント）— event_dan1

| ingame_id | 種別 | BGM | boss_bgm | MstEnemyOutpost HP |
|-----------|------|-----|----------|-------------------|
| event_dan1_1day_00001 | 1日限定 | SSE_SBG_003_002 | なし | 500 |
| event_dan1_challenge01_00001〜00004 | チャレンジ | SSE_SBG_003_001 or 004 | SSE_SBG_003_004（一部） | 40,000〜80,000 |
| event_dan1_charaget01_00001〜00008 | キャラゲット | SSE_SBG_003_001 or 004 | SSE_SBG_003_004（一部） | 5,000〜50,000 |
| event_dan1_charaget02_00001〜00008 | キャラゲット2 | SSE_SBG_003_001 or 004 | SSE_SBG_003_004（一部） | 5,000〜50,000 |
| event_dan1_savage_00001〜00002 | 猛攻 | SSE_SBG_003_007 | なし | 100,000 |

### その他

| ingame_id | 種別 | MstEnemyOutpost HP |
|-----------|------|-------------------|
| pvp_dan_01 | PvP | pvp（共通） |
| raid_dan1_00001 | レイド | 1,000,000（ダメージ無効） |

---

## エネミーキャラクター一覧

danで使用されているエネミーキャラクターは以下の通り：

| mst_enemy_character_id | 種別 | 主な役割 | 使用頻度 |
|------------------------|------|---------|---------|
| enemy_dan_00001 | 雑魚 | Defense（防御タイプ） | 95回（最多） |
| enemy_dan_00101 | 雑魚 | Attack（攻撃タイプ） | 61回（2位） |
| chara_dan_00001 | ガチャキャラ | Defense | 19回 |
| chara_dan_00101 | ガチャキャラ | Attack | 7回 |
| enemy_dan_00201 | 雑魚（大型） | Attack | 3回（特殊場面のみ） |

> **主力2体体制**: `enemy_dan_00001`（防御型・赤）＋ `enemy_dan_00101`（攻撃型・赤）

---

## エネミー別パラメータ詳細

### enemy_dan_00001（主力・防御タイプ）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_dan_00001_general_n_Normal_Red | Normal | Defense | Red | 10,000 | 34 | 50 | 1 |
| e_dan_00001_general_h_Normal_Red | Normal | Defense | Red | 10,000 | 34 | 100 | 1 |
| e_dan_00001_general_h_Normal_Colorless | Normal | Defense | Colorless | 10,000 | 34 | 100 | 1 |
| e_dan_00001_general_vh_Boss_Red | Boss | Defense | Red | 10,000 | 34 | 100 | 1 |
| e_dan_00001_general_vh_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 34 | 100 | 1 |
| e_dan_00001_general_vh_Normal_Green | Normal | Attack | Green | 10,000 | 34 | 100 | 1 |

**変身形態（_trans_）**:

| id | unit_kind | role | color | HP | speed | attack |
|----|-----------|------|-------|----|-------|--------|
| e_dan_00001_general_n_trans_Normal_Red | Normal | Attack | Red | 1,000 | 34 | 50 |
| e_dan_00001_general_n_trans_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 34 | 50 |
| e_dan_00001_general_h_trans_Normal_Red | Normal | Attack | Red | 10,000 | 34 | 100 |
| e_dan_00001_general_h_trans_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 34 | 100 |

> `enemy_dan_00001` は変身（transform）ギミックを持つ。変身後は role が Defense → Attack に切り替わる。

### enemy_dan_00101（主力・攻撃タイプ）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| e_dan_00101_general_n_Normal_Red | Normal | Attack | Red | 10,000 | 47 | 50 | 1 |
| e_dan_00101_general_n_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 47 | 50 | 1 |
| e_dan_00101_general_h_Normal_Red | Normal | Attack | Red | 10,000 | 47 | 100 | 1 |
| e_dan_00101_general_h_Normal_Colorless | Normal | Attack | Colorless | 10,000 | 47 | 100 | 1 |
| e_dan_00101_general_vh_Normal_Red | Normal | Attack | Red | 15,000 | 47 | 150 | 1 |
| e_dan_00101_general_vh_Normal_Blue | Normal | Attack | Blue | 10,000 | 47 | 100 | 1 |
| e_dan_00101_general_vh_Normal_Green | Normal | Attack | Green | 10,000 | 47 | 100 | 1 |

> `enemy_dan_00101` は `enemy_dan_00001` より速い（speed 47 vs 34）。攻撃特化型。

### enemy_dan_00201（大型ボス型・特殊用途）

| id | unit_kind | role | color | HP | speed | attack |
|----|-----------|------|-------|----|-------|--------|
| e_dan_00201_general_n_Boss_Colorless | Boss | Attack | Colorless | 100,000 | 75 | 1,000 |
| e_dan_00201_general_n_Boss_Red | Boss | Attack | Red | 10,000 | 65 | 50 |

> 通常ステージ（normal）では特殊ギミックとして登場（HP係数 0.55 で実質HP 55,000相当）。

---

## シーケンスパターン分析

### normal_dan_00001（基本パターン）

| 要素 | condition_type | condition_value | action | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|--------------|---------|----------|
| 1 | DarknessKomaCleared | 2 | SummonEnemy | e_dan_00201_general_n_Boss_Colorless | 1 | 0.55 | 0.3 |
| 2 | ElapsedTime | 500 | SummonEnemy | e_glo_00001_general_n_Normal_Colorless | 5 | 9.5 | 8 |

- 暗コマ2個クリアで `enemy_dan_00201`（大型）が1体召喚（HP係数0.55）
- 500ms 後に汎用敵 `enemy_glo_00001` が5体大量召喚（HP係数9.5）

### normal_dan_00002（赤コンテンツ・変身あり）

| 要素 | condition_type | condition_value | action | action_value | count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|-------|---------|----------|
| 1(x3) | DarknessKomaCleared | 3 | SummonEnemy | e_dan_00001_general_n_Normal_Red | 1 | 0.55 | 5 |
| 2 | ElapsedTime | 500 | SummonEnemy | e_glo_00001_general_n_Normal_Red | 3 | 9.5 | 8 |
| 3 | ElapsedTimeSinceGroup | 50 | SummonEnemy | e_dan_00001_general_n_Normal_Red | 4 | 0.55 | 5 |
| 4 | ElapsedTimeSinceGroup | 500 | SummonEnemy | e_dan_00001_general_n_Normal_Red | 4 | 0.55 | 5 |
| 5 | ElapsedTimeSinceGroup | 1500 | SummonEnemy | e_dan_00001_general_n_Normal_Red | 4 | 0.55 | 5 |
| 6 | FriendUnitDead | 1 | SwitchSequenceGroup | group1 | - | - | - |

- `enemy_dan_00001` メインの赤コンテンツ
- 敵撃破でグループ切り替えによる新フェーズ移行あり

### normal_dan_00003（変身ギミック・enemy_dan_00101メイン）

| 要素 | condition_type | condition_value | action | action_value | count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|-------|---------|----------|
| 1 | ElapsedTime | 250 | SummonEnemy | e_dan_00001_general_n_trans_Normal_Colorless | 1 | 3 | 2 |
| 2 | ElapsedTimeSinceGroup | 100 | SummonEnemy | e_dan_00101_general_n_Normal_Colorless | 5 | 0.65 | 7 |
| 3 | ElapsedTimeSinceGroup | 150 | SummonEnemy | e_dan_00101_general_n_Normal_Colorless | 5 | 0.65 | 7 |
| 4 | ElapsedTimeSinceGroup | 2000 | SummonEnemy | e_dan_00101_general_n_Normal_Colorless | 5 | 0.65 | 7 |
| 5 | FriendUnitTransform | 1 | SwitchSequenceGroup | group1 | - | - | - |

- 変身ギミック（`_trans_`型）からスタート
- 変身後に `enemy_dan_00101` が大量召喚されるフェーズへ移行

### normal_dan_00004（chara混合・多フェーズ）

| 要素 | condition_type | condition_value | action | action_value | count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|-------|---------|----------|
| 1 | ElapsedTime | 400 | SummonEnemy | c_dan_00002_general_n_Boss_Red | 1 | 5.5 | 3 |
| 2 | ElapsedTime | 1000 | SummonEnemy | e_dan_00101_general_n_Normal_Colorless | 1 | 0.55 | 5 |
| 3 | ElapsedTime | 1700 | SummonEnemy | e_dan_00101_general_n_Normal_Colorless | 1 | 0.55 | 5 |
| 4 | ElapsedTime | 2050 | SummonEnemy | e_dan_00101_general_n_Normal_Colorless | 1 | 0.55 | 5 |
| 5 | FriendUnitDead | 2 | SummonEnemy | e_dan_00101_general_n_Normal_Colorless | 2 | 0.55 | 5 |
| 6 | FriendUnitDead | 3 | SummonEnemy | e_dan_00101_general_n_Normal_Colorless | 3 | 0.55 | 5 |
| 7 | ElapsedTime | 2000 | SummonEnemy | e_dan_00101_general_n_Normal_Colorless | 3 | 0.55 | 5 |

- `chara_dan_00002`（URキャラ）がボスとして初登場
- `enemy_dan_00101` がサポートとして順次出現

### normal_dan_00005（large ボス・汎用敵組み合わせ）

| 要素 | condition_type | condition_value | action | action_value | count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|-------|---------|----------|
| 1 | InitialSummon | 0 | SummonEnemy | e_dan_00201_general_n_Boss_Colorless | 1 | 0.45 | 2 |
| 2 | ElapsedTime | 1200 | SummonEnemy | e_glo_00001_general_n_Normal_Colorless | 2 | 5.5 | 5 |
| 3 | ElapsedTime | 3250 | SummonEnemy | e_glo_00001_general_n_Normal_Colorless | 5 | 5.5 | 5 |

- 開幕から `enemy_dan_00201`（大型）が1体先行召喚
- 時間経過で汎用敵が増援

### normal_dan_00006（最終面・キャラ混合複合）

| 要素 | condition_type | condition_value | action | action_value | count | hp_coef | atk_coef |
|------|----------------|-----------------|--------|--------------|-------|---------|----------|
| 1 | InitialSummon | 1 | SummonEnemy | e_dan_00001_general_n_trans_Normal_Red | 1 | 1.5 | 4 |
| 2 | ElapsedTime | 0 | SummonEnemy | c_dan_00101_general_n_Boss_Red | 1 | 4.5 | 4 |
| 3 | ElapsedTime | 750 | SummonEnemy | c_dan_00001_general_n_Normal_Red | 1 | 1.4 | 2.4 |
| 4 | ElapsedTime | 850 | SummonEnemy | e_dan_00201_general_n_Boss_Red | 1 | 4.5 | 7 |
| 5 | FriendUnitDead | 3 | SummonEnemy | c_dan_00002_general_n_Boss_Red | 1 | 5.5 | 5 |
| 6〜8 | FriendUnitTransform | 1 | SummonEnemy | e_dan_00101_general_n_Normal_Red | 3 | 0.65 | 5 |

- 最も複雑な構成：変身型 + chara複数 + 大型ボス
- 変身発動後に `enemy_dan_00101` が3体ずつ3回召喚される

---

## コンテンツ種別ごとの特徴比較

### アウトポスト HP スケール

| 種別 | HP |
|------|-----|
| normal | 15,000 |
| hard | 50,000 |
| veryhard | 100,000〜150,000 |
| event（charaget最終） | 50,000 |
| event（challenge最終） | 80,000 |
| event（savage） | 100,000 |
| raid | 1,000,000（ダメージ無効） |

### 雑魚敵の使われ方

| 難易度 | メイン雑魚 | HP係数（シーケンス内） | 攻撃係数 |
|--------|----------|----------------------|---------|
| normal | enemy_dan_00001 / enemy_dan_00101 | 0.55〜1.5 | 2〜8 |
| hard | enemy_dan_00001 / e_glo_00001 | 4.5〜21 | 4〜20 |
| veryhard | enemy_dan_00001 / enemy_dan_00101 / e_glo_00001 | 3〜75 | 3〜30 |

> 係数は MstEnemyStageParameter のベース値（HP=10,000、attack=50〜100）に乗算される。

### シーケンスの複雑さ

| 種別 | シーケンス行数 | 特徴 |
|------|--------------|------|
| normal_dan_00001 | 2 | 最シンプル |
| normal_dan_00002 | 8 | グループ切り替えあり |
| normal_dan_00003 | 5 | 変身ギミックでグループ移行 |
| normal_dan_00004 | 7 | 時間経過＋撃破トリガー混合 |
| normal_dan_00005 | 3 | InitialSummon + ElapsedTime |
| normal_dan_00006 | 8 | 最複合（変身＋chara複数＋transform後召喚） |

---

## コマライン構成（normal）

| ingame_id | 行数 | row1 構成 | row2 構成 | row3 構成 |
|-----------|------|-----------|-----------|-----------|
| normal_dan_00001 | 2 | koma1(0.25)+koma2(0.75) | koma1(1.0/Darkness) | - |
| normal_dan_00002 | 3 | koma1(0.6)+koma2(0.4/Darkness) | koma1(0.25/Darkness)+koma2(0.75/Darkness) | koma1(0.33)+koma2(0.34)+koma3(0.33) |
| normal_dan_00003 | 3 | koma1(0.6)+koma2(0.4) | koma1(0.25)+koma2(0.5)+koma3(0.25) | koma1(0.6)+koma2(0.4) |
| normal_dan_00004 | 3 | koma1(0.5)+koma2(0.5) | koma1(1.0) | koma1(0.33)+koma2(0.34)+koma3(0.33) |
| normal_dan_00005 | 2 | koma1(1.0) | koma1(0.6)+koma2(0.4) | - |
| normal_dan_00006 | 4 | koma1(0.4)+koma2(0.6) | koma1(0.25)+koma2(0.5)+koma3(0.25) | koma1(0.6)+koma2(0.4) / koma1(1.0) |

> dungeon（限界チャレンジ）の **normal ブロックは 3 行固定**、**boss ブロックは 1 行固定**。

---

## dungeon用 設計ポイント

### dungeon normalブロックの想定設計

```
インゲームID: dungeon_dan_normal_00001
BGM: SSE_SBG_003_001（作品BGM）
MstEnemyOutpost HP: 100（dungeon固定）
行数: 3行

使用する雑魚敵:
  - メイン: enemy_dan_00001（Defense / Red, HP=10,000, speed=34, attack=50）
  - サブ: enemy_dan_00101（Attack / Red or Colorless, HP=10,000, speed=47, attack=50）

シーケンス参考:
  - normal_dan_00003 パターン（enemy_dan_00101メイン + 変身ギミック）
  - normal_dan_00002 パターン（enemy_dan_00001メイン + グループ切り替え）
```

### dungeon bossブロックの想定設計

```
インゲームID: dungeon_dan_boss_00001
BGM: SSE_SBG_003_001（作品BGM）
MstEnemyOutpost HP: 1,000（dungeon固定）
行数: 1行

URキャラをボスとして配置:
  - chara_dan_00002（Blue / Attack）または chara_dan_00202（Green / Attack）
  - ベース HP: 50,000（normal難易度パラメータ参照）
```

---

## 背景・BGMアセット情報

| アセットキー | 説明 |
|-------------|------|
| dan_00005 | ダンダダン背景1（ループ背景） |
| dan_00006 | ダンダダン背景2（ループ背景） |
| dan_00007 | ダンダダン背景3（ループ背景） |
| SSE_SBG_003_001 | ダンダダン通常BGM |
| SSE_SBG_003_002 | ダンダダン1日限定BGM |
| SSE_SBG_003_004 | ダンダダンボスBGM（イベントチャレンジで使用） |
| SSE_SBG_003_007 | ダンダダンPvP/Savage BGM |

---

## まとめ・パターン特徴

1. **2体体制**: `enemy_dan_00001`（防御/赤）と `enemy_dan_00101`（攻撃/赤 or 無色）の組み合わせが基本。
2. **変身ギミック**: `enemy_dan_00001` は `_trans_` バリアントを持ち、変身後に role が Defense→Attack に切り替わる独特のギミックを保有。
3. **シーケンスの多様性**: normal 6本でも `DarknessKomaCleared`、`FriendUnitTransform`、`InitialSummon` など多彩な条件タイプを使用。
4. **HP スケール**: normal アウトポスト HP は 15,000 で固定、hard は 50,000（3.3倍）、veryhard は 150,000（10倍）。
5. **dungeon固定値**: dungeon normal は HP=100、dungeon boss は HP=1,000 が仕様（他コンテンツとは桁が異なる）。
6. **URキャラ（chara_dan_00002/00202）**: normal ステージでは `c_dan_00002_general_n_Boss_Red`（HP=10,000）、イベントでは `c_dan_00002_bbaget_Boss_Blue`（HP=50,000）として登場しており、dungeon boss では中間スペック相当（HP係数調整）が想定される。
