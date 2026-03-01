# 【推しの子】 インゲームパターン分析

## 概要

- series_id: `osh`
- URキャラ: `chara_osh_00001` (B小町不動のセンター アイ / Red / Technical)、`chara_osh_00101` (星野 ルビー / Colorless / Special)
- インゲームコンテンツ数: 25件（`%_osh_%` パターン全体）
- 調査日: 2026-03-01

---

## コンテンツ種別一覧

### normal（通常）

| ingame_id | BGM | 背景アセット | MstEnemyOutpost HP |
|-----------|-----|-------------|-------------------|
| normal_osh_00001 | SSE_SBG_003_003 | osh_00001 | 85,000 |
| normal_osh_00002 | SSE_SBG_003_002 | osh_00001 | 90,000 |
| normal_osh_00003 | SSE_SBG_003_003 | osh_00001 | 95,000 |

> BGM は `SSE_SBG_003_003`（作品メインBGM）と `SSE_SBG_003_002` が混在。背景アセットは全て `osh_00001` で統一。

### hard（ハード）

| ingame_id | BGM | MstEnemyOutpost HP |
|-----------|-----|-------------------|
| hard_osh_00001 | SSE_SBG_003_003 | 175,000 |
| hard_osh_00002 | SSE_SBG_003_002 | 170,000 |
| hard_osh_00003 | SSE_SBG_003_003 | 165,000 |

### veryhard（超ハード）

| ingame_id | BGM | MstEnemyOutpost HP |
|-----------|-----|-------------------|
| veryhard_osh_00001 | SSE_SBG_003_003 | 325,000 |
| veryhard_osh_00002 | SSE_SBG_003_003 | 250,000 |
| veryhard_osh_00003 | SSE_SBG_003_003 | 375,000 |

### event（イベント）— event_osh1

| ingame_id | 種別 | BGM | boss_bgm | MstEnemyOutpost HP |
|-----------|------|-----|----------|-------------------|
| event_osh1_1day_00001 | 1日限定 | SSE_SBG_003_002 | なし | 500 |
| event_osh1_challenge01_00001〜00004 | チャレンジ | SSE_SBG_003_001 | SSE_SBG_003_004 | 30,000〜80,000 |
| event_osh1_charaget01_00001〜00003 | キャラゲット1 | SSE_SBG_003_002 | SSE_SBG_003_006（一部） | 20,000〜25,000 |
| event_osh1_charaget02_00001〜00003 | キャラゲット2 | SSE_SBG_003_002 | SSE_SBG_003_006（最終のみ） | 20,000〜80,000 |
| event_osh1_savage_00001〜00003 | 猛攻 | SSE_SBG_003_009 | なし | 100,000〜180,000 |

### その他

| ingame_id | 種別 | MstEnemyOutpost HP |
|-----------|------|-------------------|
| pvp_osh_01 | PvP | pvp（共通） |
| raid_osh1_00001 | レイド | 1,000,000（ダメージ無効） |

---

## エネミーキャラクター一覧

oshで使用されているエネミーキャラクターは以下の通り：

| asset_key | 日本語名 | 種別 | 主な役割 |
|-----------|---------|------|---------|
| chara_osh_00001 | B小町不動のセンター アイ | URキャラ（FestivalUR） | Technical / ボス役 |
| chara_osh_00101 | 星野 ルビー | URキャラ | Colorless / Special |
| chara_osh_00201 | 星野 ルビー | ガチャキャラ | Attack |
| chara_osh_00301 | MEMちょ | ガチャキャラ | Support |
| chara_osh_00401 | 有馬 かな | ガチャキャラ | Technical |
| chara_osh_00501 | 黒川あかね | ガチャキャラ | Technical |
| chara_osh_00601 | ぴえヨン | ガチャキャラ | Attack |

> **oshには専用雑魚敵が存在しない。** normalコンテンツでは `enemy_glo_00001`（汎用敵1）と `enemy_glo_00002`（汎用敵2）を使用し、ガチャキャラ（`chara_osh_00001`）をボス役として登場させる構成。

---

## エネミー別パラメータ詳細

### enemy_glo_00001（汎用敵1 / osh normal 用バリアント）

| id | unit_kind | role | color | HP | speed | attack |
|----|-----------|------|-------|----|-------|--------|
| e_glo_00001_general_osh_n_Normal_Yellow | Normal | Attack | Yellow | 1,000 | 34 | 100 |
| e_glo_00001_general_osh_n_Normal_Green | Normal | Attack | Green | 1,000 | 34 | 100 |
| e_glo_00001_general_osh_n_Boss_Yellow | Boss | Attack | Yellow | 1,000 | 34 | 100 |

> `enemy_glo_00001` はoshi normalコンテンツでは Yellow / Green 2色展開。Boss バリアントも存在（シーケンスHP係数600倍で実質60万相当まで拡大可能）。

### enemy_glo_00002（汎用敵2 / osh normal 用バリアント）

| id | unit_kind | role | color | HP | speed | attack |
|----|-----------|------|-------|----|-------|--------|
| e_glo_00002_general_osh_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 47 | 100 |
| e_glo_00002_general_osh_n_Normal_Green | Normal | Defense | Green | 1,000 | 47 | 100 |

> `enemy_glo_00002` は speed 47 で `enemy_glo_00001`（speed 34）より高速。normal_osh_00002 にて緑色・無色として使用。

### chara_osh_00001（B小町不動のセンター アイ / URキャラ / normal難易度パラメータ）

| id | unit_kind | role | color | HP | speed | attack | combo |
|----|-----------|------|-------|----|-------|--------|-------|
| c_osh_00001_general_osh_n_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 31 | 100 | 5 |
| c_osh_00001_general_osh_n_Boss_Colorless | Boss | Technical | Colorless | 1,000 | 20 | 100 | 6 |
| c_osh_00001_general_osh_n_Boss_Green | Boss | Technical | Green | 1,000 | 31 | 100 | 6 |

> `chara_osh_00001` は normal コンテンツでは Colorless（無色）ボスとして登場することが多い。シーケンス内 HP 係数 70〜1000 が乗算されるため、実質 HP は 70,000〜1,000,000 相当。

---

## シーケンスパターン分析

### normal_osh_00001（グループ切り替えパターン・chara登場＋汎用大量召喚）

| 要素 | condition_type | condition_value | action_type | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|-------------|--------------|--------------|---------|----------|
| 1 | ElapsedTime | 200 | SummonEnemy | c_osh_00001_general_osh_n_Normal_Colorless | 1 | 70 | 10 |
| 2 | EnterTargetKomaIndex | 3 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 1 | 40 | 15 |
| 3 | FriendUnitDead | 2 | SummonEnemy | e_glo_00001_general_osh_n_Boss_Yellow | 1 | 600 | 25 |
| 3 | FriendUnitDead | 2 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 2 | 40 | 15 |
| 4 | OutpostHpPercentage | 99 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 20 | 40 | 15 |
| 5 | OutpostHpPercentage | 99 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 2 | 40 | 15 |
| groupchange_1 | FriendUnitDead | 3 | SwitchSequenceGroup | w1 | - | - | - |
| w1-6 | ElapsedTimeSinceSequenceGroupActivated | 50 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 3 | 40 | 15 |
| w1-7 | ElapsedTimeSinceSequenceGroupActivated | 2000 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 3 | 40 | 15 |
| w1-8 | ElapsedTimeSinceSequenceGroupActivated | 3000 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 2 | 40 | 15 |
| w1-9 | ElapsedTimeSinceSequenceGroupActivated | 500 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 3 | 40 | 15 |
| w1-10 | ElapsedTimeSinceSequenceGroupActivated | 1000 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 10 | 40 | 15 |

- 序盤に `chara_osh_00001`（Normal/Colorless, HP係数70倍）が1体登場
- コマインデックス進行で Yellow 汎用敵が増援
- 2体撃破後、Bossタイプ Yellow（HP係数600）が登場
- OutpostHP99%時点で20体大量召喚（実質初撃トリガー）
- 3体撃破でグループ切り替え → w1 グループで時間経過召喚フェーズへ移行

### normal_osh_00002（多キャラ混合・初期一括召喚パターン）

| 要素 | condition_type | condition_value | action_type | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|-------------|--------------|--------------|---------|----------|
| 1 | InitialSummon | 0 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Colorless | 1 | 20 | 7 |
| 2 | InitialSummon | 0 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Colorless | 1 | 20 | 7 |
| 3〜8 | InitialSummon | 0 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Green | 各1 | 65 | 3 |
| 9 | InitialSummon | 0 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Green | 1 | 65 | 3 |
| 10 | EnterTargetKomaIndex | 0 | SummonEnemy | c_osh_00001_general_osh_n_Boss_Colorless | 1 | 900 | 11 |
| 11 | FriendUnitDead | 1 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Colorless | 5 | 20 | 7 |
| 12 | FriendUnitDead | 2 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Colorless | 3 | 20 | 7 |
| 13 | FriendUnitDead | 3 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Green | 5 | 65 | 3 |
| 14 | FriendUnitDead | 4 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Green | 3 | 65 | 3 |
| 15 | FriendUnitDead | 5 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Green | 3 | 65 | 3 |
| 16 | FriendUnitDead | 6 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Green | 3 | 65 | 3 |
| 17 | FriendUnitDead | 7 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Colorless | 3 | 20 | 7 |
| 18 | FriendUnitDead | 8 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Colorless | 3 | 20 | 7 |
| 19 | FriendUnitDead | 9 | SummonEnemy | e_glo_00002_general_osh_n_Normal_Colorless | 3 | 20 | 7 |

- 開幕から Colorless 2体＋Green 7体の計9体が一斉召喚（InitialSummon）
- コマインデックス0到達で `c_osh_00001`（Boss/Colorless, HP係数900）が登場
- その後は撃破数カウントで段階的に Colorless と Green の増援が続く

### normal_osh_00003（chara中心・コマ進行＋撃破トリガーパターン）

| 要素 | condition_type | condition_value | action_type | action_value | summon_count | hp_coef | atk_coef |
|------|----------------|-----------------|-------------|--------------|--------------|---------|----------|
| 1 | ElapsedTime | 250 | SummonEnemy | c_osh_00001_general_osh_n_Boss_Green | 1 | 1000 | 10 |
| 2 | EnterTargetKomaIndex | 0 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 2 | 30 | 6 |
| 3 | EnterTargetKomaIndex | 1 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 3 | 30 | 6 |
| 4 | EnterTargetKomaIndex | 2 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 2 | 30 | 6 |
| 5 | EnterTargetKomaIndex | 3 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 3 | 30 | 6 |
| 6 | EnterTargetKomaIndex | 4 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 3 | 30 | 6 |
| 7 | EnterTargetKomaIndex | 5 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 5 | 30 | 6 |
| 8 | EnterTargetKomaIndex | 5 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 3 | 30 | 6 |
| 9 | FriendUnitDead | 1 | SummonEnemy | c_osh_00001_general_osh_n_Normal_Colorless | 1 | 100 | 10 |
| 10 | FriendUnitDead | 9 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 15 | 30 | 6 |
| 11〜14 | FriendUnitDead | 1 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 各2〜4 | 30 | 6 |
| 15 | FriendUnitDead | 9 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 15 | 30 | 6 |
| 16 | ElapsedTime | 4000 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Green | 3 | 30 | 6 |

- 250ms後に `c_osh_00001`（Boss/Green, HP係数1000）が先行登場（実質HP 100万相当）
- コマインデックス進行（0〜5）で Green 汎用敵が段階的に増援
- 1体撃破で `c_osh_00001`（Normal/Colorless, HP係数100）が追加登場
- 9体撃破で Green 汎用敵15体大量召喚（繰り返しトリガー）
- 4000ms 経過でも Green 増援あり

---

## コンテンツ種別ごとの特徴比較

### アウトポスト HP スケール

| 種別 | HP |
|------|-----|
| normal | 85,000〜95,000 |
| hard | 165,000〜175,000 |
| veryhard | 250,000〜375,000 |
| event（charaget最終） | 25,000〜80,000 |
| event（challenge最終） | 80,000 |
| event（savage最終） | 180,000 |
| raid | 1,000,000（ダメージ無効） |

> normal の HP は他作品（dan: 15,000、spy: 未確認）と比べて大幅に高い。これは実装時期が後のコンテンツであることを示している。

### 雑魚敵の使われ方

| 難易度 | メイン雑魚 | HP係数（シーケンス内） | 攻撃係数 |
|--------|----------|----------------------|---------|
| normal | e_glo_00001 / e_glo_00002 | 20〜1000（chara含む） | 3〜25 |

> **osh は専用雑魚敵を持たない。** 全コンテンツで汎用敵（`enemy_glo_00001` / `enemy_glo_00002`）を使用。ガチャキャラ（`chara_osh_00001`）をHP係数を乗算してボスとして機能させる設計。

### シーケンスの複雑さ

| 種別 | シーケンス行数 | 特徴 |
|------|--------------|------|
| normal_osh_00001 | 12 | グループ切り替え（w1）＋OutpostHp%トリガー |
| normal_osh_00002 | 19 | InitialSummon 9体一斉 ＋ 撃破数トリガー多段 |
| normal_osh_00003 | 16 | ElapsedTime＋コマ進行＋撃破トリガー複合 |

---

## コマライン構成（normal）

| ingame_id | 行数 | row1 構成 | row2 構成 | row3 構成 | row4 構成 |
|-----------|------|-----------|-----------|-----------|-----------|
| normal_osh_00001 | 4 | koma1(0.5)+koma2(0.5) | koma1(0.33)+koma2(0.34)+koma3(0.33) | koma1(0.5)+koma2(0.5) | koma1(0.25)+koma2(0.25)+koma3(0.25)+koma4(0.25) |
| normal_osh_00002 | 3 | koma1(0.33)+koma2(0.34)+koma3(0.33) | koma1(1.0) | koma1(0.25)+koma2(0.25)+koma3(0.5) |
| normal_osh_00003 | 4 | koma1(0.5)+koma2(0.5) | koma1(0.33)+koma2(0.34)+koma3(0.33) | koma1(1.0) | koma1(0.25)+koma2(0.25)+koma3(0.5) |

> dungeon（限界チャレンジ）の **normal ブロックは 3 行固定**、**boss ブロックは 1 行固定**。

---

## dungeon用 設計ポイント

### dungeon normalブロックの想定設計

```
インゲームID: dungeon_osh_normal_00001
BGM: SSE_SBG_003_003（作品メインBGM）
MstEnemyOutpost HP: 100（dungeon固定）
行数: 3行

使用する雑魚敵:
  - メイン: enemy_glo_00001（Attack / Yellow, HP=1,000, speed=34, attack=100）
  - サブ: enemy_glo_00002（Attack / Colorless or Green, HP=1,000, speed=47, attack=100）

シーケンス参考:
  - normal_osh_00001 パターン（e_glo_00001メイン＋グループ切り替え）
  - normal_osh_00002 パターン（InitialSummon＋撃破トリガー）
  ※ oshには専用雑魚敵がないため汎用敵のみでシーケンスを構成
```

### dungeon bossブロックの想定設計

```
インゲームID: dungeon_osh_boss_00001
BGM: SSE_SBG_003_003（作品メインBGM）
MstEnemyOutpost HP: 1,000（dungeon固定）
行数: 1行

URキャラをボスとして配置:
  - chara_osh_00001（B小町不動のセンター アイ / Red / Technical）
    → 使用パラメータ候補: c_osh_00001_general_osh_n_Boss_Colorless（HP=1,000, speed=20, attack=100）
      またはイベント用: c_osh_00001_challenge_Boss_Red（HP=50,000, speed=32, attack=300）
  - chara_osh_00101（星野 ルビー / Colorless / Special）は現時点でパラメータ未確認
    → 代替案: c_osh_00201_osh1_advent_Boss_Red（HP=10,000, speed=31, attack=100）を参考に設計
```

---

## 背景・BGMアセット情報

| アセットキー | 説明 |
|-------------|------|
| osh_00001 | 【推しの子】背景（ループ背景、全normalで共通） |
| SSE_SBG_003_001 | 【推しの子】通常BGM（eventチャレンジ） |
| SSE_SBG_003_002 | 【推しの子】サブBGM（normal_00002、event系） |
| SSE_SBG_003_003 | 【推しの子】メインBGM（normal_00001/00003、hard、veryhard） |
| SSE_SBG_003_004 | 【推しの子】チャレンジBoss BGM |
| SSE_SBG_003_006 | 【推しの子】キャラゲットBoss BGM / レイドBGM |
| SSE_SBG_003_007 | 【推しの子】PvP BGM |
| SSE_SBG_003_009 | 【推しの子】猛攻BGM |

---

## まとめ・パターン特徴

1. **専用雑魚敵なし**: osh は `enemy_glo_00001`（Yellow/Green）と `enemy_glo_00002`（Colorless/Green）の汎用敵のみを使用。dungeon設計でも同様に汎用敵2種を組み合わせる構成が適切。

2. **chara登場パターン**: `chara_osh_00001`（B小町不動のセンター アイ）が normal コンテンツのボス役として頻繁に登場。Boss/Colorless（HP係数900）やBoss/Green（HP係数1000）として使われ、実質ボスとして機能。

3. **シーケンスの多様性**: `OutpostHpPercentage`（アウトポストHP%）、`EnterTargetKomaIndex`（コマ進行）、`InitialSummon`（初期召喚）、`FriendUnitDead`（撃破数）など多様なトリガーを組み合わせている。

4. **HP スケール**: normal アウトポスト HP は 85,000〜95,000 と、danの15,000と比べて格段に高い。これはコンテンツ実装時期の違いを反映。dungeon では固定値 100（normal）/ 1,000（boss）を使用する。

5. **2色展開**: normalコンテンツは主にYellow+Colorlessの組み合わせ（00001）、またはColorless+Green（00002）、Green中心（00003）と、コンテンツごとに異なる色の敵を配置。

6. **URキャラ注意点**: `chara_osh_00001`（アイ / Red / Technical）は既存パラメータあり。`chara_osh_00101`（ルビー / Colorless / Special）はFestivalURではなく `chara_osh_00201`（星野ルビー / Attack）とは別キャラのため、dungeon boss に使用する場合はパラメータIDの確認が必要。
