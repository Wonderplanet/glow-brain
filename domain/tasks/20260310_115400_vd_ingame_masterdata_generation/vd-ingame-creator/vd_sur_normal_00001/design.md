# vd_sur_normal_00001 インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: 202604010

## インゲーム要件テキスト

魔都精兵のスレイブの世界観を反映したノーマルブロックです。作品のメイン敵として登場する醜鬼（Red属性・攻撃ロール）と、全作品共通のファントム（Colorless属性・攻撃ロール）が時間差で出現します。醜鬼は高いHPと3段ノックバック・高攻撃力を持つ強敵で、序盤から圧力をかけてくる構成です。3波構成（0.25秒後・1.5秒後・3.0秒後）で合計18体が登場し、醜鬼の高耐久を活かしたタフな戦闘体験を提供しながら、ファントムで素早くエナジーを積む機会も確保しています。プレイヤーは醜鬼のHPの高さに対して戦略的なコマ配置が求められる、歯ごたえのあるノーマルブロックです。

---

## レベルデザイン

### 敵キャラ設計

#### 敵キャラ選定（MstEnemyCharacter）

| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| enemy_sur_00101 | 醜鬼 | 雑魚 | Red属性・攻撃ロール・高HP・高ATK |
| enemy_glo_00001 | ファントム | 雑魚（共通） | Colorless属性・攻撃ロール |

#### 敵キャラステータス（MstEnemyStageParameter）

> 既存参照: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/generated/ファントムマスター/MstEnemyStageParameter.csv` (release_key: 202509010)
> 新規生成不要（既存IDをそのままMstAutoPlayerSequence.action_valueで参照）

| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | knockback | combo | drop_bp |
|--------------------------|---------|------|------|-------|---------|----------|----------|-----------|-----------|-------|---------|
| e_sur_00101_vd_Normal_Red | 醜鬼 | Normal | Attack | Red | 400,000 | 850 | 45 | 0.2 | 3 | 1 | 10 |
| e_glo_00001_vd_Normal_Colorless | ファントム | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 | 1 | 150 |

---

### コマ設計

各行独立ランダム抽選（12パターンから）の結果:

```mermaid
block-beta
  columns 4
  A["row=1 / koma1\n幅=0.75\neffect: None"]:3 B["row=1 / koma2\n幅=0.25\neffect: None"]:1
  columns 3
  C["row=2 / koma1\n幅=0.33\neffect: None"]:1 D["row=2 / koma2\n幅=0.34\neffect: None"]:1 E["row=2 / koma3\n幅=0.33\neffect: None"]:1
  columns 4
  F["row=3 / koma1\n幅=0.25\neffect: None"]:1 G["row=3 / koma2\n幅=0.25\neffect: None"]:1 H["row=3 / koma3\n幅=0.50\neffect: None"]:2
```

| row | height | 選択パターン | コマ数 | 各幅 | 幅合計 |
|-----|--------|------------|-------|------|--------|
| 1 | 0.33 | パターン4「がっつり右長2コマ」 | 2コマ | 0.75, 0.25 | 1.0 |
| 2 | 0.33 | パターン7「3等分」 | 3コマ | 0.33, 0.34, 0.33 | 1.0 |
| 3 | 0.34 | パターン10「左広い」 | 3コマ | 0.25, 0.25, 0.50 | 1.0 |

---

### 敵キャラシーケンス設計

#### どのフェーズで、どの敵を、いつ、どこに、どのくらい出現させるか

```mermaid
flowchart LR
  Start((開始)) --> W1["ElapsedTime 250ms\n醜鬼 ×6\nDefault"]
  W1 --> W2["ElapsedTime 1500ms\nファントム ×6\nDefault"]
  W2 --> W3["ElapsedTime 3000ms\n醜鬼 ×6\nDefault"]
  W3 --> End((終了))
```

| elem | 出現タイミング | 敵 | 数 | 累計出現数 |
|------|-------------|---|---|---------|
| 1 | ElapsedTime 250ms | 醜鬼 (e_sur_00101_vd_Normal_Red) | 6 | 6 |
| 2 | ElapsedTime 1500ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 6 | 12 |
| 3 | ElapsedTime 3000ms | 醜鬼 (e_sur_00101_vd_Normal_Red) | 6 | 18 |

合計: **18体**（要件「最低15体以上」を満たす）

#### 敵キャラの固有ステータス調整（hp_coef / atk_coef）

| 波 | 敵 | base_hp | hp_coef | 実HP | base_atk | atk_coef | 実ATK |
|---|---|---------|---------|------|----------|----------|-------|
| 1 | 醜鬼 | 400,000 | 1.0 | 400,000 | 850 | 1.0 | 850 |
| 2 | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |
| 3 | 醜鬼 | 400,000 | 1.0 | 400,000 | 850 | 1.0 | 850 |

#### フェーズ切り替えはあるか

なし（VDではSwitchSequenceGroup使用禁止）

---

## 演出

### アセット

#### 背景

| 設定箇所 | アセットキー | 備考 |
|---------|------------|------|
| loop_background_asset_key | （空） | VDの背景切り替えはゲームロジック側で管理 |
| フロア0以上 | koma_background_vd_00001 | クライアント側でフロア係数に応じて切り替え |
| フロア20以上 | koma_background_vd_00003 | 同上 |
| フロア40以上 | koma_background_vd_00005 | 同上 |

#### BGM

| 設定 | 値 | 備考 |
|-----|---|------|
| bgm_asset_key | SSE_SBG_003_010 | ノーマルブロック用BGM |

---

### 敵キャラオーラ

| オーラ種別 | 使用箇所 |
|----------|---------|
| Default | 全敵キャラ（ノーマルブロックはボスなし、全行Default） |

---

### 敵キャラ召喚アニメーション

全キャラ `SummonEnemy` アクションによるElapsedTime時間差召喚。InitialSummonは使用しない（normalブロックはボスなし）。醜鬼はHP400,000の高耐久キャラのため、召喚時の迫力ある登場演出がデフォルトアニメーションで表現される。

---

## 生成テーブルまとめ

| テーブル | 状態 | 備考 |
|---------|------|------|
| MstEnemyStageParameter | 既存参照 | generated/ファントムマスター/ の既存データ使用（e_sur_00101_vd_Normal_Red, e_glo_00001_vd_Normal_Colorless） |
| MstEnemyOutpost | 新規生成 | id=vd_sur_normal_00001、HP=100固定、is_damage_invalidation=空 |
| MstPage | 新規生成 | id=vd_sur_normal_00001 |
| MstKomaLine | 新規生成 | 3行固定（row1-3）、各行独立ランダム抽選 |
| MstAutoPlayerSequence | 新規生成 | sequence_set_id=vd_sur_normal_00001、3要素（計18体） |
| MstInGame | 新規生成 | id=vd_sur_normal_00001、content_type=Dungeon、stage_type=vd_normal、ボスなし |

---

## 設計詳細（CSV生成時の参照用）

### MstEnemyOutpost

| カラム | 値 |
|--------|-----|
| ENABLE | e |
| release_key | 202604010 |
| id | vd_sur_normal_00001 |
| hp | 100 |
| is_damage_invalidation | （空） |

### MstPage

| カラム | 値 |
|--------|-----|
| ENABLE | e |
| release_key | 202604010 |
| id | vd_sur_normal_00001 |

### MstKomaLine（3行分）

| ENABLE | release_key | id | mst_page_id | row | height | koma1_width | koma2_width | koma3_width | koma4_width | koma1_effect_type | koma1_effect_target_side |
|--------|-------------|-----|------------|-----|--------|------------|------------|------------|------------|-------------------|--------------------------|
| e | 202604010 | vd_sur_normal_00001_r1 | vd_sur_normal_00001 | 1 | 0.33 | 0.75 | 0.25 | | | None | All |
| e | 202604010 | vd_sur_normal_00001_r2 | vd_sur_normal_00001 | 2 | 0.33 | 0.33 | 0.34 | 0.33 | | None | All |
| e | 202604010 | vd_sur_normal_00001_r3 | vd_sur_normal_00001 | 3 | 0.34 | 0.25 | 0.25 | 0.50 | | None | All |

### MstAutoPlayerSequence（3行分）

| ENABLE | release_key | id | sequence_set_id | sequence_group_id | sequence_element_id | condition_type | condition_value | action_type | action_value | summon_count | summon_position | summon_interval | move_start_condition_type | move_start_condition_value | aura_type | enemy_hp_coef | enemy_attack_coef | enemy_speed_coef | koma_effect_type |
|--------|-------------|-----|----------------|-------------------|-------------------|----------------|----------------|-------------|-------------|-------------|-----------------|----------------|--------------------------|--------------------------|-----------|--------------|------------------|-----------------|-----------------|
| e | 202604010 | vd_sur_normal_00001_1 | vd_sur_normal_00001 | （空） | 1 | ElapsedTime | 250 | SummonEnemy | e_sur_00101_vd_Normal_Red | 6 | | | | | Default | 1 | 1 | 1 | None |
| e | 202604010 | vd_sur_normal_00001_2 | vd_sur_normal_00001 | （空） | 2 | ElapsedTime | 1500 | SummonEnemy | e_glo_00001_vd_Normal_Colorless | 6 | | | | | Default | 1 | 1 | 1 | None |
| e | 202604010 | vd_sur_normal_00001_3 | vd_sur_normal_00001 | （空） | 3 | ElapsedTime | 3000 | SummonEnemy | e_sur_00101_vd_Normal_Red | 6 | | | | | Default | 1 | 1 | 1 | None |

### MstInGame

| カラム | 値 |
|--------|-----|
| ENABLE | e |
| release_key | 202604010 |
| id | vd_sur_normal_00001 |
| mst_auto_player_sequence_set_id | vd_sur_normal_00001 |
| bgm_asset_key | SSE_SBG_003_010 |
| boss_bgm_asset_key | （空） |
| loop_background_asset_key | （空） |
| mst_page_id | vd_sur_normal_00001 |
| mst_enemy_outpost_id | vd_sur_normal_00001 |
| boss_mst_enemy_stage_parameter_id | （空） |
| content_type | Dungeon |
| stage_type | vd_normal |
