# タコピーの原罪 インゲームパターン分析

## 概要

- series_id: tak
- URキャラ: chara_tak_00001 (Yellow / Defense) - 「ハッピー星からの使者 タコピー」
- インゲームコンテンツ数: 9件（normal x3, hard x3, veryhard x3）
- dungeonコンテンツ: 現時点では存在しない（未生成）
- 背景BGM: SSE_SBG_003_002（全コンテンツ共通）

---

## コンテンツ種別一覧

### normal（通常）

| ingame_id | loop_background_asset_key | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef |
|-----------|---------------------------|------------|----------------------|--------------------------|
| normal_tak_00001 | glo_00004 | 30,000 | 1.0 | 1.0 |
| normal_tak_00002 | glo_00004 | 30,000 | 1.0 | 1.0 |
| normal_tak_00003 | glo_00004 | 30,000 | 1.0 | 1.0 |

> OutpostのartworkアセットはMstEnemyOutpostに記録: `tak_0001`（normal共通）

### hard（難）

| ingame_id | loop_background_asset_key | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef |
|-----------|---------------------------|------------|----------------------|--------------------------|
| hard_tak_00001 | （空欄） | 80,000 | 1.0 | 1.0 |
| hard_tak_00002 | （空欄） | 80,000 | 1.0 | 1.0 |
| hard_tak_00003 | （空欄） | 80,000 | 1.0 | 1.0 |

> OutpostのartworkアセットはMstEnemyOutpostに記録: `tak_0002`（hard共通）
> loop_background_asset_keyは空欄（hardでは背景なし or デフォルト背景を使用）

### veryhard（超難）

| ingame_id | loop_background_asset_key | outpost_hp | normal_enemy_hp_coef | normal_enemy_attack_coef |
|-----------|---------------------------|------------|----------------------|--------------------------|
| veryhard_tak_00001 | （空欄） | 150,000 | 1.0 | 1.0 |
| veryhard_tak_00002 | （空欄） | 150,000 | 1.0 | 1.0 |
| veryhard_tak_00003 | （空欄） | 150,000 | 1.0 | 1.0 |

> OutpostのartworkアセットはMstEnemyOutpostに記録: `tak_0003`（veryhard共通）

> **注意**: 全コンテンツで `normal_enemy_hp_coef / attack_coef / speed_coef = 1.0` かつ `boss_enemy_hp_coef / attack_coef / speed_coef = 1.0`
> 実際の強さはシーケンス内のenemy_hp_coef / enemy_attack_coefで個別に設定されている（後述）

### MstKomaLine 背景アセット（コマアセット）

全コンテンツ（normal/hard/veryhard）でコマ背景アセットは `glo_00004` を共通使用。
loop_background_asset_key（MstInGame）はnormalのみ `glo_00004` を設定、hard/veryhardは空欄。

---

## エネミーID → 日本語名対応表

### キャラエネミー（chara_tak系）

| asset_key | 日本語名 |
|-----------|---------|
| chara_tak_00001 | ハッピー星からの使者 タコピー（URキャラ） |

### GLO汎用エネミー（全コンテンツで使用）

| asset_key | 日本語名 |
|-----------|---------|
| enemy_glo_00001 | GLO汎用エネミー1 |

> takシリーズには専用雑魚敵（enemy_tak_XXXXX）が存在しない。
> 全コンテンツでGLO汎用エネミー（enemy_glo_00001）を雑魚として使用している。

---

## エネミー別パラメータ詳細

### キャラエネミー（chara_tak_00001 系）

| id | mst_enemy_character_id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|------------------------|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| c_tak_00001_mainquest_Boss_Yellow | chara_tak_00001（タコピー） | Boss | Defense | Yellow | 10,000 | 300 | 25 | 5 |
| c_tak_00001_mainquest_Boss_Blue | chara_tak_00001（タコピー） | Boss | Defense | Blue | 10,000 | 300 | 25 | 5 |
| c_tak_00001_mainquest_Boss_Green | chara_tak_00001（タコピー） | Boss | Defense | Green | 10,000 | 300 | 25 | 5 |
| c_tak_00001_mainquest_glo2_Normal_Red | chara_tak_00001（タコピー） | Normal | Defense | Red | 10,000 | 100 | 32 | 1 |

> **基本HPは全Boss系で10,000（共通）**。実際の強さはシーケンス内の`enemy_hp_coef`で倍率をかけて設定。
> Bossタイプは move_speed 25（かなり遅い）、attack_combo_cycle 5（5コンボ）。
> Normalタイプ（glo2_Normal_Red）は speed 32、combo 1。

### GLO汎用エネミー（general系 / takコンテンツで使用）

| id | mst_enemy_character_id | character_unit_kind | role_type | color | hp | attack_power | move_speed | attack_combo_cycle |
|----|------------------------|---------------------|-----------|-------|-----|--------------|------------|---------------------|
| e_glo_00001_general_Normal_Colorless | enemy_glo_00001 | Normal | Attack | Colorless | 5,000 | 100 | 34 | 1 |
| e_glo_00001_general_Normal_Yellow | enemy_glo_00001 | Normal | Attack | Yellow | 5,000 | 100 | 34 | 1 |
| e_glo_00001_general_Normal_Blue | enemy_glo_00001 | Normal | Attack | Blue | 5,000 | 100 | 34 | 1 |
| e_glo_00001_general_Normal_Green | enemy_glo_00001 | Normal | Attack | Green | 5,000 | 100 | 34 | 1 |

> 基本パラメータは全色共通で HP 5,000 / attack 100 / speed 34。
> takコンテンツでは `enemy_hp_coef` や `enemy_attack_coef` で実際の強さを調整している。

---

## シーケンスパターン（MstAutoPlayerSequence）

シーケンス総数: 57件（normal: 11件、hard: 20件、veryhard: 26件）

### normal_tak_00001（3イベント）

- 使用エネミー: `c_tak_00001_mainquest_Boss_Yellow`（ボス）、`e_glo_00001_general_Normal_Colorless`（雑魚）
- 進行パターン:
  1. DarknessKomaCleared 2: Boss_Yellow x1（summon_position 1.3、hp_coef 15、atk_coef 1.0）
  2. ElapsedTime 350: Colorless x30（間隔700、hp_coef 1.3、atk_coef 2.5）
  3. ElapsedTime 0: Colorless x5（間隔700、hp_coef 1.3、atk_coef 2.5）
- 特徴: コマクリア2枚でボス（タコピー）が前方出現し、同時に雑魚が量産される

### normal_tak_00002（5イベント）

- 使用エネミー: `e_glo_00001_general_Normal_Colorless`（雑魚）、`c_tak_00001_mainquest_Boss_Blue`（中ボス→強ボス）
- 進行パターン:
  1. ElapsedTime 0: Colorless x4（間隔500、hp_coef 1.3、atk_coef 2.5）
  2. FriendUnitDead 4: Colorless x10（間隔500、hp_coef 1.3、atk_coef 2.5）
  3. FriendUnitDead 4: Colorless x10（間隔800、hp_coef 1.3、atk_coef 2.5）
  4. ElapsedTime 700: Boss_Blue x1（hp_coef 5、atk_coef 1.0）
  5. FriendUnitDead 4: Boss_Blue x1（hp_coef 12、atk_coef 1.5）
- 特徴: 時間経過でボス（Blue/弱）、撃破後に強化版ボス（hp_coef 12）が出現

### normal_tak_00003（3イベント）

- 使用エネミー: `e_glo_00001_general_Normal_Colorless`（雑魚）、`c_tak_00001_mainquest_Boss_Green`（ボス）
- 進行パターン:
  1. ElapsedTime 0: Colorless x30（間隔950、hp_coef 1.3、atk_coef 2.5）
  2. ElapsedTime 500: Colorless x2（間隔300、hp_coef 1.3、atk_coef 2.5）
  3. ElapsedTime 1650: Boss_Green x1（aura_type Boss、hp_coef 15、atk_coef 1.5）
- 特徴: 大量の雑魚を処理しながら、一定時間後にBossオーラ付きのタコピー（Green）が出現

---

### hard_tak_00001（7イベント）

- 使用エネミー: `c_tak_00001_mainquest_Boss_Yellow`（ボス）、`e_glo_00001_general_Normal_Yellow`（雑魚）
- 進行パターン:
  1. DarknessKomaCleared 2: Boss_Yellow x1（position 1.8、hp_coef 40、atk_coef 2.4）
  2. DarknessKomaCleared 2: Yellow x1（position 1.45、hp_coef 5.5、atk_coef 10）
  3. DarknessKomaCleared 3: Boss_Yellow x1（position 2.8、hp_coef 50、atk_coef 2.4）
  4. DarknessKomaCleared 3: Yellow x1（position 2.45、hp_coef 5.5、atk_coef 10）
  5. DarknessKomaCleared 3: Yellow x1（position 2.65、hp_coef 5.5、atk_coef 10）
  6. ElapsedTime 0: Yellow x30（間隔600、hp_coef 5.5、atk_coef 10）
  7. ElapsedTime 450: Yellow x30（間隔650、hp_coef 5.5、atk_coef 10）
- 特徴: コマクリアトリガーでボスと雑魚が交互出現。DarknessKomaCleared型シーケンスが中心。

### hard_tak_00002（6イベント）

- 使用エネミー: `e_glo_00001_general_Normal_Blue`（雑魚）、`c_tak_00001_mainquest_Boss_Blue`（ボス）
- 進行パターン:
  1. ElapsedTime 0: Blue x30（間隔800、hp_coef 5.5、atk_coef 10）
  2. FriendUnitDead 4: Blue x30（間隔800、hp_coef 5.5、atk_coef 10）
  3. FriendUnitDead 4: Blue x30（間隔850、hp_coef 5.5、atk_coef 10）
  4. ElapsedTime 1500: Boss_Blue x1（hp_coef 40、atk_coef 2.4）
  5. FriendUnitDead 4: Boss_Blue x1（hp_coef 60、atk_coef 2.4、action_delay 150）
  6. FriendUnitDead 4: Blue x50（間隔850、hp_coef 5.5、atk_coef 10、action_delay 5000）
- 特徴: Blue系で統一。ボスは時間経過と撃破後の2回出現（hp_coef 40→60）。

### hard_tak_00003（7イベント）

- 使用エネミー: `e_glo_00001_general_Normal_Green`（雑魚）、`c_tak_00001_mainquest_Boss_Green`（ボス）
- 進行パターン:
  1. ElapsedTime 800: Green x30（間隔600、hp_coef 5.5、atk_coef 8）
  2. EnterTargetKomaIndex 1: Green x4（間隔150、aura_type Boss、hp_coef 5.5、atk_coef 8）
  3. EnterTargetKomaIndex 2: Green x4（間隔150、hp_coef 5.5、atk_coef 8）
  4. EnterTargetKomaIndex 2: Green x3（間隔50、aura_type Boss、hp_coef 5.5、atk_coef 8）
  5. EnterTargetKomaIndex 3: Green x10（間隔300、hp_coef 5.5、atk_coef 8）
  6. EnterTargetKomaIndex 3: Green x10（間隔500、aura_type Boss、hp_coef 5.5、atk_coef 8）
  7. EnterTargetKomaIndex 3: Boss_Green x1（hp_coef 40、atk_coef 2.4）
- 特徴: EnterTargetKomaIndex型（コマ到達トリガー）で段階的に雑魚強化。最後にボス登場。

---

### veryhard_tak_00001（11イベント）

- 使用エネミー: `c_tak_00001_mainquest_Boss_Yellow`（ボス）、`e_glo_00001_general_Normal_Yellow`（雑魚）
- 進行パターン（DarknessKomaCleared型が中心）:
  1. DarknessKomaCleared 2: Boss_Yellow x1（position 1.8、hp_coef 30、atk_coef 10）
  2. DarknessKomaCleared 2: Yellow x1（position 1.9、hp_coef 8.5、atk_coef 20）
  3. DarknessKomaCleared 2: Yellow x1（position 1.95、hp_coef 8.5、atk_coef 20）
  4. DarknessKomaCleared 4: Boss_Yellow x1（position 1.8、hp_coef 30、atk_coef 10、action_delay 10）
  5. DarknessKomaCleared 4: Yellow x1（position 1.9、hp_coef 8.5、atk_coef 20）
  6. DarknessKomaCleared 4: Yellow x1（position 1.95、hp_coef 8.5、atk_coef 20）
  7. DarknessKomaCleared 5: Boss_Yellow x1（position 2.5、hp_coef 90、atk_coef 13、action_delay 10）
  8. DarknessKomaCleared 5: Yellow x1（position 2.2、hp_coef 8.5、atk_coef 20）
  9. DarknessKomaCleared 5: Yellow x1（position 2.4、hp_coef 8.5、atk_coef 20）
  10. DarknessKomaCleared 5: Yellow x1（position 2.6、hp_coef 8.5、atk_coef 20）
  11. DarknessKomaCleared 5: Yellow x1（position 2.7、hp_coef 8.5、atk_coef 20）
- 特徴: 全てDarknessKomaCleared型。コマクリア2/4/5枚でボスと雑魚が段階出現。最終ボスはhp_coef 90（最強）。

### veryhard_tak_00002（6イベント）

- 使用エネミー: `e_glo_00001_general_Normal_Blue`（雑魚）、`c_tak_00001_mainquest_Boss_Blue`（ボス）
- 進行パターン:
  1. ElapsedTime 500: Blue x30（間隔1000、hp_coef 8.5、atk_coef 18）
  2. FriendUnitDead 4: Blue x30（間隔1000、hp_coef 8.5、atk_coef 18）
  3. FriendUnitDead 4: Blue x30（間隔1000、hp_coef 8.5、atk_coef 18）
  4. ElapsedTime 1500: Boss_Blue x1（hp_coef 70、atk_coef 12）
  5. FriendUnitDead 4: Boss_Blue x1（hp_coef 70、atk_coef 12、action_delay 150）
  6. ElapsedTime 400: Blue x30（間隔1250、hp_coef 8.5、atk_coef 18）
- 特徴: hard_tak_00002の強化版。ボスhp_coefが40/60→70に増加。atk_coef 10→18に強化。

### veryhard_tak_00003（8イベント）

- 使用エネミー: `e_glo_00001_general_Normal_Green`（雑魚）、`c_tak_00001_mainquest_Boss_Green`（ボス）
- 進行パターン（hard_tak_00003の強化版）:
  1. ElapsedTime 750: Green x30（間隔700、hp_coef 8.5、atk_coef 8）
  2. EnterTargetKomaIndex 1: Green x4（間隔400、aura_type Boss、hp_coef 8.5、atk_coef 8）
  3. EnterTargetKomaIndex 2: Green x4（間隔400、hp_coef 8.5、atk_coef 8、action_delay 25）
  4. EnterTargetKomaIndex 2: Green x3（間隔50、aura_type Boss、hp_coef 8.5、atk_coef 8）
  5. EnterTargetKomaIndex 3: Green x10（間隔400、hp_coef 8.5、atk_coef 8）
  6. EnterTargetKomaIndex 3: Green x10（間隔600、aura_type Boss、hp_coef 8.5、atk_coef 8）
  7. EnterTargetKomaIndex 1: Boss_Green x1（hp_coef 30、atk_coef 6.8）
  8. FriendUnitDead 7: Boss_Green x1（hp_coef 50、atk_coef 6.8、action_delay 500）
- 特徴: hard_tak_00003のEnterTargetKomaIndex構成を踏襲。ボスがEnterTargetKomaIndex 1でも出現するため早期登場。FriendUnitDead 7でさらに強化版が出現。

---

## コンテンツ種別ごとの特徴比較

### OutpostHP（アウトポストHP）スケール

| 種別 | HP | 増加幅 |
|------|-----|--------|
| normal | 30,000（全3ステージ共通） | +0/ステージ |
| hard | 80,000（全3ステージ共通） | +0/ステージ |
| veryhard | 150,000（全3ステージ共通） | +0/ステージ |

> 各種別内でHPは全ステージ同一（takの特徴）

### ボスパラメータ比較（実効値 = base HP × hp_coef）

| コンテンツ | ボスID | base HP | hp_coef | 実効HP |
|-----------|--------|---------|---------|--------|
| normal_tak_00001 | Boss_Yellow | 10,000 | 15 | 150,000 |
| normal_tak_00002 | Boss_Blue（初回） | 10,000 | 5 | 50,000 |
| normal_tak_00002 | Boss_Blue（2回目） | 10,000 | 12 | 120,000 |
| normal_tak_00003 | Boss_Green | 10,000 | 15 | 150,000 |
| hard_tak_00001 | Boss_Yellow（初回） | 10,000 | 40 | 400,000 |
| hard_tak_00001 | Boss_Yellow（2回目） | 10,000 | 50 | 500,000 |
| hard_tak_00002 | Boss_Blue（初回） | 10,000 | 40 | 400,000 |
| hard_tak_00002 | Boss_Blue（2回目） | 10,000 | 60 | 600,000 |
| hard_tak_00003 | Boss_Green | 10,000 | 40 | 400,000 |
| veryhard_tak_00001 | Boss_Yellow（2/4コマ） | 10,000 | 30 | 300,000 |
| veryhard_tak_00001 | Boss_Yellow（5コマ最終） | 10,000 | 90 | 900,000 |
| veryhard_tak_00002 | Boss_Blue | 10,000 | 70 | 700,000 |
| veryhard_tak_00003 | Boss_Green（初回） | 10,000 | 30 | 300,000 |
| veryhard_tak_00003 | Boss_Green（2回目） | 10,000 | 50 | 500,000 |

### 雑魚パラメータ比較（実効値 = base HP × hp_coef）

| コンテンツ種別 | 雑魚ID | base HP | hp_coef | 実効HP | atk_coef | 実効attack |
|--------------|--------|---------|---------|--------|----------|-----------|
| normal | Colorless | 5,000 | 1.3 | 6,500 | 2.5 | 250 |
| hard | Yellow/Blue | 5,000 | 5.5 | 27,500 | 10 | 1,000 |
| hard | Green | 5,000 | 5.5 | 27,500 | 8 | 800 |
| veryhard | Yellow | 5,000 | 8.5 | 42,500 | 20 | 2,000 |
| veryhard | Blue | 5,000 | 8.5 | 42,500 | 18 | 1,800 |
| veryhard | Green | 5,000 | 8.5 | 42,500 | 8 | 800 |

### シーケンストリガータイプ比較

| 種別 | 主なトリガー | 特記事項 |
|------|------------|---------|
| normal | ElapsedTime / FriendUnitDead / DarknessKomaCleared | 3種類が混在、複雑度低め |
| hard | DarknessKomaCleared / ElapsedTime / FriendUnitDead / EnterTargetKomaIndex | 4種類混在 |
| veryhard | DarknessKomaCleared / ElapsedTime / FriendUnitDead / EnterTargetKomaIndex | hardの強化版 |

---

## dungeon（限界チャレンジ）設計向けの推奨パラメータ

### 現状

dungeon_tak_* のMstInGameエントリは現時点で存在しない。これから生成する。

### 雑魚敵の選択

takシリーズには専用雑魚敵が存在しないため、GLO汎用エネミーを使用する。

- **normalブロック用雑魚敵（推奨）**: `e_glo_00001_general_Normal_Colorless`
  - base HP: 5,000 / attack: 100 / speed: 34
  - normalコンテンツで実際に使用されている実績あり
  - dungeon仕様に合わせてhp_coef / atk_coefを調整

- **bossブロック用ボス（推奨）**: `c_tak_00001_mainquest_Boss_Yellow`
  - base HP: 10,000 / attack: 300 / speed: 25 / combo: 5
  - URキャラ（chara_tak_00001）のBossパラメータ
  - Defenseロール / Yellow色

### 推奨パラメータ値（既存コンテンツより算出）

dungeonの仕様:
- normalブロック: MstEnemyOutpost HP = 100（固定）、コマ3行
- bossブロック: MstEnemyOutpost HP = 1,000（固定）、コマ1行

**normalブロック向け雑魚パラメータ（参考）**:

| 参考コンテンツ | 雑魚実効HP | 実効attack | 雑魚速度 |
|---------------|------------|-----------|---------|
| normal（outpost HP: 30,000） | 6,500（coef 1.3） | 250（coef 2.5） | 34 |
| hard（outpost HP: 80,000） | 27,500（coef 5.5） | 1,000（coef 10） | 34 |

dungeonはoutpost HP=100と極めて低いため、normalコンテンツの雑魚程度（実効HP 5,000〜10,000）が目安。

**bossブロック向けボスパラメータ（参考）**:

| 参考コンテンツ | ボス実効HP |
|---------------|------------|
| normal（最初のボス） | 50,000〜150,000 |
| hard（ボス初回） | 400,000〜500,000 |

dungeonのbossブロックはoutpost HP=1,000なので、normalコンテンツのボスレベル（実効HP 100,000〜200,000程度）が目安。

### 背景アセット

normalコンテンツで使用されている背景アセット（参考）:
- `glo_00004`（loop_background_asset_key, normal_tak_00001〜00003 共通）
- `glo_00004`（MstKomaLine koma_asset_key, 全コンテンツ共通）

dungeonブロック用は `glo_00004` を踏襲するか、tak専用背景 `tak_000XX` 系があれば確認すること。

### BGM

全既存コンテンツで `SSE_SBG_003_002` を使用（統一）。dungeonブロックも同BGMを推奨。

---

## まとめ・パターン特徴

1. **BGM統一**: 全9コンテンツで `SSE_SBG_003_002` を使用
2. **専用雑魚なし**: takシリーズには専用雑魚敵（enemy_tak_XXXXX）が存在せず、全コンテンツで `enemy_glo_00001` の汎用パラメータを使用
3. **色を統一したコンテンツ設計**: normalの各ステージ、hardの各ステージ、veryhardの各ステージでそれぞれ色（Yellow/Blue/Green）を統一する設計
4. **ボス（タコピー）はhp_coefで強さを表現**: base HP 10,000を各コンテンツのhp_coefで倍率調整（最大90倍 = 実効HP 900,000）
5. **シーケンストリガーの多様性**: DarknessKomaCleared / ElapsedTime / FriendUnitDead / EnterTargetKomaIndex の4タイプを使い分け
6. **OutpostHP全ステージ同一**: 各種別内で全3ステージのOutpost HPは変化しない（chiの+2,000/ステージとは異なる設計）
7. **dungeonは専用雑魚不在のため汎用GLO敵を使用**: ingame-requirements.mdの「専用雑魚なし作品はglo汎用敵に変更」に対応
8. **コマ背景アセット**: 全コンテンツで `glo_00004` を一貫使用
9. **dungeon用データは未生成**: これから `/masterdata-ingame-creator` スキルで normal_00001 と boss_00001 を生成する予定
